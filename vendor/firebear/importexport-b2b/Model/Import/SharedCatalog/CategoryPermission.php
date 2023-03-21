<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\SharedCatalog;

use Magento\SharedCatalog\Api\Data\PermissionInterface;
use Magento\SharedCatalog\Api\Data\ProductItemInterface;

class CategoryPermission extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = PermissionInterface::SHARED_CATALOG_PERMISSION_ID;
    const COLUMN_CATEGORY_ID = PermissionInterface::SHARED_CATALOG_PERMISSION_CATEGORY_ID;
    const COLUMN_CUSTOMER_GROUP_ID = PermissionInterface::SHARED_CATALOG_PERMISSION_CUSTOMER_GROUP_ID;
    const COLUMN_PERMISSION = PermissionInterface::SHARED_CATALOG_PERMISSION_PERMISSION;

    /**
     * Available Permissions
     */
    const PERMISSION_ALLOW = -1;
    const PERMISSION_DENY = -2;

    /**
     * Shared Catalog Product Item Table
     *
     * @var string
     */
    protected $_sharedCatalogProductItem = 'shared_catalog_product_item';

    /**
     * Category Product Table
     *
     * @var string
     */
    protected $_categoryProductTable = 'catalog_category_product';

    public function getAllFields()
    {
        return [];
    }

    public function prepareRowData(array $rowData)
    {
        return $this->prepareMultipleField($rowData, static::COLUMN_CATEGORY);
    }

    /**
     * Retrieve Shared Catalog Product Item Table Name
     *
     * @return string
     */
    protected function getSharedCatalogProductItemTable()
    {
        return $this->_resource->getTableName(
            $this->_sharedCatalogProductItem
        );
    }

    /**
     * Retrieve Category Product Table Name
     *
     * @return string
     */
    protected function getCategoryProductTable()
    {
        return $this->_resource->getTableName(
            $this->_categoryProductTable
        );
    }

    /**
     * Get Identifiers By Category Identifiers
     *
     * @param array $categoryIds
     * @param $customerGroupId
     * @return array
     */
    protected function getIdsByCategoryIds(array $categoryIds, $customerGroupId)
    {
        $column = static::COLUMN_CATEGORY_ID;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), [static::COLUMN_ENTITY_ID, $column])
            ->where($column.' IN (?)', $categoryIds)
            ->where(static::COLUMN_CUSTOMER_GROUP_ID.' = ?', $customerGroupId);

        return $this->_connection->fetchPairs($select);
    }

    /**
     * Retrieve Category Identifiers
     *
     * @param $customerGroupId
     * @return array
     */
    protected function getCategoryIds($customerGroupId)
    {
        $column = ProductItemInterface::CUSTOMER_GROUP_ID;
        $bind = [':'.$column => $customerGroupId];

        $select = $this->_connection->select()
            ->from(['scpi' => $this->getSharedCatalogProductItemTable()], [])
            ->joinRight(
                ['p' => $this->getProductTable()],
                'scpi.'.ProductItemInterface::SKU.' = p.sku',
                []
            )
            ->joinRight(
                ['cp' => $this->getCategoryProductTable()],
                'p.entity_id = cp.product_id',
                ['category_id']
            )
            ->where('scpi.'.$column.' = :'.$column);

        return $this->_connection->fetchCol($select, $bind);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $customerGroupId = $this->getCustomerGroupIdFromMap($rowData);
        $idsByCustomerGroupId = $this->getIdsByCustomerGroupId($customerGroupId);
        $rowData[static::COLUMN_CATEGORY] = !empty($rowData[static::COLUMN_CATEGORY])
            ? $rowData[static::COLUMN_CATEGORY] : [];
        $categoryIds = array_unique(
            array_merge($this->getCategoryIds($customerGroupId), $rowData[static::COLUMN_CATEGORY])
        );
        $idsByCategoryIds = !empty($categoryIds) ? $this->getIdsByCategoryIds($categoryIds, $customerGroupId) : [];

        foreach ($categoryIds as $categoryId) {
            $newEntity = false;
            $entityId = array_search($categoryId, $idsByCategoryIds);
            if (!$entityId) {
                /* create new entity id */
                $newEntity = true;
                $entityId = $this->_getNextEntityId();
            }

            $entityRow = [
                static::COLUMN_ENTITY_ID => $entityId,
                static::COLUMN_CATEGORY_ID => $categoryId,
                static::COLUMN_CUSTOMER_GROUP_ID => $customerGroupId,
                static::COLUMN_PERMISSION => static::PERMISSION_ALLOW
            ];

            if ($newEntity) {
                $toCreate[] = $entityRow;
            } else {
                $toUpdate[] = $entityRow;
            }
        }

        $toDelete = array_diff($idsByCustomerGroupId, array_keys($idsByCategoryIds));

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate,
            static::ENTITIES_TO_DELETE_KEY => $toDelete
        ];
    }

    protected function _getIdForDelete(array $rowData)
    {
        $customerGroupId = $this->getCustomerGroupIdFromMap($rowData);
        return $this->getIdsByCustomerGroupId($customerGroupId);
    }
}
