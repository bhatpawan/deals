<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import;

use Firebear\ImportExport\Model\Import\Context;
use Firebear\ImportExport\Model\Import\Order\AbstractAdapter as OrderAbstractAdapter;
use Magento\Eav\Model\Entity\AbstractEntity as EavAbstractEntity;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Validator\EmailAddress;
use Magento\ImportExport\Model\Import;

abstract class AbstractAdapter extends OrderAbstractAdapter
{
    /**
     * Exclude Fields
     *
     * @var array
     */
    protected $excludeFields = [];

    /**
     * Option Group Name
     *
     * @var string
     */
    protected $optGroupName = '';

    /**
     * Column Prefix
     *
     * @var string
     */
    protected $columnPrefix = '';

    /**
     * Replace behavior available for the adapter?
     *
     * @var bool
     */
    protected $enableReplace = false;

    /**
     * Resource model
     *
     * @var AbstractDb|EavAbstractEntity
     */
    protected $resourceModel;

    /**
     * Additional data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @param Context $context
     * @param AbstractResource $resourceModel
     * @param array $data
     */
    public function __construct(
        Context $context,
        AbstractResource $resourceModel,
        array $data = []
    ) {
        $this->_dataSourceModel = $context->getDataSourceModel();
        $this->_resource = $context->getResource();
        $this->_connection = $context->getResource()->getConnection('checkout');
        $this->_resourceHelper = $context->getResourceHelper();
        $this->errorAggregator = $context->getErrorAggregator();
        $this->jsonHelper = $context->getJsonHelper();
        $this->defaultConnection = $this->_connection;
        $this->resourceModel = $resourceModel;
        $this->data = $data;

        foreach ($this->errorMessageTemplates as $errorCode => $message) {
            $this->addMessageTemplate($errorCode, $message);
        }
        $this->initErrorTemplates();
        $this->_mainTable = $this->getMainTable();
    }

    /**
     * Retrieve Objects From Data
     *
     * @param $method
     * @param $args
     * @return mixed|null
     */
    public function __call($method, $args)
    {
        if (substr($method, 0, 7) == 'getData') {
            $objectName = lcfirst(substr($method, 7));

            return isset($this->data[$objectName]) ?
                $this->data[$objectName] :
                null;
        }
    }

    public function getAllFields()
    {
        return array_filter(
            array_keys(
                $this->_connection->describeTable(
                    $this->getMainTable()
                )
            ),
            function ($field) {
                return !in_array($field, $this->excludeFields);
            }
        );
    }

    public function getMainTable()
    {
        return $this->getTableFromModel($this->resourceModel);
    }

    /**
     * Retrieve Column Prefix
     *
     * @return string
     */
    public function getColumnPrefix()
    {
        return $this->columnPrefix;
    }

    /**
     * Retrieve Option Group Name
     *
     * @return string
     */
    public function getOptGroupName()
    {
        return $this->optGroupName;
    }

    public function prepareRowData(array $rowData)
    {
        $this->_processedRowsCount++;

        $extractRow = $this->_extractField($rowData, $this->columnPrefix);
        if (!array_filter($extractRow)) {
            return false;
        }

        return $extractRow;
    }

    public function getBehavior()
    {
        $behavior = parent::getBehavior();
        if (($behavior == Import::BEHAVIOR_REPLACE) && !$this->enableReplace) {
            $behavior = Import::BEHAVIOR_ADD_UPDATE;
        }

        return $behavior;
    }

    /**
     * Retrieve Table From Model Resource
     *
     * @param AbstractDb|EavAbstractEntity $model
     * @return string
     */
    protected function getTableFromModel($model)
    {
        if ($model instanceof AbstractDb) {
            return $model->getMainTable();
        }

        return $model->getEntityTable();
    }

