<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Company;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;

class Credit extends AbstractAdapter
{
    protected $tableName = 'company_credit';
    protected $retrieveField = CreditLimitInterface::COMPANY_ID;

    protected function _getHeaderColumns()
    {
        return [
            CreditLimitInterface::BALANCE,
            CreditLimitInterface::CURRENCY_CODE,
            CreditLimitInterface::CREDIT_LIMIT,
            CreditLimitInterface::EXCEED_LIMIT
        ];
    }

    protected function getSelectForField($columnName)
    {
        if ($columnName == CreditLimitInterface::EXCEED_LIMIT) {
            return [
                1 => __('Yes'),
                0 => __('No')
            ];
        }

        return parent::getSelectForField($columnName);
    }
}
