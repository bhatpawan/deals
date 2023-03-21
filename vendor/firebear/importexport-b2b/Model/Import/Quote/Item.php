<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote;

use Magento\Quote\Api\Data\CartItemInterface;

class Item extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CartItemInterface::KEY_ITEM_ID;
    const COLUMN_QUOTE_ID = CartItemInterface::KEY_QUOTE_ID;
    const COLUMN_PRODUCT_ID = 'product_id';
    const COLUMN_SKU = CartItemInterface::KEY_SKU;
    const COLUMN_QTY = 'qty';

    /**
     * Error Codes
     */
    const ERROR_SKU_IS_EMPTY = 'itemSkuIsEmpty';
    const ERROR_SKU_IS_INVALID = 'itemSkuIsInvalid';
    const ERROR_QTY_IS_INVALID = 'itemQtyIsInvalid';

    protected $_messageTemplates = [
        self::ERROR_SKU_IS_EMPTY => 'Item %s is empty',
        self::ERROR_SKU_IS_INVALID => 'Item %s is invalid',
        self::ERROR_QTY_IS_INVALID => 'Item %s is invalid'
    ];

    protected $columnPrefix = 'item';

    protected $excludeFields = [
        self::COLUMN_ENTITY_ID,
        self::COLUMN_QUOTE_ID,
        self::COLUMN_PRODUCT_ID
    ];

    protected $optGroupName = 'Quote Item';

    protected function getExistEntityId(array $rowData)
    {
        $columnQuoteId = static::COLUMN_QUOTE_ID;
        $columnSku = static::COLUMN_SKU;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where($columnQuoteId.' = ?', $this->getQuoteIdFromMap())
            ->where($columnSku.' = ?', $rowData[$columnSku]);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Validate Quantity
     *
     * @param $qty
     * @return bool
     */
    protected function validateQty($qty)
    {
        return is_numeric($qty) && ($qty > 0);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        $columnSku = static::COLUMN_SKU;
        if (empty($rowData[$columnSku])) {
            $this->addRowError(static::ERROR_SKU_IS_EMPTY, $rowNumber, $columnSku);
        } elseif (!$this->getProductIdBySku($rowData[$columnSku])) {
            $this->addRowError(static::ERROR_SKU_IS_INVALID, $rowNumber, $columnSku);
        }

        $columnQty = static::COLUMN_QTY;
        if (!empty($rowData[$columnQty]) && !$this->validateQty($rowData[$columnQty])) {
            $this->addRowError(static::ERROR_QTY_IS_INVALID, $rowNumber, $columnQty);
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $quoteId = $this->getQuoteIdFromMap();
        if ($quoteId) {
            $entityId = $this->getExistEntityId($rowData);
            $newEntity = false;
            if (!$entityId) {
                /* create new entity id */
                $newEntity = true;
                $entityId = $this->_getNextEntityId();
            }

            $itemIdsMap = $this->storage->getItemIdsMap();
            $itemIdsMap[$this->incrementItemId] = $entityId;
            $this->storage->setItemIdsMap($itemIdsMap);

            $productId = $this->getProductIdBySku($rowData[static::COLUMN_SKU]);

            $productQty = 1;
            if (!empty($rowData[static::COLUMN_QTY])) {
                $productQty = $rowData[static::COLUMN_QTY];
            }

            $entityRow = [
                static::COLUMN_ENTITY_ID => $entityId,
                static::COLUMN_QUOTE_ID => $quoteId,
                static::COLUMN_PRODUCT_ID => $productId,
                static::COLUMN_QTY => $productQty
            ];

            /* prepare data */
            $entityRow = $this->_prepareEntityRow($entityRow, $rowData);
            if ($newEntity) {
                $toCreate[] = $entityRow;
            } else {
                $toUpdate[] = $entityRow;
            }
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }
}
