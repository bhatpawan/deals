<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\CompanyRole;

use Magento\Company\Api\Data\PermissionInterface;
use Magento\Framework\Acl\AclResource\ProviderInterface;

/**
 * @method ProviderInterface getDataResourceProvider
 */
class Permission extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = PermissionInterface::PERMISSION_ID;
    const COLUMN_ROLE_ID = PermissionInterface::ROLE_ID;
    const COLUMN_RESOURCE_ID = PermissionInterface::RESOURCE_ID;
    const COLUMN_PERMISSION = PermissionInterface::PERMISSION;
    const COLUMN_PERMISSIONS = 'permissions';

    /**
     * Error Codes
     */
    const ERROR_PERMISSION_NOT_FOUND = 'companyRolePermissionNotFound';

    protected $_messageTemplates = [
        self::ERROR_PERMISSION_NOT_FOUND => 'Permission %s not found'
    ];

    /**
     * ACL Company Resources
     *
     * @var array
     */
    protected $aclResources = [];

    public function getAllFields()
    {
        return [
            static::COLUMN_PERMISSIONS
        ];
    }

    public function prepareRowData(array $rowData)
    {
        $rowData = parent::prepareRowData($rowData);
        if ($rowData) {
            $rowData = $this->prepareMultipleField($rowData, static::COLUMN_PERMISSIONS);
        }

        return $rowData;
    }

    /**
     * Get Permission Identifiers
     *
     * @return array
     */
    protected function getPermissionIds()
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), [static::COLUMN_RESOURCE_ID, static::COLUMN_ENTITY_ID])
            ->where(static::COLUMN_ROLE_ID.' = ?', $this->getCompanyRoleIdFromMap());

        return $this->_connection->fetchPairs($select);
    }

    /**
     * Get ACL Company Resources
     *
     * @return array
     */
    protected function getAclResources()
    {
        if (empty($this->aclResources)) {
            $this->aclResources = $this->getFlatResources(
                $this->getDataResourceProvider()->getAclResources()
            );
        }

        return $this->aclResources;
    }

    /**
     * Get Flat Resources
     *
     * @param array $resources
     * @param array $parents [optional]
     * @return array
     */
    protected function getFlatResources(array $resources, array $parents = [])
    {
        $result = [];
        foreach ($resources as $resource) {
            $result[$resource['id']]['parents'] = $parents;
            $result[$resource['id']]['children'] = [];

            foreach ($parents as $parentId) {
                $result[$parentId]['children'][] = $resource['id'];
            }
            $parents[] = $resource['id'];
            if (!empty($resource['children'])) {
                $result = array_merge_recursive(
                    $result,
                    $this->getFlatResources($resource['children'], $parents)
                );
            }
            array_pop($parents);
        }

        return $result;
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        $columnPermissions = static::COLUMN_PERMISSIONS;
        if (!empty($rowData[$columnPermissions])) {
            $resources = $this->getAclResources();
            foreach ($rowData[$columnPermissions] as $permission) {
                if (!isset($resources[$permission])) {
                    $this->addRowError(static::ERROR_PERMISSION_NOT_FOUND, $rowNumber, $permission);
                }
            }
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $columnPermissions = static::COLUMN_PERMISSIONS;
        $roleId = $this->getCompanyRoleIdFromMap();

        if ($roleId && isset($rowData[$columnPermissions])) {
            $permissions = $rowData[$columnPermissions];
            $resources = $this->getAclResources();
            $permissionIds = $this->getPermissionIds();

            $parents = $children = [];
            foreach ($permissions as $permission) {
                $parents = array_merge($parents, $resources[$permission]['parents']);
                $children = array_merge($children, $resources[$permission]['children']);
            }
            $permissions = array_unique(array_merge($permissions, $parents, $children));

            foreach (array_keys($resources) as $resource) {
                $newEntity = false;
                $entityId = isset($permissionIds[$resource]) ? $permissionIds[$resource] : false;
                if (!$entityId) {
                    /* create new entity id */
                    $newEntity = true;
                    $entityId = $this->_getNextEntityId();
                }

                $entityRow = [
                    static::COLUMN_ENTITY_ID => $entityId,
                    static::COLUMN_ROLE_ID => $roleId,
                    static::COLUMN_RESOURCE_ID => $resource,
                    static::COLUMN_PERMISSION => in_array($resource, $permissions) ?
                        PermissionInterface::ALLOW_PERMISSION :
                        PermissionInterface::DENY_PERMISSION
                ];

                /* prepare data */
                if ($newEntity) {
                    $toCreate[] = $entityRow;
                } else {
                    $toUpdate[] = $entityRow;
                }
            }
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }
}
