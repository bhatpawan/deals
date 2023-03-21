<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\CompanyRole;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\ResourceModel\Company;

/**
 * @method Customer getDataCustomerResourceModel
 * @method Company getDataCompanyResourceModel
 */
class User extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = 'user_role_id';
    const COLUMN_ROLE_ID = 'role_id';
    const COLUMN_USER_ID = 'user_id';
    const COLUMN_USER_EMAILS = 'user_emails';
    const COLUMN_COMPANY_EMAIL = 'company_email';

    /**
     * Error Codes
     */
    const ERROR_USER_NOT_FOUND = 'companyRoleUserNotFound';
    const ERROR_ADMIN_ROLE = 'companyRoleUserAdminRole';

    protected $_messageTemplates = [
        self::ERROR_USER_NOT_FOUND => 'User with email %s not found',
        self::ERROR_ADMIN_ROLE => 'You cannot change role of administrator with email %s'
    ];

    /**
     * Retrieve All Fields Source
     *
     * @return array
     */
    public function getAllFields()
    {
        return [
            static::COLUMN_USER_EMAILS
        ];
    }

    /**
     * Retrieve The Prepared Data
     *
     * @param array $rowData
     * @return array|bool
     */
    public function prepareRowData(array $rowData)
    {
        $rowData = parent::prepareRowData($rowData);
        if ($rowData) {
            $rowData = $this->prepareMultipleField($rowData, static::COLUMN_USER_EMAILS);
        }

        return $rowData;
    }

    /**
     * Get User Role Identifiers By Role Identifier
     *
     * @return array
     */
    protected function getIdsByRoleId()
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_ROLE_ID.' = ?', $this->getCompanyRoleIdFromMap());

        return $this->_connection->fetchCol($select);
    }

    /**
     * Get User Role Identifiers By User Identifiers
     *
     * @param array $userIds
     * @return array
     */
    protected function getIdsByUserIds($userIds)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), [static::COLUMN_USER_ID, static::COLUMN_ENTITY_ID])
            ->where(static::COLUMN_USER_ID.' IN (?)', $userIds);

        return $this->_connection->fetchPairs($select);
    }

    /**
     * Get User Identifiers By Emails
     *
     * @param array $emails
     * @return array
     */
    protected function getUserIdsByEmails($emails)
    {
        $select = $this->_connection->select()
            ->from(
                $this->getTableFromModel($this->getDataCustomerResourceModel()),
                [CustomerInterface::EMAIL, 'entity_id']
            )
            ->where(CustomerInterface::EMAIL.' IN (?)', $emails);

        return $this->_connection->fetchPairs($select);
    }

    /**
     * Retrieve Super User Id By Company Email
     *
     * @param string $email
     * @return bool|int
     */
    protected function getSuperUserIdByCompanyEmail($email)
    {
        $select = $this->_connection->select()
            ->from(
                $this->getTableFromModel($this->getDataCompanyResourceModel()),
                CompanyInterface::SUPER_USER_ID
            )
            ->where(CompanyInterface::COMPANY_EMAIL.' = ?', $email);

        return $this->_connection->fetchOne($select);
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
        $columnUserEmails = static::COLUMN_USER_EMAILS;
        $columnCompanyEmail = static::COLUMN_COMPANY_EMAIL;
        if (!empty($rowData[$columnUserEmails])) {
            $userIdsByEmails = $this->getUserIdsByEmails($rowData[$columnUserEmails]);
            $superUserId = $this->getSuperUserIdByCompanyEmail($rowData[$columnCompanyEmail]);

            foreach ($rowData[$columnUserEmails] as $email) {
                if (!isset($userIdsByEmails[$email])) {
                    $this->addRowError(static::ERROR_USER_NOT_FOUND, $rowNumber, $email);
                    return;
                }
                if ($superUserId == $userIdsByEmails[$email]) {
                    $this->addRowError(static::ERROR_ADMIN_ROLE, $rowNumber, $email);
                }
            }
        }
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
        $toDelete = [];

        $columnUserEmails = static::COLUMN_USER_EMAILS;
        $roleId = $this->getCompanyRoleIdFromMap();

        if ($roleId && isset($rowData[$columnUserEmails])) {
            $idsByRoleId = $this->getIdsByRoleId();
            $idsByUserIds = [];

            if (!empty($rowData[$columnUserEmails])) {
                $userIdsByEmails = $this->getUserIdsByEmails($rowData[$columnUserEmails]);
                $idsByUserIds = $this->getIdsByUserIds($userIdsByEmails);

                foreach ($rowData[$columnUserEmails] as $email) {
                    $userId = $userIdsByEmails[$email];
                    $newEntity = false;
                    $entityId = isset($idsByUserIds[$userId]) ? $idsByUserIds[$userId] : false;
                    if (!$entityId) {
                        /* create new entity id */
                        $newEntity = true;
                        $entityId = $this->_getNextEntityId();
                    }

                    $entityRow = [
                        static::COLUMN_ENTITY_ID => $entityId,
                        static::COLUMN_ROLE_ID => $roleId,
                        static::COLUMN_USER_ID => $userId
                    ];

                    /* prepare data */
                    if ($newEntity) {
                        $toCreate[] = $entityRow;
                    } else {
                        $toUpdate[] = $entityRow;
                    }
                }
            }

            $toDelete = array_diff($idsByRoleId, $idsByUserIds);
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate,
            static::ENTITIES_TO_DELETE_KEY => $toDelete
        ];
    }
}
