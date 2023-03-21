<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\SharedCatalog;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

class Company extends AbstractAdapter
{
    /**
     * Column names
     */
    const COLUMN_COMPANY = 'company';

    protected $tableName = 'company';
    protected $retrieveField = CompanyInterface::CUSTOMER_GROUP_ID;
    protected $relationField = SharedCatalogInterface::CUSTOMER_GROUP_ID;

    public function getHeaderColumns()
    {
        return [
            static::COLUMN_COMPANY => CompanyInterface::COMPANY_EMAIL
        ];
    }

    public function exportItem($item)
    {
        $result = [
            static::COLUMN_COMPANY => []
        ];
        foreach (parent::exportItem($item) as $row) {
            $result[static::COLUMN_COMPANY][] = $row[static::COLUMN_COMPANY];
        }

        return [$result];
    }
}
