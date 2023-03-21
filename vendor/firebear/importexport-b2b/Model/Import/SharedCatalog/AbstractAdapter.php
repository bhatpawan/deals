<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\SharedCatalog;

use Firebear\ImportExport\Model\Import\Context;
use Firebear\ImportExportB2b\Model\Import\AbstractAdapter as BaseAbstractAdapter;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\ImportExport\Model\Import;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

abstract class AbstractAdapter extends BaseAbstractAdapter
{
    /**
     * Keys Which Used To Build Result Data Array For Future Update
     */
    const ENTITIES_TO_DELETE_KEY = 'entities_to_delete';

    /**
     * Column Names
     */
    const COLUMN_NAME = SharedCatalogInterface::NAME;
    const COLUMN_CATEGORY = 'category';

    /**
     * Storage For Maps
     *
     * @var Storage
     */
    protected $storage;

    /**
     * @param Context $context
     * @param AbstractResource $resourceModel
     * @param Storage $storage
     * @param array $data
     */
    public function __construct(
        Context $context,
        AbstractResource $resourceModel,
        Storage $storage,
        array $data = []
    ) {
        parent::__construct($context, $resourceModel, $data);

        $this->storage = $storage
            ->setCustomerGroupIdsMap([]);
    }

    /**
     * Retrieve Customer Group Identifier If Customer Group Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getCustomerGroupIdFromMap(array $rowData)
    {
        $name = $rowData[static::COLUMN_NAME];
        $customerGroupIdsMap = $this->storage->getCustomerGroupIdsMap();

        if (isset($customerGroupIdsMap[$name])) {
            return $customerGroupIdsMap[$name];
        }

        return false;
    }

    /**
     * Prepare multiple field
     *
     * @param array $rowData
     * @param $field
     * @return array
     */
    protected function prepareMultipleField(array $rowData, $field)
    {
        if (isset($rowData[$field])) {
            $separator = isset($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR]) ?
                $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR] :
                Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;

            $rowData[$field] = array_filter(explode($separator, $rowData[$field]));
        }

        return $rowData;
    }

    /**
     * Retrieve Identifiers By Customer Group Identifier
     *
     * @param $customerGroupId
     * @return array
     */
    protected function getIdsByCustomerGroupId($customerGroupId)
    {
        $column = static::COLUMN_CUSTOMER_GROUP_ID;
        $bind = [':'.$column => $customerGroupId];

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where($column.' = :'.$column);

        return $this->_connection->fetchCol($select, $bind);
    }

    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $toCreate = [];
            $toUpdate = [];
            $toDelete = [];

            foreach ($bunch as $rowNumber => $rowData) {
                $rowData = $this->prepareRowData($rowData);
                /* validate data */
                if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }
                /* behavior selector */
                switch ($this->getBehavior()) {
                    case Import::BEHAVIOR_DELETE:
                        $data = $this->_getIdForDelete($rowData);
                        if (is_array($data)) {
                            $toDelete = array_merge($toDelete, $data);
                        } else {
                            $toDelete[] = $data;
                        }
                        break;
                    case Import::BEHAVIOR_REPLACE:
                        $data = $this->_prepareDataForReplace($rowData);
                        $toUpdate = array_merge($toUpdate, $data[self::ENTITIES_TO_UPDATE_KEY]);
                        break;
                    case Import::BEHAVIOR_ADD_UPDATE:
                        $data = $this->_prepareDataForUpdate($rowData);
                        $toCreate = array_merge($toCreate, $data[self::ENTITIES_TO_CREATE_KEY]);
                        $toUpdate = array_merge($toUpdate, $data[self::ENTITIES_TO_UPDATE_KEY]);

                        if (isset($data[self::ENTITIES_TO_DELETE_KEY])) {
                            $toDelete = array_merge($toDelete, $data[self::ENTITIES_TO_DELETE_KEY]);
                        }
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
     * Retrieve Table Field Names
     *
     * @return array
     */
    protected function getTableFieldNames()
    {
        return $this->_mainTable ? parent::getTableFieldNames() : [];
    }
}
