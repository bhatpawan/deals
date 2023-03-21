<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote;

use Magento\Quote\Api\Data\CartItemInterface;

class ItemOption extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = 'option_id';
    const COLUMN_ITEM_ID = 'item_id';
    const COLUMN_PRODUCT_ID = 'product_id';
    const COLUMN_CODE = 'code';

    /**
     * Error Codes
     */
    const ERROR_CODE_IS_EMPTY = 'itemOptionCodeIsEmpty';

    protected $_messageTemplates = [
        self::ERROR_CODE_IS_EMPTY => 'Item option %s is empty'
    ];

    protected $columnPrefix = 'item_option';

    protected $excludeFields = [
        self::COLUMN_ENTITY_ID,
        self::COLUMN_ITEM_ID,
        self::COLUMN_PRODUCT_ID
    ];

    protected $optGroupName = 'Quote Item Option';

    /**
     * Quote Item Table
     *
     * @var string
     */
    protected $_quoteItemTable = 'quote_item';

    /**
     * Retrieve Quote Item Table Name
     *
     * @return string
     */
    protected function getQuoteItemTable()
    {
        return $this->_resource->getTableName(
            $this->_quoteItemTable
        );
    }

    protected function getExistEntityId(array $rowData)
    {
        $columnItemId = static::COLUMN_ITEM_ID;
        $columnCode = static::COLUMN_CODE;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where($columnItemId.' = ?', $this->getItemIdFromMap())
            ->where($columnCode.' = ?', $rowData[$columnCode]);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Retrieve Quote Item By Identifier
     *
     * @param $id
     * @return bool|array
     */
    protected function getQuoteItem($id)
    {
        $fields = [
            'product_id'
        ];

        $select = $this->_connection->select()
            ->from($this->getQuoteItemTable(), $fields)
            ->where(CartItemInterface::KEY_ITEM_ID.' = ?', $id);

        return $this->_connection->fetchRow($select);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        $columnCode = static::COLUMN_CODE;
        if (empty($rowData[$columnCode])) {
            $this->addRowError(static::ERROR_CODE_IS_EMPTY, $rowNumber, $columnCode);
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $itemId = $this->getItemIdFromMap();
        if ($itemId) {
            $newEntity = false;
            $entityId = $this->getExistEntityId($rowData);
            if (!$entityId) {
                /* create new entity id */
                $newEntity = true;
                $entityId = $this->_getNextEntityId();
            }

            $quoteItem = $this->getQuoteItem($itemId);

            $entityRow = [
                static::COLUMN_ENTITY_ID => $entityId,
                static::COLUMN_ITEM_ID => $itemId,
                static::COLUMN_PRODUCT_ID => $quoteItem['product_id']
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
