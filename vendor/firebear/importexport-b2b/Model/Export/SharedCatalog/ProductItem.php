<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\SharedCatalog;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\SharedCatalog\Api\Data\ProductItemInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

class ProductItem extends AbstractAdapter
{
    protected $tableName = 'shared_catalog_product_item';
    protected $retrieveField = ProductItemInterface::CUSTOMER_GROUP_ID;
    protected $relationField = SharedCatalogInterface::CUSTOMER_GROUP_ID;

    protected function _getHeaderColumns()
    {
        return [
            ProductItemInterface::SKU
        ];
    }

    public function exportItem($item)
    {
        $result = [
            ProductItemInterface::SKU => []
        ];
        foreach (parent::exportItem($item) as $row) {
            $result[ProductItemInterface::SKU][] = $row[ProductItemInterface::SKU];
        }

        return [$result];
    }
}
