<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\CompanyRole;

use Firebear\ImportExport\Model\Import\Context;
use Firebear\ImportExportB2b\Model\Import\AbstractAdapter as BaseAbstractAdapter;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\ImportExport\Model\Import;

abstract class AbstractAdapter extends BaseAbstractAdapter
{
    /**
     * Keys Which Used To Build Result Data Array For Future Update
     */
    const ENTITIES_TO_DELETE_KEY = 'entities_to_delete';

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
            ->setCompanyRoleIdsMap([]);
    }

    /**
     * Retrieve Company Role Id If Company Role Is Present In Database
     *
     * @return bool|int
     */
    protected function getCompanyRoleIdFromMap()
    {
        $companyRoleIdsMap = $this->storage->getCompanyRoleIdsMap();
        if (isset($companyRoleIdsMap[$this->_processedRowsCount])) {
            return $companyRoleIdsMap[$this->_processedRowsCount];
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
}
