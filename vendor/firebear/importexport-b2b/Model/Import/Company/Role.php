<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

use Magento\Company\Api\Data\RoleInterface;
use Magento\Company\Model\Permission;
use Magento\Company\Model\PermissionManagementInterface;

/**
 * @method PermissionManagementInterface getDataPermissionManagement
 */
class Role extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = RoleInterface::ROLE_ID;
    const COLUMN_ROLE_NAME = RoleInterface::ROLE_NAME;
    const COLUMN_COMPANY_ID = RoleInterface::COMPANY_ID;

    /**
     * Permission Table
     *
     * @var string
     */
    protected $_permissionTable = 'company_permissions';

    public function getAllFields()
    {
        return [];
    }

    /**
     * Retrieve Permission Table Name
     *
     * @return string
     */
    protected function getPermissionTable()
    {
        return $this->_resource->getTableName(
            $this->_permissionTable
        );
    }

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_COMPANY_ID.' = ?', $this->getCompanyIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $companyId = $this->getCompanyIdFromMap();
        if ($companyId && !$this->getExistEntityId($rowData)) {
            $toCreate[] = [
                static::COLUMN_ENTITY_ID => $this->_getNextEntityId(),
                static::COLUMN_ROLE_NAME => 'Default User',
                static::COLUMN_COMPANY_ID => $companyId
            ];
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    protected function _saveEntities(array $toCreate, array $toUpdate)
    {
        parent::_saveEntities($toCreate, $toUpdate);

        if ($toCreate) {
            /** @var Permission[] $defaultPermissions */
            $defaultPermissions = $this->getDataPermissionManagement()->retrieveDefaultPermissions();

            $permissions = [];
            foreach ($toCreate as $role) {
                foreach ($defaultPermissions as $permission) {
                    $permissions[] = $permission->setRoleId($role[static::COLUMN_ENTITY_ID])->getData();
                }
            }

            $this->_connection->insertMultiple(
                $this->getPermissionTable(),
                $permissions
            );
        }
    }

    /**
     * @return bool
     */
    public function rowsCanBeSkipped(): bool
    {
        return false;
    }
}
