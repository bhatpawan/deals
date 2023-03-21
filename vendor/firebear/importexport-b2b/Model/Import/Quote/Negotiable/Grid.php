<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote\Negotiable;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\TotalsInterface;

class Grid extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = QuoteGrid::QUOTE_ID;
    const COLUMN_QUOTE_NAME = QuoteGrid::QUOTE_NAME;
    const COLUMN_CREATED_AT = QuoteGrid::CREATED_AT;
    const COLUMN_COMPANY_ID = QuoteGrid::COMPANY_ID;
    const COLUMN_COMPANY_NAME = QuoteGrid::COMPANY_NAME;
    const COLUMN_CUSTOMER_ID = QuoteGrid::CUSTOMER_ID;
    const COLUMN_SUBMITTED_BY = QuoteGrid::SUBMITTED_BY;
    const COLUMN_UPDATED_AT = QuoteGrid::UPDATED_AT;
    const COLUMN_SALES_REP_ID = QuoteGrid::SALES_REP_ID;
    const COLUMN_SALES_REP = QuoteGrid::SALES_REP;
    const COLUMN_BASE_GRAND_TOTAL = QuoteGrid::BASE_GRAND_TOTAL;
    const COLUMN_GRAND_TOTAL = QuoteGrid::GRAND_TOTAL;
    const COLUMN_BASE_NEGOTIATED_GRAND_TOTAL = QuoteGrid::BASE_NEGOTIATED_GRAND_TOTAL;
    const COLUMN_NEGOTIATED_GRAND_TOTAL = QuoteGrid::NEGOTIATED_GRAND_TOTAL;
    const COLUMN_STATUS = QuoteGrid::QUOTE_STATUS;
    const COLUMN_BASE_CURRENCY_CODE = QuoteGrid::BASE_CURRENCY;
    const COLUMN_QUOTE_CURRENCY_CODE = QuoteGrid::CURRENCY;
    const COLUMN_STORE_ID = QuoteGrid::STORE_ID;
    const COLUMN_RATE = QuoteGrid::RATE;

    /**
     * Company Advanced Customer Entity Table
     *
     * @var string
     */
    protected $_companyAdvancedCustomerEntityTable = 'company_advanced_customer_entity';

    /**
     * Company Table
     *
     * @var string
     */
    protected $_companyTable = 'company';

    /**
     * Admin User Table
     *
     * @var string
     */
    protected $_adminUserTable = 'admin_user';

    public function getAllFields()
    {
        return [];
    }

    /**
     * Retrieve Company Advanced Customer Entity Table Name
     *
     * @return string
     */
    protected function getCompanyAdvancedCustomerEntityTable()
    {
        return $this->_resource->getTableName(
            $this->_companyAdvancedCustomerEntityTable
        );
    }

    /**
     * Retrieve Company Table
     *
     * @return string
     */
    protected function getCompanyTable()
    {
        return $this->_resource->getTableName(
            $this->_companyTable
        );
    }

    /**
     * Retrieve Admin User Table
     *
     * @return string
     */
    protected function getAdminUserTable()
    {
        return $this->_resource->getTableName(
            $this->_adminUserTable
        );
    }

    protected function getExistEntityId(array $rowData)
    {
        $columnEntityId = static::COLUMN_ENTITY_ID;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), $columnEntityId)
            ->where($columnEntityId.' = ?', $this->getQuoteIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    /**
     * Retrieve Company By Customer Identifier
     *
     * @param int $customerId
     * @return bool|array
     */
    protected function getCompany($customerId)
    {
        $select = $this->_connection->select()
            ->from(['cace' => $this->getCompanyAdvancedCustomerEntityTable()], [])
            ->join(
                ['c' => $this->getCompanyTable()],
                'cace.'.CompanyCustomerInterface::COMPANY_ID.' = c.'.CompanyInterface::COMPANY_ID,
                [
                    CompanyInterface::COMPANY_ID,
                    CompanyInterface::NAME,
                    CompanyInterface::SALES_REPRESENTATIVE_ID
                ]
            )
            ->where(CompanyCustomerInterface::CUSTOMER_ID.' = ?', $customerId);

        return $this->_connection->fetchRow($select);
    }

    /**
     * Retrieve Customer Name By Identifier
     *
     * @param int $id
     * @return bool|string
     */
    protected function getCustomerName($id)
    {
        $fields = [
            CustomerInterface::PREFIX,
            CustomerInterface::FIRSTNAME,
            CustomerInterface::MIDDLENAME,
            CustomerInterface::LASTNAME,
            CustomerInterface::SUFFIX
        ];

        $select = $this->_connection->select()
            ->from($this->getCustomerTable(), $fields)
            ->where('entity_id = ?', $id);

        $customer = $this->_connection->fetchRow($select);
        if ($customer) {
            return implode(' ', array_filter($customer));
        }

        return false;
    }

    /**
     * Retrieve Admin User Name By Identifier
     *
     * @param int $id
     * @return bool|string
     */
    protected function getAdminUserName($id)
    {
        $fields = [
            'firstname',
            'lastname'
        ];

        $select = $this->_connection->select()
            ->from($this->getAdminUserTable(), $fields)
            ->where('user_id = ?', $id);

        $adminUser = $this->_connection->fetchRow($select);
        if ($adminUser) {
            return implode(' ', array_filter($adminUser));
        }

        return false;
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $quoteId = $this->getQuoteIdFromMap();
        $negotiableQuote = null;
        if ($quoteId) {
            $fields = [
                NegotiableQuoteInterface::QUOTE_STATUS,
                NegotiableQuoteInterface::QUOTE_NAME,
                NegotiableQuoteInterface::BASE_ORIGINAL_TOTAL_PRICE,
                NegotiableQuoteInterface::ORIGINAL_TOTAL_PRICE,
                NegotiableQuoteInterface::BASE_NEGOTIATED_TOTAL_PRICE,
                NegotiableQuoteInterface::NEGOTIATED_TOTAL_PRICE
            ];

            $negotiableQuote = $this->getNegotiableQuote($quoteId, $fields);
        }

        if ($negotiableQuote) {
            $newEntity = !$this->getExistEntityId($rowData);

            $fields = [
                CartInterface::KEY_STORE_ID,
                CartInterface::KEY_CREATED_AT,
                CartInterface::KEY_UPDATED_AT,
                TotalsInterface::KEY_BASE_CURRENCY_CODE,
                TotalsInterface::KEY_QUOTE_CURRENCY_CODE,
                'customer_id'
            ];

            $quote = $this->getQuote($quoteId, $fields);
            $customerId = $quote['customer_id'];
            $customerName = $this->getCustomerName($customerId);

            $entityRow = [
                static::COLUMN_ENTITY_ID => $quoteId,
                static::COLUMN_QUOTE_NAME => $negotiableQuote[NegotiableQuoteInterface::QUOTE_NAME],
                static::COLUMN_CREATED_AT => $quote[CartInterface::KEY_CREATED_AT],
                static::COLUMN_COMPANY_ID => null,
                static::COLUMN_COMPANY_NAME => null,
                static::COLUMN_CUSTOMER_ID => $customerId,
                static::COLUMN_SUBMITTED_BY => $customerName,
                static::COLUMN_UPDATED_AT => $quote[CartInterface::KEY_UPDATED_AT],
                static::COLUMN_SALES_REP_ID => null,
                static::COLUMN_SALES_REP => null,
                static::COLUMN_BASE_GRAND_TOTAL => $negotiableQuote[NegotiableQuoteInterface::BASE_ORIGINAL_TOTAL_PRICE],
                static::COLUMN_GRAND_TOTAL => $negotiableQuote[NegotiableQuoteInterface::ORIGINAL_TOTAL_PRICE],
                static::COLUMN_BASE_NEGOTIATED_GRAND_TOTAL => $negotiableQuote[NegotiableQuoteInterface::BASE_NEGOTIATED_TOTAL_PRICE],
                static::COLUMN_NEGOTIATED_GRAND_TOTAL => $negotiableQuote[NegotiableQuoteInterface::NEGOTIATED_TOTAL_PRICE],
                static::COLUMN_STATUS => $negotiableQuote[NegotiableQuoteInterface::QUOTE_STATUS],
                static::COLUMN_BASE_CURRENCY_CODE => $quote[TotalsInterface::KEY_BASE_CURRENCY_CODE],
                static::COLUMN_QUOTE_CURRENCY_CODE => $quote[TotalsInterface::KEY_QUOTE_CURRENCY_CODE],
                static::COLUMN_STORE_ID => $quote[CartInterface::KEY_STORE_ID],
                static::COLUMN_RATE => null
            ];

            $company = $this->getCompany($customerId);
            if ($company) {
                $salesRep = $this->getAdminUserName(
                    $company[CompanyInterface::SALES_REPRESENTATIVE_ID]
                );

                $entityRow[static::COLUMN_COMPANY_ID] = $company[CompanyInterface::COMPANY_ID];
                $entityRow[static::COLUMN_COMPANY_NAME] = $company[CompanyInterface::NAME];
                $entityRow[static::COLUMN_SALES_REP_ID] = $company[CompanyInterface::SALES_REPRESENTATIVE_ID];
                $entityRow[static::COLUMN_SALES_REP] = $salesRep;
            }

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

    protected function _getIdForDelete(array $rowData)
    {
        return $this->getExistEntityId($rowData);
    }
}
