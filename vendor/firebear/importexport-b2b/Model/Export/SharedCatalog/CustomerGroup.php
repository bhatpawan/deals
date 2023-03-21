<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\SharedCatalog;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

class CustomerGroup extends AbstractAdapter
{
    protected $tableName = 'customer_group';
    protected $retrieveField = 'customer_group_id';
    protected $relationField = SharedCatalogInterface::CUSTOMER_GROUP_ID;

    protected function _getHeaderColumns()
    {
        return [
            GroupInterface::TAX_CLASS_ID
        ];
    }
}
