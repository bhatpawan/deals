<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Company;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;

class Payment extends AbstractAdapter
{
    /**
     * Column names
     */
    const COLUMN_USE_CONFIG_SETTINGS = 'use_config_settings';

    protected $tableName = 'company_payment';
    protected $retrieveField = 'company_id';

    protected function _getHeaderColumns()
    {
        return [
            'applicable_payment_method',
            'available_payment_methods',
            static::COLUMN_USE_CONFIG_SETTINGS
        ];
    }

    protected function getSelectForField($columnName)
    {
        if ($columnName == static::COLUMN_USE_CONFIG_SETTINGS) {
            return [
                1 => __('Yes'),
                0 => __('No')
            ];
        }

        return parent::getSelectForField($columnName);
    }
}