    protected function _extractField($rowData, $prefix)
    {
        if (!$prefix) {
            return array_filter(
                $rowData,
                function ($key) {
                    return strpos($key, ':') === false;
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return parent::_extractField($rowData, $prefix);
    }

    /**
     * Retrieve Entity Id If Entity Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getExistEntityId(array $rowData)
    {
        return false;
    }

    /**
     * Validate Email
     *
     * @param string $email
     * @return bool
     */
    protected function validateEmail($email)
    {
        return \Zend_Validate::is($email, EmailAddress::class);
    }

    /**
     * Validate Foreign Key
     *
     * @param $column
     * @param $value
     * @return bool
     */
    protected function validateForeignKey($column, $value)
    {
        $foreignKeys = $this->_connection->getForeignKeys(
            $this->getMainTable()
        );
        foreach ($foreignKeys as $key) {
            if ($key['COLUMN_NAME'] == $column) {
                $queryColumn = $key['REF_COLUMN_NAME'];
                $queryTable = $this->_resource->getTableName($key['REF_TABLE_NAME']);

                $bind = [':'.$queryColumn => $value];
                $select = $this->_connection->select()
                    ->from($queryTable, $queryColumn)
                    ->where($queryColumn.' = :'.$queryColumn);

                return (bool) $this->_connection->fetchOne($select, $bind);
            }
        }

        return false;
    }

    /**
     * Convert To Boolean For Query
     *
     * @param $value
     * @return int
     */
    protected function convertToBool($value)
    {
        return intval($value) > 0 ? 1 : 0;
    }

    /**
     * General Check Of Unique Key
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function checkUniqueKey(array $rowData, $rowNumber)
    {
        return true;
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        return true;
    }

    protected function _validateRowForReplace(array $rowData, $rowNumber)
    {
        $this->_validateRowForDelete($rowData, $rowNumber);
        if (!$this->getErrorAggregator()->isRowInvalid($rowNumber)) {
            $this->_validateRowForUpdate($rowData, $rowNumber);
        }
    }

    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        return true;
    }

    protected function _prepareDataForReplace(array $rowData)
    {
        return $this->_prepareDataForUpdate($rowData);
    }

    protected function _getIdForDelete(array $rowData)
    {
        return -1;
    }

    protected function _prepareEntityRow(array $entityRow, array $rowData)
    {
        return $this->_prepareRowForDb(
            parent::_prepareEntityRow($entityRow, $rowData)
        );
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

    protected function _getEntityFieldsToUpdate(array $toUpdate)
    {
        return array_keys(reset($toUpdate));
    }

    /**
     * Retrieve Table Field Names
     *
     * @return array
     */
    protected function getTableFieldNames()
    {
        return $this->_mainTable ? parent::getTableFieldNames() : [];
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
            $existingIncrementIds = [];

            if ($this->getBehavior() == Import::BEHAVIOR_REPLACE) {
                $incrementIds = array_filter(array_column($bunch, 'increment_id'));
                $existingIds = $this->getExistingIds($incrementIds);
                $existingIncrementIds = array_filter(array_column($existingIds, 'increment_id'));
            }
            foreach ($bunch as $rowNumber => $rowData) {
                $this->prepareCurrentOrderId($rowData);
                if (!empty($this->_currentOrderId)) {
                    if ($this->getBehavior() == Import::BEHAVIOR_REPLACE
                        && !in_array($this->_currentOrderId, $existingIncrementIds)) {
                        continue;
                    }
                }

                $rowData = $this->prepareRowData($rowData);
                /* validate data */
                if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->isErrorLimitExceeded() && $this->rowsCanBeSkipped()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                /* behavior selector */
                switch ($this->getBehavior()) {
                    case Import::BEHAVIOR_DELETE:
                        $toDelete[] = $this->_getIdForDelete($rowData);
                        break;
                    case Import::BEHAVIOR_REPLACE:
                        $toDelete[] = $this->_getIdForDelete($rowData);
                        $this->_deleteEntities($toDelete);
                        $data = $this->_prepareDataForUpdate($rowData);
                        $toCreate = array_merge($toCreate, $data[self::ENTITIES_TO_CREATE_KEY]);
                        break;
                    case Import::BEHAVIOR_ADD_UPDATE:
                        $data = $this->_prepareDataForUpdate($rowData);
                        $toCreate = array_merge($toCreate, $data[self::ENTITIES_TO_CREATE_KEY]);
                        $toUpdate = array_merge($toUpdate, $data[self::ENTITIES_TO_UPDATE_KEY]);
                        break;
                }
            }
            /* save prepared data */
            if (isset($this->_parameters['entity']) &&
                $this->_parameters['entity'] == 'quote' &&
                $this->getBehavior() == Import::BEHAVIOR_REPLACE
            ) {
                $this->quoteIdsDeleted = $toDelete;
            }
            if ($toCreate || $toUpdate) {
                $this->_saveEntities($toCreate, $toUpdate);
            }
            if ($toDelete && $this->getBehavior() == Import::BEHAVIOR_DELETE) {
                $this->_deleteEntities($toDelete);
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function rowsCanBeSkipped(): bool
    {
        return true;
    }
}
