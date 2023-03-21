<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote\Negotiable;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\TotalsItemInterface;

class Item extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = NegotiableQuoteItemInterface::ITEM_ID;
    const COLUMN_ORIGINAL_PRICE = NegotiableQuoteItemInterface::ORIGINAL_PRICE;
    const COLUMN_ORIGINAL_TAX_AMOUNT = NegotiableQuoteItemInterface::ORIGINAL_TAX_AMOUNT;
    const COLUMN_ORIGINAL_DISCOUNT_AMOUNT = NegotiableQuoteItemInterface::ORIGINAL_DISCOUNT_AMOUNT;

    /**
     * Quote Item Table
     *
     * @var string
     */
    protected $_quoteItemTable = 'quote_item';

    public function getAllFields()
    {
        return [];
    }

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
        $columnEntityId = static::COLUMN_ENTITY_ID;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), $columnEntityId)
            ->where($columnEntityId.' = ?', $rowData[$columnEntityId]);

        return $this->_connection->fetchOne($select);
    }

    /**
     * Retrieve Quote Items By Quote Identifier
     *
     * @param $quoteId
     * @return bool|array
     */
    protected function getQuoteItems($quoteId)
    {
        $fields = [
            TotalsItemInterface::KEY_ITEM_ID,
            TotalsItemInterface::KEY_QTY,
            TotalsItemInterface::KEY_BASE_PRICE,
            TotalsItemInterface::KEY_BASE_TAX_AMOUNT,
            TotalsItemInterface::KEY_BASE_DISCOUNT_AMOUNT
        ];

        $select = $this->_connection->select()
            ->from($this->getQuoteItemTable(), $fields)
            ->where(CartItemInterface::KEY_QUOTE_ID.' = ?', $quoteId);

        return $this->_connection->fetchAssoc($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $quoteId = $this->getQuoteIdFromMap();
        if ($quoteId && $this->getNegotiableQuote($quoteId)) {
            foreach ($this->getQuoteItems($quoteId) as $item) {
                $rowData[static::COLUMN_ENTITY_ID] = $item[TotalsItemInterface::KEY_ITEM_ID];
                $newEntity = !$this->getExistEntityId($rowData);

                $qty = $item[TotalsItemInterface::KEY_QTY];
                $originalTaxAmount = $item[TotalsItemInterface::KEY_BASE_TAX_AMOUNT] / $qty;
                $originalDiscountAmount = $item[TotalsItemInterface::KEY_BASE_DISCOUNT_AMOUNT] / $qty;

                $entityRow = [
                    static::COLUMN_ENTITY_ID => $item[TotalsItemInterface::KEY_ITEM_ID],
                    static::COLUMN_ORIGINAL_PRICE => $item[TotalsItemInterface::KEY_BASE_PRICE],
                    static::COLUMN_ORIGINAL_TAX_AMOUNT => $originalTaxAmount,
                    static::COLUMN_ORIGINAL_DISCOUNT_AMOUNT => $originalDiscountAmount
                ];

                if ($newEntity) {
                    $toCreate[] = $entityRow;
                } else {
                    $toUpdate[] = $entityRow;
                }
            };
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }
}
