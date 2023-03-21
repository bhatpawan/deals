<?php
/**
 * @copyright: Copyright © 2021 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\SharedCatalog;

use Firebear\ImportExport\Model\Import\Context;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Company\Model\CompanyManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class Company
 * @package Firebear\ImportExportB2b\Model\Import\SharedCatalog
 */
class Company extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CompanyInterface::COMPANY_ID;
    const COLUMN_COMPANY_EMAIL = CompanyInterface::COMPANY_EMAIL;
    const COLUMN_CUSTOMER_GROUP_ID = CompanyInterface::CUSTOMER_GROUP_ID;
    const COLUMN_COMPANY = 'company';

    /**
     * Error Codes
     */
    const ERROR_NOT_FOUND = 'companyNotFound';

    protected $_messageTemplates = [
        self::ERROR_NOT_FOUND => 'Company with email %s not found'
    ];

    /**
     * Shared Catalog Table
     *
     * @var string
     */
    protected $_sharedCatalogTable = 'shared_catalog';

    /**
     * @return string[]
     */
    public function getAllFields()
    {
        return [
            static::COLUMN_COMPANY
        ];
    }

    /**
     * @param array $rowData
     * @return array|bool
     */
    public function prepareRowData(array $rowData)
    {
        return $this->prepareMultipleField($rowData, static::COLUMN_COMPANY);
    }

    /**
     * Retrieve Shared Catalog Table Name
     *
     * @return string
     */
    protected function getSharedCatalogTable()
    {
        return $this->_resource->getTableName(
            $this->_sharedCatalogTable
        );
    }

    /**
     * Get Identifiers By Emails
     *
     * @param array $emails
     * @return array
     */
    protected function getIdsByEmails(array $emails)
    {
        $column = static::COLUMN_COMPANY_EMAIL;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), [static::COLUMN_ENTITY_ID, $column])
            ->where($column . ' IN (?)', $emails);

        return $this->_connection->fetchPairs($select);
    }

    /**
     * Get Base Customer Group Identifier
     *
     * @return bool|int
     */
    protected function getBaseCustomerGroupId()
    {
        $column = SharedCatalogInterface::TYPE;
        $bind = [':' . $column => SharedCatalogInterface::TYPE_PUBLIC];

        $select = $this->_connection->select()
            ->from($this->getSharedCatalogTable(), SharedCatalogInterface::CUSTOMER_GROUP_ID)
            ->where($column . ' = :' . $column);

        return $this->_connection->fetchOne($select, $bind);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        $columnCompany = static::COLUMN_COMPANY;
        if (isset($rowData[$columnCompany]) && !empty($rowData[$columnCompany])) {
            $idsByEmails = $this->getIdsByEmails($rowData[$columnCompany]);

            foreach ($rowData[$columnCompany] as $email) {
                if (!in_array($email, $idsByEmails)) {
                    $this->addRowError(static::ERROR_NOT_FOUND, $rowNumber, $email);
                }
            }
        }
    }

    /**
     * @param array $rowData
     * @return array
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $columnCompany = static::COLUMN_COMPANY;
        if (isset($rowData[$columnCompany])) {
            $customerGroupId = $this->getCustomerGroupIdFromMap($rowData);
            $idsByCustomerGroupId = $this->getIdsByCustomerGroupId($customerGroupId);
            $idsByEmails = !empty($rowData[$columnCompany]) ? $this->getIdsByEmails($rowData[$columnCompany]) : [];
            if ($idsByEmails && is_array($idsByEmails)) {
                $customersIds = $this->getAllCustomersByCompanies(array_keys($idsByEmails));
                $this->updateGroupByCustomerIds($customersIds, $customerGroupId);
            }

            if (!empty($idsByCustomerGroupId)) {
                $baseCustomerGroupId = $this->getBaseCustomerGroupId();
                if ($baseCustomerGroupId) {
                    foreach ($idsByCustomerGroupId as $id) {
                        $toUpdate[] = [
                            static::COLUMN_ENTITY_ID => $id,
                            static::COLUMN_CUSTOMER_GROUP_ID => $baseCustomerGroupId
                        ];
                    }
                }
            }

            foreach ($rowData[$columnCompany] as $email) {
                $toUpdate[] = [
                    static::COLUMN_ENTITY_ID => array_search($email, $idsByEmails),
                    static::COLUMN_CUSTOMER_GROUP_ID => $customerGroupId
                ];
            }
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    /**
     * @param array $companyIds
     * @return array
     */
    private function getAllCustomersByCompanies(array $companyIds)
    {
        $tableNameCompanyAdvancedCustomerEntity =
            $this->_resource->getTableName('company_advanced_customer_entity');
        $connection  = $this->_resource->getConnection();

        $query = $connection->select()
            ->from($tableNameCompanyAdvancedCustomerEntity, ['customer_id'])
            ->where('company_id in(?)', $companyIds);
        $customers = $connection->fetchAll($query);

        $customersIds = array_map(function ($v) {
            return $v['customer_id'] ?? null;
        }, $customers);

        return $customersIds;
    }

    /**
     * @param array $customerIds
     * @param $customerGroupId
     */
    private function updateGroupByCustomerIds(array $customerIds, $customerGroupId)
    {
        $connection = $this->_resource->getConnection();
        $tableNameCustomerEntity = $this->_resource->getTableName('customer_entity');
        $connection->update(
            $tableNameCustomerEntity,
            ['group_id' => $customerGroupId],
            ['entity_id in(?)' => $customerIds]
        );
    }
}
