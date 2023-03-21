<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Company;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface;

class QuoteConfig extends AbstractAdapter
{
    protected $tableName = 'negotiable_quote_company_config';
    protected $retrieveField = CompanyQuoteConfigInterface::COMPANY_ID;

    protected function _getHeaderColumns()
    {
        return [
            CompanyQuoteConfigInterface::IS_QUOTE_ENABLED
        ];
    }

    protected function getSelectForField($columnName)
    {
        if ($columnName == CompanyQuoteConfigInterface::IS_QUOTE_ENABLED) {
            return [
                1 => __('Yes'),
                0 => __('No')
            ];
        }

        return parent::getSelectForField($columnName);
    }
}
