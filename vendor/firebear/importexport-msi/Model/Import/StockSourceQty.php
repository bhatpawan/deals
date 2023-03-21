<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportMsi\Model\Import;

use Exception;
use Firebear\ImportExport\Model\Import\Context;
use Firebear\ImportExport\Model\Import\ImportAdapterInterface;
use Firebear\ImportExport\Traits\Import\Entity as ImportTrait;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Indexer\Model\Indexer\StateFactory;
use Magento\InventoryImportExport\Model\Import\Serializer\Json as JsonHelper;
use Magento\InventoryImportExport\Model\Import\Sources;
use Magento\InventoryImportExport\Model\Import\Sources as AbstractEntity;
use Magento\InventoryImportExport\Model\Import\Validator\ValidatorInterface;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;

/**
 * StockSourceQty Import
 */
class StockSourceQty extends AbstractEntity implements ImportAdapterInterface
{
    use ImportTrait;

    /**
     * Entity columns
     *
     * @var string[]
     */
    protected $_importFields = [
        Sources::COL_SOURCE_CODE,
        Sources::COL_SKU,
        Sources::COL_QTY,
        Sources::COL_STATUS
    ];

    /**
     * Product factory
     *
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Product model
     *
     * @var Product
     */
    protected $product;

    /**
     * @var StateFactory
     */
    private $stateFactory;

    /**
     * StockSourceQty constructor.
     * @param Context $context
     * @param JsonHelper $jsonHelper
     * @param ValidatorInterface $validator
     * @param StateFactory $stateFactory
     * @param ProductFactory $productFactory
     * @param array $commands
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        ValidatorInterface $validator,
        StateFactory $stateFactory,
        ProductFactory $productFactory,
        array $commands = []
    ) {
        $this->_logger = $context->getLogger();
        $this->output = $context->getOutput();
        $this->productFactory = $productFactory;

        parent::__construct(
            $jsonHelper,
            $context->getErrorAggregator(),
            $context->getResourceHelper(),
            $context->getImportExportData(),
            $context->getDataSourceModel(),
            $validator,
            $commands
        );
        $this->stateFactory = $stateFactory;
    }

    /**
     * Retrieve All Fields Source
     *
     * @return array
     */
    public function getAllFields()
    {
        return $this->_importFields;
    }

    /**
     * @return bool
     */
    protected function _importData()
    {
        $result = false;
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowData) {
                $this->_processedRowsCount++;
                $this->_processedEntitiesCount++;
            }
        }
        try {
            $this->changeIndexerStatus(StateInterface::STATUS_WORKING);
            $result = parent::_importData();
            $this->changeIndexerStatus(StateInterface::STATUS_INVALID);
        } catch (ValidationException $validationException) {
            $this->addLogWriteln($validationException->getMessage(), $this->getOutput(), 'error');
            $this->addLogWriteln(
                $this->jsonHelper->jsonEncode($validationException->getParameters()),
                $this->getOutput(),
                'error'
            );
            /** @var LocalizedException $error */
            foreach ($validationException->getErrors() as $error) {
                $this->addLogWriteln($error->getMessage(), $this->getOutput(), 'error');
            }
        } catch (LocalizedException $localizedException) {
            $this->addLogWriteln($localizedException->getMessage(), $this->getOutput(), 'error');
        } catch (Exception $exception) {
            $this->addLogWriteln($exception->getMessage(), $this->getOutput(), 'error');
        }
        return $result;
    }

    /**
     * @param string $status
     * @throws Exception
     */
    private function changeIndexerStatus($status = StateInterface::STATUS_WORKING)
    {
        $state = $this->stateFactory->create();
        $state->loadByIndexer(InventoryIndexer::INDEXER_ID);
        $state->setStatus($status);
        $state->save();
    }

    /**
     * Save Validated Bunches
     *
     * @return $this
     * @throws LocalizedException
     */
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
                $currentDataSize = strlen($this->jsonHelper->serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }

            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                    $this->_processedEntitiesCount++;
                } catch (InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }
                $rowData = $this->customBunchesData($rowData);
                $this->_processedRowsCount++;
                if ($this->validateRow($rowData, $source->key())) {
                    $rowSize = strlen($this->jsonHelper->serialize($rowData));

                    $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize += $rowSize;
                    }
                }
                $source->next();
            }
        }

        foreach ($this->getErrorAggregator()->getAllErrors() as $error) {
            $this->addLogWriteln(
                __('%1 in row %2', $error->getErrorMessage(), $error->getRowNumber()),
                $this->getOutput()
            );
        }
        return $this;
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'stock_sources_qty';
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $result = true;
        foreach ($this->_importFields as $field) {
            if ($field === self::COL_STATUS
                && isset($rowData[self::COL_STATUS])
                && $rowData[self::COL_STATUS] !== ''
                && !in_array($rowData[self::COL_STATUS], [0, 1])
            ) {
                $this->addRowError(__('Invalid %1 value', $field), $rowNum);
                $result = false;
            } elseif (isset($rowData[$field]) &&
                $rowData[$field] === ''
            ) {
                $this->addRowError(__('Invalid %1 value', $field), $rowNum);
                $result = false;
            }
        }

        if (isset($rowData[self::COL_SKU]) &&
            !$this->getProduct()->getIdBySku($rowData[self::COL_SKU])
        ) {
            $this->addRowError(
                __('The Product with the "%1" SKU doesn\'t exist.', $rowData[self::COL_SKU]),
                $rowNum
            );
            return false;
        }

        if (!$result) {
            return $result;
        }
        return parent::validateRow($rowData, $rowNum);
    }

    /**
     * Retrieve Product model
     *
     * @return Product
     */
    public function getProduct()
    {
        if (null === $this->product) {
            $this->product = $this->productFactory->create();
        }
        return $this->product;
    }
}
