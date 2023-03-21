<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExportMsi\Model\Import;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventoryImportExport\Model\Import\Serializer\Json as JsonHelper;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper as ResourceHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Helper\Data as ImportExportData;
use Firebear\ImportExport\Model\ResourceModel\Import\Data as DataSourceModel;
use Firebear\ImportExport\Model\Import\ImportAdapterInterface;
use Firebear\ImportExport\Traits\Import\Entity as ImportTrait;

/**
 * StockSource Import
 */
class StockSource extends AbstractEntity implements ImportAdapterInterface
{
    use ImportTrait;

    /**
     * Keys Which Used To Build Result Data Array For Future Update
     */
    const ENTITIES_TO_CREATE_KEY = 'entities_to_create';

    const ENTITIES_TO_UPDATE_KEY = 'entities_to_update';

    /**
     * Resource Connection
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $_mainTable = 'inventory_source';

    /**
     * Entity columns
     *
     * @var string[]
     */
    protected $_importFields = [
        SourceInterface::SOURCE_CODE,
        SourceInterface::NAME,
        SourceInterface::CONTACT_NAME,
        SourceInterface::EMAIL,
        SourceInterface::ENABLED,
        SourceInterface::DESCRIPTION,
        SourceInterface::LATITUDE,
        SourceInterface::LONGITUDE,
        SourceInterface::COUNTRY_ID,
        SourceInterface::REGION_ID,
        SourceInterface::REGION,
        SourceInterface::CITY,
        SourceInterface::STREET,
        SourceInterface::POSTCODE,
        SourceInterface::PHONE,
        SourceInterface::FAX,
        SourceInterface::USE_DEFAULT_CARRIER_CONFIG,
        //SourceInterface::CARRIER_LINKS,
    ];

    /**
     * Error Codes
     */
    const ERROR_SOURCE_CODE_IS_EMPTY = 'sourceCodeIsEmpty';
    const ERROR_SOURCE_CODE_IS_DEFAULT = 'sourceCodeIsDefault';

    /**
     * Validation Failure Message Template Definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_SOURCE_CODE_IS_EMPTY => 'Source code is empty',
        self::ERROR_SOURCE_CODE_IS_DEFAULT => 'Default Stock could not be deleted.',
    ];

    /**
     * Initialize Import
     *
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param ResourceHelper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param ConsoleOutput $output
     * @param LoggerInterface $logger
     * @param ImportExportData $importExportData
     * @param JsonHelper $jsonHelper
     * @param DataSourceModel $dataSourceModel
     * @param array $data
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        ResourceHelper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        ConsoleOutput $output,
        LoggerInterface $logger,
        ImportExportData $importExportData,
        JsonHelper $jsonHelper,
        DataSourceModel $dataSourceModel,
        array $data = []
    ) {
        $this->_logger = $logger;
        $this->output = $output;
        $this->_resource = $resource;
        $this->_resourceHelper = $resourceHelper;
        $this->_importExportData = $importExportData;
        $this->jsonHelper = $jsonHelper;

        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $data
        );
        $this->initErrorTemplates();
        $this->_dataSourceModel = $dataSourceModel;
    }

    /**
     * Source model setter
     *
     * @param AbstractSource $source
     * @return $this
     */
    public function setSource(AbstractSource $source)
    {
        $this->_source = $source;
        $this->_dataValidated = false;

        return $this;
    }

    /**
     * Inner source object getter
     *
     * @return AbstractSource
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getSource()
    {
        if (!$this->_source) {
            throw new LocalizedException(__('Please specify a source.'));
        }
        return $this->_source;
    }

    /**
     * Initialize Error Templates
     *
     * @return void
     */
    public function initErrorTemplates()
    {
        foreach ($this->_messageTemplates as $errorCode => $template) {
            $this->addMessageTemplate($errorCode, $template);
        }
    }

