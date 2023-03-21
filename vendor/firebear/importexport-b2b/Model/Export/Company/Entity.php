<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Company;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\Company\Api\Data\CompanyInterface;

class Entity extends AbstractAdapter
{
    protected $tableName = 'company';
    protected $retrieveField = CompanyInterface::COMPANY_ID;

    protected function _getHeaderColumns()
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

    protected function getSelectForField($columnName)
    {
        if ($columnName == CompanyInterface::STATUS) {
            return [
                CompanyInterface::STATUS_PENDING => __('Pending Approval'),
                CompanyInterface::STATUS_APPROVED => __('Active'),
                CompanyInterface::STATUS_REJECTED => __('Rejected'),
                CompanyInterface::STATUS_BLOCKED => __('Blocked')
            ];
        }

        return parent::getSelectForField($columnName);
    }

    /**
     * Describe the table
     *
     * @return array
     */
    protected function describeTable()
    {
        return $this->resource->getConnection()->describeTable(
            $this->getTableName()
        );
    }
}
