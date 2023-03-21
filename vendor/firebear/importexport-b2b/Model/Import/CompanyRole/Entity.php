<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\CompanyRole;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\RoleInterface;
use Magento\Company\Model\ResourceModel\Company;

/**
 * @method Company getDataCompanyResourceModel
 */
class Entity extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = RoleInterface::ROLE_ID;
    const COLUMN_ROLE_NAME = RoleInterface::ROLE_NAME;
    const COLUMN_COMPANY_ID = RoleInterface::COMPANY_ID;
    const COLUMN_COMPANY_EMAIL = 'company_email';

    /**
     * Error Codes
     */
    const ERROR_ROLE_NAME_IS_EMPTY = 'companyRoleNameIsEmpty';
    const ERROR_COMPANY_EMAIL_IS_EMPTY = 'companyRoleCompanyEmailIsEmpty';
    const ERROR_COMPANY_NOT_FOUND = 'companyNotFound';
    const ERROR_COMPANY_ROLE_NOT_FOUND = 'companyRoleNotFound';

    protected $_messageTemplates = [
        self::ERROR_ROLE_NAME_IS_EMPTY => 'Company role %s is empty',
        self::ERROR_COMPANY_EMAIL_IS_EMPTY => 'Company role %s is empty',
        self::ERROR_COMPANY_NOT_FOUND => 'Company not found',
        self::ERROR_COMPANY_ROLE_NOT_FOUND => 'Company role not found'
    ];

    protected $enableReplace = true;

    public function getAllFields()
    {
        return [
            RoleInterface::ROLE_NAME,
            static::COLUMN_COMPANY_EMAIL
        ];
    }

    protected function getExistEntityId(array $rowData)
    {
        $columnRoleName = static::COLUMN_ROLE_NAME;
        $companyId = $this->getCompanyIdByEmail($rowData[static::COLUMN_COMPANY_EMAIL]);

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where($columnRoleName.' = ?', $rowData[$columnRoleName])
            ->where(static::COLUMN_COMPANY_ID.' = ?', $companyId);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Retrieve Company Identifier By Email
     *
     * @param string $email
     * @return bool|int
     */
    protected function getCompanyIdByEmail($email)
    {
        $select = $this->_connection->select()
            ->from(
                $this->getTableFromModel($this->getDataCompanyResourceModel()),
                CompanyInterface::COMPANY_ID
            )
            ->where(CompanyInterface::COMPANY_EMAIL.' = ?', $email);

        return $this->_connection->fetchOne($select);
    }

    protected function checkUniqueKey(array $rowData, $rowNumber)
    {
        $columnRoleName = static::COLUMN_ROLE_NAME;
        $columnCompanyEmail = static::COLUMN_COMPANY_EMAIL;

        if (empty($rowData[$columnRoleName])) {
            $this->addRowError(static::ERROR_ROLE_NAME_IS_EMPTY, $rowNumber, $columnRoleName);
        }

        if (empty($rowData[$columnCompanyEmail])) {
            $this->addRowError(static::ERROR_COMPANY_EMAIL_IS_EMPTY, $rowNumber, $columnCompanyEmail);
        } elseif (!$this->getCompanyIdByEmail($rowData[$columnCompanyEmail])) {
            $this->addRowError(static::ERROR_COMPANY_NOT_FOUND, $rowNumber);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        return $this->checkUniqueKey($rowData, $rowNumber);
    }

    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->checkUniqueKey($rowData, $rowNumber)) {
            $entityId = $this->getExistEntityId($rowData);

            if (!$entityId) {
                $this->addRowError(static::ERROR_COMPANY_ROLE_NOT_FOUND, $rowNumber);
            }
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $newEntity = false;
        $entityId = $this->getExistEntityId($rowData);
        if (!$entityId) {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
        }

        $companyRoleIdsMap = $this->storage->getCompanyRoleIdsMap();
        $companyRoleIdsMap[$this->_processedRowsCount] = $entityId;
        $this->storage->setCompanyRoleIdsMap($companyRoleIdsMap);

        $entityRow = [
            static::COLUMN_ENTITY_ID => $entityId,
            static::COLUMN_COMPANY_ID => $this->getCompanyIdByEmail(
                $rowData[static::COLUMN_COMPANY_EMAIL]
            )
        ];

        /* prepare data */
        unset($rowData[static::COLUMN_COMPANY_EMAIL]);
        $entityRow = $this->_prepareEntityRow($entityRow, $rowData);
        if ($newEntity) {
            $toCreate[] = $entityRow;
        } else {
            $toUpdate[] = $entityRow;
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    protected function _getIdForDelete(array $rowData)
    {
        return $this->getExistEntityId($rowData);
    }
}
