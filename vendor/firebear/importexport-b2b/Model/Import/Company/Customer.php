<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

use Magento\Framework\Stdlib\DateTime;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class Customer extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = 'entity_id';
    const COLUMN_WEBSITE_ID = CustomerInterface::WEBSITE_ID;
    const COLUMN_EMAIL = CustomerInterface::EMAIL;
    const COLUMN_GROUP_ID = CustomerInterface::GROUP_ID;
    const COLUMN_FIRSTNAME = CustomerInterface::FIRSTNAME;
    const COLUMN_LASTNAME = CustomerInterface::LASTNAME;
    const COLUMN_IS_ACTIVE = 'is_active';
    const COLUMN_LOCK_EXPIRES = 'lock_expires';

    /**
     * Error Codes
     */
    const ERROR_WEBSITE_ID_IS_EMPTY = 'customerWebsiteIdIsEmpty';
    const ERROR_WEBSITE_ID_IS_INVALID = 'customerWebsiteIdIsInvalid';
    const ERROR_EMAIL_IS_EMPTY = 'customerEmailIsEmpty';
    const ERROR_EMAIL_IS_INVALID = 'customerEmailIsInvalid';
    const ERROR_FIRSTNAME_IS_EMPTY = 'customerFirstNameIsEmpty';
    const ERROR_LASTNAME_IS_EMPTY = 'customerLastNameIsEmpty';

    protected $_messageTemplates = [
        self::ERROR_WEBSITE_ID_IS_EMPTY => 'Customer %s is empty',
        self::ERROR_WEBSITE_ID_IS_INVALID => 'Customer %s is invalid',
        self::ERROR_EMAIL_IS_EMPTY => 'Customer %s is empty',
        self::ERROR_EMAIL_IS_INVALID => 'Customer %s is invalid',
        self::ERROR_FIRSTNAME_IS_EMPTY => 'Customer %s is empty',
        self::ERROR_LASTNAME_IS_EMPTY => 'Customer %s is empty'
    ];

    /**
     * Website Table
     *
     * @var string
     */
    protected $_websiteTable = 'store_website';

    public function getAllFields()
    {
        return [
            CustomerInterface::WEBSITE_ID,
            CustomerInterface::EMAIL,
            CustomerInterface::PREFIX,
            CustomerInterface::FIRSTNAME,
            CustomerInterface::MIDDLENAME,
            CustomerInterface::LASTNAME,
            CustomerInterface::SUFFIX,
            CustomerInterface::GENDER,
            self::COLUMN_IS_ACTIVE,
            self::COLUMN_LOCK_EXPIRES
        ];
    }

    /**
     * Retrieve Website Table Name
     *
     * @return string
     */
    protected function getWebsiteTable()
    {
        return $this->_resource->getTableName(
            $this->_websiteTable
        );
    }

    protected function getExistEntityId(array $rowData)
    {
        $columnWebsiteId = static::COLUMN_WEBSITE_ID;
        $columnEmail = static::COLUMN_EMAIL;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where($columnWebsiteId.' = ?', $rowData[$columnWebsiteId])
            ->where($columnEmail.' = ?', $rowData[$columnEmail]);

        return $this->_connection->fetchOne($select);
    }

    protected function getDataForExistEntity($entityId)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), [static::COLUMN_IS_ACTIVE, static::COLUMN_LOCK_EXPIRES])
            ->where(static::COLUMN_ENTITY_ID.' = ?', $entityId);

        return $this->_connection->fetchRow($select);
    }

    /**
     * Get Group Identifier By Website Identifier
     *
     * @param $websiteId
     * @return bool|int
     */
    protected function getGroupIdByWebsiteId($websiteId)
    {
        $column = 'website_id';
        $bind = [':'.$column => $websiteId];

        $select = $this->_connection->select()
            ->from($this->getWebsiteTable(), 'default_group_id')
            ->where($column.' = :'.$column);

        return $this->_connection->fetchOne($select, $bind);
    }

    protected function checkUniqueKey(array $rowData, $rowNumber)
    {
        $columnWebsiteId = static::COLUMN_WEBSITE_ID;
        $columnEmail = static::COLUMN_EMAIL;

        if (empty($rowData[$columnWebsiteId])) {
            $this->addRowError(static::ERROR_WEBSITE_ID_IS_EMPTY, $rowNumber, $columnWebsiteId);
        } elseif (!$this->validateForeignKey($columnWebsiteId, $rowData[$columnWebsiteId])) {
            $this->addRowError(static::ERROR_WEBSITE_ID_IS_INVALID, $rowNumber, $columnWebsiteId);
        }

        if (empty($rowData[$columnEmail])) {
            $this->addRowError(static::ERROR_EMAIL_IS_EMPTY, $rowNumber, $columnEmail);
        } elseif (!$this->validateEmail(strtolower($rowData[$columnEmail]))) {
            $this->addRowError(static::ERROR_EMAIL_IS_INVALID, $rowNumber, $columnEmail);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        if (isset($rowData[static::COLUMN_EMAIL]) && $this->checkUniqueKey($rowData, $rowNumber)) {
            $entityId = $this->getExistEntityId($rowData);

            foreach ([
                static::COLUMN_FIRSTNAME => static::ERROR_FIRSTNAME_IS_EMPTY,
                static::COLUMN_LASTNAME => static::ERROR_LASTNAME_IS_EMPTY] as $column => $errorCode) {
                if ((isset($rowData[$column]) || !$entityId) && empty($rowData[$column])) {
                    $this->addRowError($errorCode, $rowNumber, $column);
                }
            }
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        if (isset($rowData[static::COLUMN_EMAIL])) {
            $newEntity = false;
            $entityId = $this->getExistEntityId($rowData);
            if (!$entityId) {
                /* create new entity id */
                $newEntity = true;
                $entityId = $this->_getNextEntityId();
            }

            $customerIdsMap = $this->storage->getCustomerIdsMap();
            $customerIdsMap[$this->_processedRowsCount] = $entityId;
            $this->storage->setCustomerIdsMap($customerIdsMap);

            $status = CompanyCustomerInterface::STATUS_ACTIVE;
            $lockExpires = null;

            $entityRow = [
                static::COLUMN_ENTITY_ID => $entityId,
            ];

            if ($newEntity) {
                $entityRow[static::COLUMN_GROUP_ID] = $this->getGroupIdByWebsiteId(
                    $rowData[static::COLUMN_WEBSITE_ID]
                );
            } else {
                $data = $this->getDataForExistEntity($entityId);
                $status = $data[static::COLUMN_IS_ACTIVE];
                $lockExpires = $data[static::COLUMN_LOCK_EXPIRES];
            }

            if (isset($rowData[static::COLUMN_IS_ACTIVE]) &&
                in_array($rowData[static::COLUMN_IS_ACTIVE], ['0', '1'])
            ) {
                $status = (int)$rowData[static::COLUMN_IS_ACTIVE];
            }

            if (!empty($rowData[static::COLUMN_LOCK_EXPIRES])) {
                $date = (new \DateTime())->setTimestamp(strtotime($rowData[static::COLUMN_LOCK_EXPIRES]));
                $lockExpires = $date->format(DateTime::DATETIME_PHP_FORMAT);
            }

            $entityRow[static::COLUMN_IS_ACTIVE] = $status;
            $entityRow[static::COLUMN_LOCK_EXPIRES] = $lockExpires;

            /* prepare data */
            $entityRow = $this->_prepareEntityRow($entityRow, $rowData);
            if ($newEntity) {
                $toCreate[] = $entityRow;
            } else {
                $toUpdate[] = $entityRow;
            }
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }
}
