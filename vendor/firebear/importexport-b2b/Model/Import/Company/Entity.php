<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\Company\Source\Status;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Group;
use Magento\Directory\Model\ResourceModel\Region;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\User\Model\ResourceModel\User;
use Firebear\ImportExport\Model\Import\Context;

/**
 * @method User getDataAdminUserResourceModel
 * @method Status getDataCompanyStatus
 * @method Group getDataCustomerGroupResourceModel
 * @method Region getDataRegionResourceModel
 */
class Entity extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CompanyInterface::COMPANY_ID;
    const COLUMN_COMPANY_NAME = CompanyInterface::NAME;
    const COLUMN_COMPANY_EMAIL = CompanyInterface::COMPANY_EMAIL;
    const COLUMN_STATUS = CompanyInterface::STATUS;
    const COLUMN_SALES_REPRESENTATIVE_ID = CompanyInterface::SALES_REPRESENTATIVE_ID;
    const COLUMN_SALES_REPRESENTATIVE_EMAIL = 'sales_representative_email';
    const COLUMN_STREET = CompanyInterface::STREET;
    const COLUMN_CITY = CompanyInterface::CITY;
    const COLUMN_COUNTRY_ID = CompanyInterface::COUNTRY_ID;
    const COLUMN_REGION = CompanyInterface::REGION;
    const COLUMN_REGION_ID = CompanyInterface::REGION_ID;
    const COLUMN_POSTCODE = CompanyInterface::POSTCODE;
    const COLUMN_TELEPHONE = CompanyInterface::TELEPHONE;
    const COLUMN_CUSTOMER_GROUP_ID = CompanyInterface::CUSTOMER_GROUP_ID;
    const COLUMN_CUSTOMER_GROUP_CODE = 'customer_group_code';
    const COLUMN_SUPER_USER_ID = CompanyInterface::SUPER_USER_ID;

    /**
     * Error Codes
     */
    const ERROR_COMPANY_NAME_IS_EMPTY = 'companyNameIsEmpty';
    const ERROR_STATUS_IS_INVALID = 'companyStatusIsInvalid';
    const ERROR_COMPANY_EMAIL_IS_EMPTY = 'companyEmailIsEmpty';
    const ERROR_COMPANY_EMAIL_IS_INVALID = 'companyEmailIsInvalid';
    const ERROR_SALES_REPRESENTATIVE_USER_NOT_FOUND = 'companySalesRepresentativeUserNotFound';
    const ERROR_STREET_IS_EMPTY = 'companyStreetIsEmpty';
    const ERROR_CITY_IS_EMPTY = 'companyCityIsEmpty';
    const ERROR_COUNTRY_ID_IS_EMPTY = 'companyCountryIdIsEmpty';
    const ERROR_COUNTRY_ID_IS_INVALID = 'companyCountryIdIsInvalid';
    const ERROR_REGION_ID_IS_INVALID = 'companyRegionIdIsInvalid';
    const ERROR_REGION_IS_REQUIRED = 'companyRegionIsRequired';
    const ERROR_POSTCODE_IS_EMPTY = 'companyPostcodeIsEmpty';
    const ERROR_TELEPHONE_IS_EMPTY = 'companyTelephoneIsEmpty';
    const ERROR_CUSTOMER_GROUP_NOT_FOUND = 'companyCustomerGroupNotFound';
    const ERROR_COMPANY_NOT_FOUND = 'companyNotFound';
    const ERROR_COMPANY_ADMIN_NOT_FOUND = 'companyAdminNotFound';

    protected $_messageTemplates = [
        self::ERROR_COMPANY_NAME_IS_EMPTY => 'Company %s is empty',
        self::ERROR_STATUS_IS_INVALID => 'Company %s is invalid',
        self::ERROR_COMPANY_EMAIL_IS_EMPTY => 'Company %s is empty',
        self::ERROR_COMPANY_EMAIL_IS_INVALID => 'Company %s is invalid',
        self::ERROR_SALES_REPRESENTATIVE_USER_NOT_FOUND => 'Company sales representative user not found',
        self::ERROR_STREET_IS_EMPTY => 'Company %s is empty',
        self::ERROR_CITY_IS_EMPTY => 'Company %s is empty',
        self::ERROR_COUNTRY_ID_IS_EMPTY => 'Company %s is empty',
        self::ERROR_COUNTRY_ID_IS_INVALID => 'Company %s is invalid',
        self::ERROR_REGION_ID_IS_INVALID => 'Company %s is invalid',
        self::ERROR_REGION_IS_REQUIRED => 'Region is required',
        self::ERROR_POSTCODE_IS_EMPTY => 'Company %s is empty',
        self::ERROR_TELEPHONE_IS_EMPTY => 'Company %s is empty',
        self::ERROR_CUSTOMER_GROUP_NOT_FOUND => 'Company customer group not found',
        self::ERROR_COMPANY_NOT_FOUND => 'Company not found',
        self::ERROR_COMPANY_ADMIN_NOT_FOUND => 'Company admin not found'
    ];

    /**
     * Directory Helper
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    protected $enableReplace = true;

    /**
     * @param Context $context
     * @param AbstractResource $resourceModel
     * @param Storage $storage
     * @param DirectoryHelper $directoryHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        AbstractResource $resourceModel,
        Storage $storage,
        DirectoryHelper $directoryHelper,
        array $data = []
    ) {
        $this->directoryHelper = $directoryHelper;

        parent::__construct(
            $context,
            $resourceModel,
            $storage,
            $data
        );
    }

    public function getAllFields()
    {
        return [
            CompanyInterface::NAME,
            CompanyInterface::STATUS,
            CompanyInterface::COMPANY_EMAIL,
            CompanyInterface::SALES_REPRESENTATIVE_ID,
            CompanyInterface::LEGAL_NAME,
            CompanyInterface::VAT_TAX_ID,
            CompanyInterface::RESELLER_ID,
            CompanyInterface::COMMENT,
            CompanyInterface::STREET,
            CompanyInterface::CITY,
            CompanyInterface::COUNTRY_ID,
            CompanyInterface::REGION,
            CompanyInterface::REGION_ID,
            CompanyInterface::POSTCODE,
            CompanyInterface::TELEPHONE,
            CompanyInterface::CUSTOMER_GROUP_ID,
            CompanyInterface::REJECT_REASON
        ];
    }

    public function prepareRowData(array $rowData)
    {
        $rowData = parent::prepareRowData($rowData);

        $columnSalesRepresentativeId = static::COLUMN_SALES_REPRESENTATIVE_ID;
        $columnSalesRepresentativeEmail = static::COLUMN_SALES_REPRESENTATIVE_EMAIL;

        if (empty($rowData[$columnSalesRepresentativeId]) && isset($rowData[$columnSalesRepresentativeEmail])) {
            $rowData[$columnSalesRepresentativeId] = '';

            if (!empty($rowData[$columnSalesRepresentativeEmail])) {
                $rowData[$columnSalesRepresentativeId] = $this->getAdminUserIdByEmail(
                    $rowData[$columnSalesRepresentativeEmail]
                ) ?: '';
            }
        }

        $columnCustomerGroupId = static::COLUMN_CUSTOMER_GROUP_ID;
        $columnCustomerGroupCode = static::COLUMN_CUSTOMER_GROUP_CODE;

        if (empty($rowData[$columnCustomerGroupId]) && isset($rowData[$columnCustomerGroupCode])) {
            $rowData[$columnCustomerGroupId] = '';

            if (!empty($rowData[$columnCustomerGroupCode])) {
                $rowData[$columnCustomerGroupId] = $this->getCustomerGroupIdByCode(
                    $rowData[$columnCustomerGroupCode]
                ) ?: '';
            }
        }

        $columnCountryId = static::COLUMN_COUNTRY_ID;
        $columnRegion = static::COLUMN_REGION;
        $columnRegionId = static::COLUMN_REGION_ID;

        if (empty($rowData[$columnRegionId]) && isset($rowData[$columnCountryId], $rowData[$columnRegion])) {
            $rowData[$columnRegionId] = '';

            if (!empty($rowData[$columnCountryId]) && !empty($rowData[$columnRegion])) {
                $rowData[$columnRegionId] = $this->getRegionIdByName(
                    $rowData[$columnRegion],
                    $rowData[$columnCountryId]
                ) ?: '';
            }
        }

        return $rowData;
    }

    protected function getExistEntityId(array $rowData)
    {
        $columnEmail = static::COLUMN_COMPANY_EMAIL;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where($columnEmail.' = ?', $rowData[$columnEmail]);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Validate Status
     *
     * @param string $status
     * @return bool
     */
    protected function validateStatus($status)
    {
        foreach ($this->getDataCompanyStatus()->toOptionArray() as $option) {
            if ($option['value'] == $status) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate Region Identifier
     *
     * @param integer $regionId
     * @param integer $countryId
     * @return bool|int
     */
    protected function validateRegionId($regionId, $countryId)
    {
        $select = $this->_connection->select()
            ->from(
                $this->getTableFromModel($this->getDataRegionResourceModel()),
                'region_id'
            )
            ->where('region_id = ?', $regionId)
            ->where('country_id = ?', $countryId);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Retrieve Administrator's Identifier By Company Identifier
     *
     * @param integer $companyId
     * @return bool|int
     */
    protected function getOldCustomerId($companyId)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_SUPER_USER_ID)
            ->where(static::COLUMN_ENTITY_ID.' = ?', $companyId);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Retrieve Admin User Identifier By Email
     *
     * @param string $email
     * @return bool|int
     */
    protected function getAdminUserIdByEmail($email)
    {
        $select = $this->_connection->select()
            ->from(
                $this->getTableFromModel($this->getDataAdminUserResourceModel()),
                'user_id'
            )
            ->where('email = ?', $email);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Retrieve Customer Group Identifier By Code
     *
     * @param string $code
     * @return bool|int
     */
    protected function getCustomerGroupIdByCode($code)
    {
        $select = $this->_connection->select()
            ->from(
                $this->getTableFromModel($this->getDataCustomerGroupResourceModel()),
                'customer_group_id'
            )
            ->where('customer_group_code = ?', $code);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Retrieve Region Identifier By Name
     *
     * @param string $regionName
     * @param integer $countryId
     * @return bool|int
     */
    protected function getRegionIdByName($regionName, $countryId)
    {
        $select = $this->_connection->select()
            ->from(
                $this->getTableFromModel($this->getDataRegionResourceModel()),
                'region_id'
            )
            ->where('default_name = ?', $regionName)
            ->where('country_id = ?', $countryId);

        return $this->_connection->fetchOne($select);
    }

    protected function checkUniqueKey(array $rowData, $rowNumber)
    {
        $columnEmail = static::COLUMN_COMPANY_EMAIL;
        if (empty($rowData[$columnEmail])) {
            $this->addRowError(static::ERROR_COMPANY_EMAIL_IS_EMPTY, $rowNumber, $columnEmail);
        } elseif (!$this->validateEmail(strtolower($rowData[$columnEmail]))) {
            $this->addRowError(static::ERROR_COMPANY_EMAIL_IS_INVALID, $rowNumber, $columnEmail);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        if ($this->checkUniqueKey($rowData, $rowNumber)) {
            $entityId = $this->getExistEntityId($rowData);

            foreach ([
                static::COLUMN_COMPANY_NAME => static::ERROR_COMPANY_NAME_IS_EMPTY,
                static::COLUMN_STREET => static::ERROR_STREET_IS_EMPTY,
                static::COLUMN_CITY => static::ERROR_CITY_IS_EMPTY,
                static::COLUMN_COUNTRY_ID => static::ERROR_COUNTRY_ID_IS_EMPTY,
                static::COLUMN_POSTCODE => static::ERROR_POSTCODE_IS_EMPTY,
                static::COLUMN_TELEPHONE => static::ERROR_TELEPHONE_IS_EMPTY,
                static::COLUMN_CUSTOMER_GROUP_ID => static::ERROR_CUSTOMER_GROUP_NOT_FOUND] as $column => $errorCode) {
                if ((isset($rowData[$column]) || !$entityId) && empty($rowData[$column])) {
                    $this->addRowError($errorCode, $rowNumber, $column);
                }
            }

            if (!$entityId && empty($rowData[CustomerInterface::EMAIL])) {
                $this->addRowError(static::ERROR_COMPANY_ADMIN_NOT_FOUND, $rowNumber);
            }

            $columnStatus = static::COLUMN_STATUS;
            if (!empty($rowData[$columnStatus]) && !$this->validateStatus($rowData[$columnStatus])) {
                $this->addRowError(static::ERROR_STATUS_IS_INVALID, $rowNumber, $columnStatus);
            }

            $columnSalesRepresentativeId = static::COLUMN_SALES_REPRESENTATIVE_ID;
            $columnSalesRepresentativeEmail = static::COLUMN_SALES_REPRESENTATIVE_EMAIL;

            if (empty($rowData[$columnSalesRepresentativeId])) {
                if (!empty($rowData[$columnSalesRepresentativeEmail])) {
                    $this->addRowError(static::ERROR_SALES_REPRESENTATIVE_USER_NOT_FOUND, $rowNumber);
                }
            } elseif (!$this->validateForeignKey(
                $columnSalesRepresentativeId,
                $rowData[$columnSalesRepresentativeId]
            )) {
                $this->addRowError(static::ERROR_SALES_REPRESENTATIVE_USER_NOT_FOUND, $rowNumber);
            }

            $columnCountryId = static::COLUMN_COUNTRY_ID;
            if (!empty($rowData[$columnCountryId]) &&
                !$this->validateForeignKey($columnCountryId, $rowData[$columnCountryId])
            ) {
                $this->addRowError(static::ERROR_COUNTRY_ID_IS_INVALID, $rowNumber, $columnCountryId);
            }

            $columnRegionId = static::COLUMN_REGION_ID;
            if (!empty($rowData[$columnRegionId]) && (
                    empty($rowData[$columnCountryId]) ||
                    !$this->validateRegionId($rowData[$columnRegionId], $rowData[$columnCountryId])
                )
            ) {
                $this->addRowError(static::ERROR_REGION_ID_IS_INVALID, $rowNumber, $columnRegionId);
            }

            $columnRegion = static::COLUMN_REGION;
            if (empty($rowData[$columnRegion]) &&
                empty($rowData[$columnRegionId]) &&
                !empty($rowData[$columnCountryId])
            ) {
                if ($this->directoryHelper->isRegionRequired($rowData[$columnCountryId])) {
                    $this->addRowError(static::ERROR_REGION_IS_REQUIRED, $rowNumber, $columnRegion);
                }
            }

            $columnCustomerGroupId = static::COLUMN_CUSTOMER_GROUP_ID;
            if (!empty($rowData[$columnCustomerGroupId]) &&
                !$this->validateForeignKey($columnCustomerGroupId, $rowData[$columnCustomerGroupId])
            ) {
                $this->addRowError(static::ERROR_CUSTOMER_GROUP_NOT_FOUND, $rowNumber);
            }
        }
    }

    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->checkUniqueKey($rowData, $rowNumber)) {
            $entityId = $this->getExistEntityId($rowData);

            if ($entityId) {
                $companyIdsMap = $this->storage->getCompanyIdsMap();
                $companyIdsMap[$this->_processedRowsCount] = $entityId;
                $this->storage->setCompanyIdsMap($companyIdsMap);
            } else {
                $this->addRowError(static::ERROR_COMPANY_NOT_FOUND, $rowNumber);
            }
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $entityId = $this->getExistEntityId($rowData);
        $customerId = $this->getCustomerIdFromMap();

        $newEntity = false;
        if ($entityId) {
            $oldCustomerIdsMap = $this->storage->getOldCustomerIdsMap();
            $oldCustomerIdsMap[$this->_processedRowsCount] = $this->getOldCustomerId($entityId);
            $this->storage->setOldCustomerIdsMap($oldCustomerIdsMap);
        } else {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
        }

        $companyIdsMap = $this->storage->getCompanyIdsMap();
        $companyIdsMap[$this->_processedRowsCount] = $entityId;
        $this->storage->setCompanyIdsMap($companyIdsMap);

        $entityRow = [
            static::COLUMN_ENTITY_ID => $entityId
        ];
        if ($customerId) {
            $entityRow[static::COLUMN_SUPER_USER_ID] = $customerId;
        }

        /* prepare data */
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
