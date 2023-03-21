<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\SharedCatalog;

use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\TierPriceStorageInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\SharedCatalog\Api\Data\ProductItemInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\PriceProcessor;
use Firebear\ImportExport\Model\Import\Context;

class ProductItem extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = ProductItemInterface::SHARED_CATALOG_PRODUCT_ITEM_ID;
    const COLUMN_CUSTOMER_GROUP_ID = ProductItemInterface::CUSTOMER_GROUP_ID;
    const COLUMN_SKU = ProductItemInterface::SKU;

    /**
     * Error Codes
     */
    const ERROR_CATEGORY_NOT_FOUND = 'categoryNotFound';
    const ERROR_PRODUCT_NOT_FOUND = 'productNotFound';

    protected $_messageTemplates = [
        self::ERROR_CATEGORY_NOT_FOUND => 'Category with entity_id %s not found',
        self::ERROR_PRODUCT_NOT_FOUND => 'Product with sku %s not found'
    ];

    /**
     * Category Table
     *
     * @var string
     */
    protected $_categoryTable = 'catalog_category_entity';

    /**
     * Category Product Table
     *
     * @var string
     */
    protected $_categoryProductTable = 'catalog_category_product';

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var PriceProcessor
     */
    private $priceProcessor;

    /**
     * @var TierPriceStorageInterface
     */
    private $tierPriceStorage;

    /**
     * @param Context $context
     * @param AbstractResource $resourceModel
     * @param Storage $storage
     * @param GroupRepositoryInterface $groupRepository
     * @param PriceProcessor $priceProcessor
     * @param TierPriceStorageInterface $tierPriceStorage
     * @param array $data
     */
    public function __construct(
        Context $context,
        AbstractResource $resourceModel,
        Storage $storage,
        GroupRepositoryInterface $groupRepository,
        PriceProcessor $priceProcessor,
        TierPriceStorageInterface $tierPriceStorage,
        array $data = []
    ) {
        $this->groupRepository = $groupRepository;
        $this->priceProcessor = $priceProcessor;
        $this->tierPriceStorage = $tierPriceStorage;

        parent::__construct(
            $context,
            $resourceModel,
            $storage,
            $data
        );
    }

    public function getAllFields()
    {
        return [
            static::COLUMN_CATEGORY,
            static::COLUMN_SKU
        ];
    }

    public function prepareRowData(array $rowData)
    {
        $rowData = $this->prepareMultipleField($rowData, static::COLUMN_CATEGORY);
        $rowData = $this->prepareMultipleField($rowData, static::COLUMN_SKU);

        return $rowData;
    }

    /**
     * Retrieve Category Table Name
     *
     * @return string
     */
    protected function getCategoryTable()
    {
        return $this->_resource->getTableName(
            $this->_categoryTable
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
     * Retrieve Identifiers By Sku
     *
     * @param array $sku
     * @param $customerGroupId
     * @return array
     */
    protected function getIdsBySku(array $sku, $customerGroupId)
    {
        $column = static::COLUMN_SKU;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), [static::COLUMN_ENTITY_ID, $column])
            ->where($column.' IN (?)', $sku)
            ->where(static::COLUMN_CUSTOMER_GROUP_ID.' = ?', $customerGroupId);

        return $this->_connection->fetchPairs($select);
    }

    /**
     * Retrieve Category Identifiers
     *
     * @param array $ids
     * @return array
     */
    protected function getCategoryIds(array $ids)
    {
        $column = 'entity_id';

        $select = $this->_connection->select()
            ->from($this->getCategoryTable(), $column)
            ->where($column.' IN (?)', $ids);

        return $this->_connection->fetchCol($select);
    }

    /**
     * Retrieve Product Sku
     *
     * @param array $sku
     * @return array
     */
    protected function getProductSku(array $sku)
    {
        $column = ProductInterface::SKU;

        $select = $this->_connection->select()
            ->from($this->getProductTable(), $column)
            ->where($column.' IN (?)', $sku);

        return $this->_connection->fetchCol($select);
    }

    /**
     * Retrieve Product Sku By Category Identifiers
     *
     * @param $categoryIds
     * @return array
     */
    protected function getProductSkuByCategoryIds($categoryIds)
    {
        $select = $this->_connection->select()
            ->from(['cp' => $this->getCategoryProductTable()], [])
            ->joinRight(
                ['p' => $this->getProductTable()],
                'cp.product_id = p.entity_id',
                [ProductInterface::SKU]
            )
            ->where('cp.category_id IN (?)', $categoryIds);

        return $this->_connection->fetchCol($select);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        $columnCategory = static::COLUMN_CATEGORY;
        if (isset($rowData[$columnCategory]) && !empty($rowData[$columnCategory])) {
            $categoryIds = $this->getCategoryIds($rowData[$columnCategory]);

            foreach ($rowData[$columnCategory] as $id) {
                if (!in_array($id, $categoryIds)) {
                    $this->addRowError(static::ERROR_CATEGORY_NOT_FOUND, $rowNumber, $id);
                }
            }
        }

        $columnSku = static::COLUMN_SKU;
        if (isset($rowData[$columnSku]) && !empty($rowData[$columnSku])) {
            $productSku = $this->getProductSku($rowData[$columnSku]);

            foreach ($rowData[$columnSku] as $sku) {
                if (!in_array($sku, $productSku)) {
                    $this->addRowError(static::ERROR_PRODUCT_NOT_FOUND, $rowNumber, $sku);
                }
            }
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];
        $toDelete = [];

        $customerGroupId = $this->getCustomerGroupIdFromMap($rowData);
        $columnEntityId = static::COLUMN_ENTITY_ID;
        $columnCustomerGroupId = static::COLUMN_CUSTOMER_GROUP_ID;
        $columnSku = static::COLUMN_SKU;
        $columnCategory = static::COLUMN_CATEGORY;

        if (isset($rowData[$columnCategory]) && !empty($rowData[$columnCategory])) {
            $rowData[$columnSku] = array_merge(
                isset($rowData[$columnSku]) ? $rowData[$columnSku] : [],
                $this->getProductSkuByCategoryIds($rowData[$columnCategory])
            );
            $rowData[$columnSku] = array_unique($rowData[$columnSku]);
        }

        if (isset($rowData[$columnSku])) {
            $tierPrices = [];
            $idsByCustomerGroupId = $this->getIdsByCustomerGroupId($customerGroupId);
            $idsBySku = !empty($rowData[$columnSku]) ? $this->getIdsBySku($rowData[$columnSku], $customerGroupId) : [];

            $priceField = null;
            if (!empty($rowData['price_type']) && !empty($rowData['custom_price'])) {
                $priceField = $rowData['price_type'] == TierPriceInterface::PRICE_TYPE_FIXED
                    ? ProductAttributeInterface::CODE_PRICE
                    : ProductAttributeInterface::CODE_TIER_PRICE_FIELD_PERCENTAGE_VALUE;
            }

            foreach ($rowData[$columnSku] as $sku) {
                $newEntity = false;
                $entityId = array_search($sku, $idsBySku);
                if (!$entityId) {
                    /* create new entity id */
                    $newEntity = true;
                    $entityId = $this->_getNextEntityId();
                }

                $entityRow = [
                    $columnEntityId => $entityId,
                    $columnCustomerGroupId => $customerGroupId,
                    $columnSku => $sku
                ];

                if ($priceField) {
                    $tierPrices[$sku] = [
                        'qty' => 1,
                        $priceField => $rowData['custom_price'],
                        'value_type' => $rowData['price_type'],
                        'website_id' => 0,
                        'is_changed' => true
                    ];
                }

                if ($newEntity) {
                    $toCreate[] = $entityRow;
                } else {
                    $toUpdate[] = $entityRow;
                }
            }

            $toDelete = array_diff($idsByCustomerGroupId, array_keys($idsBySku));
            /** save tier prices */
            if (0 < count($tierPrices)) {
                $customerGroupIds = [$customerGroupId];
                if ($rowData['type'] == SharedCatalogInterface::TYPE_PUBLIC) {
                    $customerGroupIds[] = GroupInterface::NOT_LOGGED_IN_ID;
                }

                foreach ($customerGroupIds as $groupId) {
                    $group = $this->groupRepository->getById($groupId);
                    foreach ($tierPrices as $sku => $tierPrice) {
                        $tierPriceToSave = [
                            'shared_catalog_id' => $entityId,
                            'customer_group' => $group->getCode(),
                            'product_sku' => $sku,
                            'prices' => [$tierPrice]
                        ];
                        $tierPriceToSave = $this->priceProcessor->createPricesUpdate($tierPriceToSave);
                        $result = $this->tierPriceStorage->update($tierPriceToSave);
                    }
                }
            }
        }

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