    /**
     * Import Data Rows
     *
     * @return boolean
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $toCreate = [];
            $toUpdate = [];
            $toDelete = [];
            foreach ($bunch as $rowNumber => $rowData) {
                /* validate data */
                if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->isErrorLimitExceeded()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }
                /* behavior selector */
                switch ($this->getBehavior()) {
                    case Import::BEHAVIOR_DELETE:
                        $toDelete[] = $rowData['source_code'];
                        break;
                    case Import::BEHAVIOR_REPLACE:
                        $data = $this->_prepareDataForReplace($rowData);
                        $toUpdate = array_merge($toUpdate, $data[self::ENTITIES_TO_UPDATE_KEY]);
                        break;
                    case Import::BEHAVIOR_ADD_UPDATE:
                        $data = $this->_prepareDataForUpdate($rowData);
                        $toCreate = array_merge($toCreate, $data[self::ENTITIES_TO_CREATE_KEY]);
                        $toUpdate = array_merge($toUpdate, $data[self::ENTITIES_TO_UPDATE_KEY]);
                        break;
                }
            }
            /* save prepared data */
            if ($toCreate || $toUpdate) {
                $this->_saveEntities($toCreate, $toUpdate);
            }
            if ($toDelete) {
                $this->_deleteEntities($toDelete);
            }
        }
        return true;
    }

    /**
     * Prepare Data For Update
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $newEntity = false;
        if (!$this->_isExistEntity($rowData)) {
            $newEntity = true;
        }

        $entityRow = [
            'source_code' => $rowData['source_code']
        ];
        /* prepare data */
        $entityRow = $this->_prepareEntityRow($entityRow, $rowData);
        if ($newEntity) {
            $toCreate[] = $entityRow;
        } else {
            $toUpdate[] = $entityRow;
        }
        return [
            self::ENTITIES_TO_CREATE_KEY => $toCreate,
            self::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    /**
     * Prepare Data For Replace
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareDataForReplace(array $rowData)
    {
        $toUpdate = [];
        $entityRow = [
            'source_code' => $rowData['source_code']
        ];
        /* prepare data */
        $toUpdate[] = $this->_prepareEntityRow($entityRow, $rowData);
        return [
            self::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    /**
     * Prepare Entity Field Values
     *
     * @param array $entityRow
     * @param array $rowData
     * @return array
     */
    protected function _prepareEntityRow(array $entityRow, array $rowData)
    {
        $keys = array_keys($entityRow);
        foreach ($this->getAllFields() as $field) {
            if (!in_array($field, $keys) && isset($rowData[$field])) {
                $entityRow[$field] = $rowData[$field];
            }
        }
        return $entityRow;
    }

    /**
     * Validate Data Row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if (isset($this->_validatedRows[$rowNumber])) {
            // check that row is already validated
            return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
        }
        $this->_validatedRows[$rowNumber] = true;
        $this->_processedEntitiesCount++;
        /* behavior selector */
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->_validateRowForDelete($rowData, $rowNumber);
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->_validateRowForReplace($rowData, $rowNumber);
                break;
            case Import::BEHAVIOR_ADD_UPDATE:
                $this->_validateRowForUpdate($rowData, $rowNumber);
                break;
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    /**
     * Validate Row Data For Replace Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForReplace(array $rowData, $rowNumber)
    {
        if (empty($rowData['source_code'])) {
            $this->addRowError(static::ERROR_SOURCE_CODE_IS_EMPTY, $rowNumber);
        }
    }

    /**
     * Validate Row Data For Add/Update Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        if (empty($rowData['source_code'])) {
            $this->addRowError(static::ERROR_SOURCE_CODE_IS_EMPTY, $rowNumber);
        }
    }

    /**
     * Validate Row Data For Delete Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        if (empty($rowData['source_code'])) {
            $this->addRowError(static::ERROR_SOURCE_CODE_IS_EMPTY, $rowNumber);
        } elseif ($rowData['source_code'] == 'default') {
            $this->addRowError(static::ERROR_SOURCE_CODE_IS_DEFAULT, $rowNumber);
        }
    }

    /**
     * Check If Entity Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _isExistEntity(array $rowData)
    {
        $bind = [':source_code' => $rowData['source_code']];
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->_connection->select();
        $select->from($this->getMainTable(), 'source_code')
            ->where('source_code = :source_code');

        return (bool)$this->_connection->fetchOne($select, $bind);
    }

    /**
     * Update And Insert Data In Entity Table
     *
     * @param array $toCreate Rows for insert
     * @param array $toUpdate Rows for update
     * @return $this
     */
    protected function _saveEntities(array $toCreate, array $toUpdate)
    {
        if ($toCreate) {
            $this->_connection->insertMultiple(
                $this->getMainTable(),
                $toCreate
            );
        }
        if ($toUpdate) {
            $this->_connection->insertOnDuplicate(
                $this->getMainTable(),
                $toUpdate,
                $this->_getEntityFieldsToUpdate($toUpdate)
            );
        }
        return $this;
    }

    /**
     * Delete List Of Entities
     *
     * @param array $toDelete Entities Id List
     * @return $this
     */
    protected function _deleteEntities(array $toDelete)
    {
        $condition = $this->_connection->quoteInto(
            'source_code IN (?)',
            $toDelete
        );
        $this->_connection->delete($this->getMainTable(), $condition);

        return $this;
    }

    /**
     * Filter The Entity That Are Being Updated So We Only Change Fields Found In The Importer File
     *
     * @param array $toUpdate
     * @return array
     */
    protected function _getEntityFieldsToUpdate(array $toUpdate)
    {
        $firstEntity = reset($toUpdate);
        $columnsToUpdate = array_keys($firstEntity);
        $fieldsToUpdate = array_filter(
            $this->getAllFields(),
            function ($field) use ($columnsToUpdate) {
                return in_array($field, $columnsToUpdate);
            }
        );
        return $fieldsToUpdate;
    }

    /**
     * Retrieve Main Table Name
     *
     * @return string
     */
    public function getMainTable()
    {
        return $this->_resource->getTableName(
            $this->_mainTable
        );
    }

    /**
     * Save Validated Bunches
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
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
                } catch (\InvalidArgumentException $e) {
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
        return $this;
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
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'stock_sources';
    }
}
