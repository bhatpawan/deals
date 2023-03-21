<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\CompanyRole;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\ImportExport\Model\Import;
use Magento\Company\Api\Data\StructureInterface;

/**
 * Class CompanyStructure
 * @package Firebear\ImportExportB2b\Model\Import\CompanyRole
 */
class CompanyStructure extends AbstractAdapter
{
    /**
     * @return bool
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
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

                $companyEmail = $rowData[User::COLUMN_COMPANY_EMAIL];
                $customerEmails = explode(',', $rowData[User::COLUMN_USER_EMAILS]);
                foreach ($customerEmails as $customerEmail) {
                    $this->processCompanyStructureSave($companyEmail, $customerEmail);
                }

            }
        }
        return true;
    }

    /**
     * @param $companyEmail
     * @param $customerEmail
     */
    protected function processCompanyStructureSave($companyEmail, $customerEmail)
    {
        $superUserId = $this->getSuperUserIdByCompanyEmail($companyEmail);
        $customerId = $this->getCustomerIdByEmail($customerEmail);
        $path = $this->getPathByCustomerId($superUserId);

        if ($path && $customerId) {
            $companyStructureId = $this->isCompanyStructureForCustomer($customerId);

            if (($this->getBehavior() == Import::BEHAVIOR_ADD_UPDATE)) {
                if ($companyStructureId) {
                    $this->companyStructureDelete($companyStructureId);
                }
                $this->companyStructureSave($path, $customerId);
            }

            if (($this->getBehavior() == Import::BEHAVIOR_REPLACE)) {
                if ($companyStructureId) {
                    $this->companyStructureDelete($companyStructureId);
                    $this->companyStructureSave($path, $customerId);
                }
            }

            if (($this->getBehavior() == Import::BEHAVIOR_DELETE)) {
                if ($companyStructureId) {
                    $this->companyStructureDelete($companyStructureId);
                }
            }
        }
    }

    /**
     * @param $companyEmail
     * @return bool|string
     */
    protected function getSuperUserIdByCompanyEmail($companyEmail)
    {
        $companyTable = $this->_resource->getTableName('company');
        $select = $this->_connection->select();
        $select->from(['c' => $companyTable], 'c.'.CompanyInterface::SUPER_USER_ID)
            ->where('c.'.CompanyInterface::COMPANY_EMAIL.' = ?', $companyEmail);

        $result = $this->_connection->fetchOne($select);
        return $result ? $result : false;
    }

    /**
     * @param $customerEmail
     * @return bool|string
     */
    protected function getCustomerIdByEmail($customerEmail)
    {
        $customerTable = $this->_resource->getTableName('customer_entity');
        $select = $this->_connection->select();
        $select->from(['c' => $customerTable], 'c.entity_id')
            ->where('c.email = ?', $customerEmail);

        $result = $this->_connection->fetchOne($select);
        return $result ? $result : false;
    }

    /**
     * @param $customerId
     * @return bool|string
     */
    protected function getPathByCustomerId($customerId)
    {
        $companyStructureTable = $this->_resource->getTableName('company_structure');
        $select = $this->_connection->select();
        $select->from(['cs' => $companyStructureTable], 'cs.'.StructureInterface::PATH)
            ->where('cs.'.StructureInterface::ENTITY_ID.' = ?', $customerId);

        $result = $this->_connection->fetchOne($select);
        return $result ? $result : false;
    }

    /**
     * @param $customerId
     * @return bool|string
     */
    protected function isCompanyStructureForCustomer($customerId)
    {
        $companyStructureTable = $this->_resource->getTableName('company_structure');
        $select = $this->_connection->select();
        $select->from(['cs' => $companyStructureTable], 'cs.'.StructureInterface::STRUCTURE_ID)
            ->where('cs.'.StructureInterface::ENTITY_ID.' = ?', $customerId);

        $result = $this->_connection->fetchOne($select);

        return $result ? $result : false;
    }

    /**
     * @param array $rowData
     * @return array|bool
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        return true;
    }

    /**
     * @param $companyStructureId
     */
    protected function companyStructureDelete($companyStructureId)
    {
        $companyStructureTable = $this->_resource->getTableName('company_structure');
        try {
            $this->_connection->delete(
                $companyStructureTable,
                [StructureInterface::STRUCTURE_ID.' IN (?)' => $companyStructureId]
            );
        } catch (\Exception $exception) {
            $this->addLogWriteln(
                __('Issue on delete at %1 for bind %2', $companyStructureTable, $companyStructureId),
                $this->getOutput(),
                'error'
            );
            $this->_logger->critical($exception->getMessage());
        }
    }

    /**
     * @param $path
     * @param $customerId
     */
    protected function companyStructureSave($path, $customerId)
    {
        $bind[StructureInterface::PARENT_ID] = $path;
        $bind[StructureInterface::ENTITY_ID] = $customerId;
        $bind[StructureInterface::PATH] = "{$path}/{$customerId}";
        $bind[StructureInterface::LEVEL] = 1;

        $companyStructureTable = $this->_resource->getTableName('company_structure');
        try {
            $this->_connection->insertOnDuplicate(
                $companyStructureTable,
                $bind
            );
        } catch (\Exception $exception) {
            $this->addLogWriteln(
                __('Issue on create at %1 for bind %2', $companyStructureTable, $bind),
                $this->getOutput(),
                'error'
            );
            $this->_logger->critical($exception->getMessage());
        }
    }
}
