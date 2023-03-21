<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\CompanyRole;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\RoleInterface;
use Magento\Company\Model\ResourceModel\Role;
use Magento\Company\Model\ResourceModel\UserRole;

/**
 * @method Role getDataCompanyRoleResourceModel
 * @method UserRole getDataCompanyRoleUserResourceModel
 */
class CustomerAdvanced extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CompanyCustomerInterface::CUSTOMER_ID;
    const COLUMN_COMPANY_ID = CompanyCustomerInterface::COMPANY_ID;

    public function getAllFields()
    {
        return [];
    }

    /**
     * Get Company Identifier By Role Identifier
     *
     * @param integer $roleId
     * @return integer
     */
    protected function getCompanyIdByRoleId($roleId)
    {
        $select = $this->_connection->select()
            ->from(
                $this->getTableFromModel($this->getDataCompanyRoleResourceModel()),
                RoleInterface::COMPANY_ID
            )
            ->where(RoleInterface::ROLE_ID.' = ?', $roleId);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Get User Identifiers By Role Identifier
     *
     * @param $roleId
     * @return array
     */
    protected function getUserIdsByRoleId($roleId)
    {
        $select = $this->_connection->select()
            ->from(
                $this->getTableFromModel($this->getDataCompanyRoleUserResourceModel()),
                'user_id'
            )
            ->where('role_id = ?', $roleId);

        return $this->_connection->fetchCol($select);
    }

    /**
     * Get Existing In Database User Identifiers
     *
     * @param array $userIds
     * @return array
     */
    protected function getIdsByUserIds($userIds)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_ENTITY_ID.' IN (?)', $userIds);

        return $this->_connection->fetchCol($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $roleId = $this->getCompanyRoleIdFromMap();
        if ($roleId) {
            $companyId = $this->getCompanyIdByRoleId($roleId);
            $userIds = $this->getUserIdsByRoleId($roleId);

            if (!empty($userIds)) {
                $ids = $this->getIdsByUserIds($userIds);
                foreach ($userIds as $userId) {
                    $entityRow = [
                        static::COLUMN_ENTITY_ID => $userId,
                        static::COLUMN_COMPANY_ID => $companyId
                    ];

                    /* prepare data */
                    if (!in_array($userId, $ids)) {
                        $toCreate[] = $entityRow;
                    } else {
                        $toUpdate[] = $entityRow;
                    }
                }
            }
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }
}
