<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\SharedCatalog;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\SharedCatalog\Api\Data\PermissionInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

class CategoryPermission extends AbstractAdapter
{
    /**
     * Column names
     */
    const COLUMN_CATEGORY = 'category';

    /**
     * Available permissions
     */
    const PERMISSION_ALLOW = -1;
    const PERMISSION_DENY = -2;

    protected $tableName = 'sharedcatalog_category_permissions';
    protected $retrieveField = PermissionInterface::SHARED_CATALOG_PERMISSION_CUSTOMER_GROUP_ID;
    protected $relationField = SharedCatalogInterface::CUSTOMER_GROUP_ID;

    public function getHeaderColumns()
    {
        return [
            static::COLUMN_CATEGORY => PermissionInterface::SHARED_CATALOG_PERMISSION_CATEGORY_ID
        ];
    }

    public function exportItem($item)
    {
        $relationField = $this->relationField ?: $item->getIdFieldName();

        $select = $this->connection->select()
            ->from($this->getTableName(), $this->getHeaderColumns())
            ->where($this->retrieveField.' = ?', $item->getData($relationField))
            ->where(
                PermissionInterface::SHARED_CATALOG_PERMISSION_PERMISSION.' = ?',
                static::PERMISSION_ALLOW
            );

        $result = [
            static::COLUMN_CATEGORY => []
        ];
        foreach ($this->connection->fetchAssoc($select) as $row) {
            $result[static::COLUMN_CATEGORY][] = $row[static::COLUMN_CATEGORY];
        }

        return [$result];
    }
}
