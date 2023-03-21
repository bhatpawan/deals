<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import;

use Firebear\ImportExport\Model\Import\Context;
use Firebear\ImportExport\Model\Import\Order;
use Magento\Quote\Model\ResourceModel\QuoteFactory as QuoteResourceModelFactory;

abstract class AbstractEntity extends Order
{
    /**
     * Entity Type Code
     *
     * @var string
     */
    protected $entityTypeCode;

    /**
     * @var QuoteResourceModelFactory
     */
    protected $quoteFactory;

    /**
     * Array Of Adapters For Import
     *
     * @var AbstractAdapter[]
     */
    protected $adapters;

    /**
     * AbstractEntity constructor.
     * @param Context $context
     * @param string $entityTypeCode
     * @param QuoteResourceModelFactory $quoteFactory
     * @param array $adapters
     */
    public function __construct(
        Context $context,
        string $entityTypeCode,
        QuoteResourceModelFactory $quoteFactory,
        array $adapters
    ) {
        $this->jsonHelper = $context->getJsonHelper();
        $this->_importExportData = $context->getImportExportData();
        $this->_dataSourceModel = $context->getDataSourceModel();
        $this->_resource = $context->getResource();
        $this->_connection = $context->getResource()->getConnection('checkout');
        $this->_resourceHelper = $context->getResourceHelper();
        $this->string = $context->getStringUtils();
        $this->errorAggregator = $context->getErrorAggregator();

        $this->entityTypeCode = $entityTypeCode;
        $this->adapters = $adapters;
        $this->quoteFactory = $quoteFactory;
    }

    public function getEntityTypeCode()
    {
        return $this->entityTypeCode;
    }

    public function getChildren()
    {
        return $this->adapters;
    }

    public function getAllFields()
    {
        $fields = [];

        foreach ($this->getChildren() as $adapter) {
            $optGroupName = $adapter->getOptGroupName();
            $columnPrefix = $adapter->getColumnPrefix();

            if ($optGroupName) {
                $adapterFields = [
                    'label' => __($adapter->getOptGroupName()),
                    'value' => []
                ];

                foreach ($adapter->getAllFields() as $field) {
                    $adapterFields['value'][] = [
                        'label' => $field,
                        'value' => $columnPrefix ? $columnPrefix.':'.$field : $field
                    ];
                }

                $fields[] = $adapterFields;
            } else {
                $fields = array_merge($fields, $adapter->getAllFields());
            }
        }

        return $fields;
    }

    public function _importData()
    {
        foreach ($this->getAdaptersForImport() as $adapter) {
            $adapter->importData();
        }

        return true;
    }

    public function getProcessedRowsCount()
    {
        return $this->adapters['entity']->getProcessedRowsCount() ?: $this->_processedRowsCount;
    }

    public function getProcessedEntitiesCount()
    {
        return $this->adapters['entity']->getProcessedEntitiesCount() ?: $this->_processedEntitiesCount;
    }

    /**
     * Retrieve Adapters For Import
     *
     * @return AbstractAdapter[]
     */
    protected function getAdaptersForImport()
    {
        return $this->getChildren();
    }

    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();
        $file = null;
        $jobId = null;
        if (isset($this->_parameters['file'])) {
            $file = $this->_parameters['file'];
        }
        if (isset($this->_parameters['job_id'])) {
            $jobId = $this->_parameters['job_id'];
        }

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunches(
                    $this->getEntityTypeCode(),
                    $this->getBehavior(),
                    $jobId,
                    $file,
                    $bunchRows
                );
                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen($this->phpSerialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }

            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                    $rowData = $this->prepareRow($rowData);
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                if ($this->_parameters['entity'] == 'requisition_list'
                    && isset($rowData['customer_id'])
                    && empty($rowData['customer_id'])
                ) {
                    $rowData['description'] = "";
                    $rowData['name'] = "";
                }

                $this->_processedRowsCount++;
                $rowData = $this->customBunchesData($rowData);
                $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                    $startNewBunch = true;
                    $nextRowBackup = [$source->key() => $rowData];
                } else {
                    $bunchRows[$source->key()] = $rowData;
                    $currentDataSize += $rowSize;
                }

                $source->next();
            }
        }
        return $this;
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function prepareRow(array $rowData)
    {
        foreach ($rowData as $key => $val) {
            if ($val === null) {
                $rowData[$key] = '';
            }
        }

        return $rowData;
    }

    /**
     * @param array $rowData
     * @return array|mixed
     */
    public function customBunchesData(array $rowData)
    {
        return $rowData;
    }
}
