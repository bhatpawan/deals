<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote;

use Magento\ImportExport\Model\Import;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote;
use Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid;

/**
 * @method Item getDataQuoteItemResourceModel
 * @method NegotiableQuote getDataNegotiableQuoteResourceModel
 * @method QuoteGrid getDataNegotiableQuoteGridResourceModel
 */
class Entity extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CartInterface::KEY_ENTITY_ID;
    const COLUMN_STORE_ID = CartInterface::KEY_STORE_ID;
    const COLUMN_CUSTOMER_ID = 'customer_id';
    const COLUMN_CUSTOMER_EMAIL = 'customer_email';
    const COLUMN_CUSTOMER_IS_GUEST = CartInterface::KEY_CUSTOMER_IS_GUEST;
    const COLUMN_TRIGGER_RECOLLECT = 'trigger_recollect';

    /**
     * Error Codes
     */
    const ERROR_INCREMENT_ID_IS_EMPTY = 'quoteIncrementIdIsEmpty';
    const ERROR_STORE_ID_IS_EMPTY = 'quoteStoreIdIsEmpty';
    const ERROR_STORE_ID_IS_INVALID = 'quoteStoreIdIsInvalid';
    const ERROR_CUSTOMER_EMAIL_IS_EMPTY = 'quoteCustomerEmailIsEmpty';
    const ERROR_CUSTOMER_EMAIL_IS_INVALID = 'quoteCustomerEmailIsInvalid';
    const ERROR_QUOTE_NOT_FOUND = 'quoteNotFound';

    protected $_messageTemplates = [
        self::ERROR_INCREMENT_ID_IS_EMPTY => 'Quote '.self::COLUMN_INCREMENT_ID.' is empty',
        self::ERROR_STORE_ID_IS_EMPTY => 'Quote %s is empty',
        self::ERROR_STORE_ID_IS_INVALID => 'Quote %s is invalid',
        self::ERROR_CUSTOMER_EMAIL_IS_EMPTY => 'Quote %s is empty',
        self::ERROR_CUSTOMER_EMAIL_IS_INVALID => 'Quote %s is invalid',
        self::ERROR_QUOTE_NOT_FOUND => 'Quote not found'
    ];

    protected $excludeFields = [
        self::COLUMN_ENTITY_ID,
        self::COLUMN_CUSTOMER_ID,
        self::COLUMN_CUSTOMER_IS_GUEST,
        self::COLUMN_TRIGGER_RECOLLECT
    ];

    protected $optGroupName = 'Quote';

    protected $enableReplace = true;

    protected function getExistEntityId(array $rowData)
    {
        $columnIncrementId = static::COLUMN_INCREMENT_ID;

        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where($columnIncrementId.' = ?', $rowData[$columnIncrementId]);

        return $this->_connection->fetchOne($select);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        if ($this->_checkIncrementIdKey($rowData, $rowNumber)) {
            $entityId = $this->getExistEntityId($rowData);

            $columnStoreId = static::COLUMN_STORE_ID;
            if (!empty($rowData[$columnStoreId])) {
                if (!$this->validateForeignKey($columnStoreId, $rowData[$columnStoreId])) {
                    $this->addRowError(static::ERROR_STORE_ID_IS_INVALID, $rowNumber, $columnStoreId);
                }
            } elseif (isset($rowData[$column]) || !$entityId) {
                $this->addRowError(static::ERROR_STORE_ID_IS_EMPTY, $rowNumber, $column);
            }

            $columnCustomerEmail = static::COLUMN_CUSTOMER_EMAIL;
            $columnIsGuest = static::COLUMN_CUSTOMER_IS_GUEST;
            if (!empty($rowData[$columnCustomerEmail])) {
                if (isset($rowData[$columnIsGuest]) && $rowData[$columnIsGuest] == '0') {
                    if (!$this->getCustomerId($rowData[$columnCustomerEmail], $rowData[$columnStoreId])) {
                        $this->addRowError(static::ERROR_CUSTOMER_EMAIL_IS_INVALID, $rowNumber, $columnCustomerEmail);
                    }
                }
            } elseif (empty($rowData[$columnIsGuest])) {
                $this->addRowError(static::ERROR_CUSTOMER_EMAIL_IS_EMPTY, $rowNumber, $columnCustomerEmail);
            }
        }
    }

    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->_checkIncrementIdKey($rowData, $rowNumber)) {
            $entityId = $this->getExistEntityId($rowData);

            if ($entityId) {
                $quoteIdsMap = $this->storage->getQuoteIdsMap();
                $quoteIdsMap[$this->incrementQuoteId] = $entityId;
                $this->storage->setQuoteIdsMap($quoteIdsMap);
            } else {
                $this->addRowError(static::ERROR_QUOTE_NOT_FOUND, $rowNumber);
            }
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $entityId = $this->getExistEntityId($rowData);
        $newEntity = false;
        if (!$entityId) {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
        }

        $quoteIdsMap = $this->storage->getQuoteIdsMap();
        $quoteIdsMap[$this->incrementQuoteId] = $entityId;
        $this->storage->setQuoteIdsMap($quoteIdsMap);

        $entityRow = [
            static::COLUMN_ENTITY_ID => $entityId,
            static::COLUMN_CUSTOMER_ID => null,
            static::COLUMN_CUSTOMER_IS_GUEST => 1,
            static::COLUMN_TRIGGER_RECOLLECT => 1
        ];

        if (!empty($rowData[static::COLUMN_CUSTOMER_EMAIL])) {
            $customerId = $this->getCustomerId(
                $rowData[static::COLUMN_CUSTOMER_EMAIL],
                $rowData[static::COLUMN_STORE_ID]
            );
            if ($customerId) {
                $entityRow[static::COLUMN_CUSTOMER_ID] = $customerId;
                $entityRow[static::COLUMN_CUSTOMER_IS_GUEST] = 0;
            }
        }

        /* prepare data */
        $entityRow = $this->_prepareEntityRow($entityRow, $rowData);
        if ($newEntity) {
            $toCreate[] = $entityRow;
        } else {
            $toUpdate[] = $entityRow;
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    protected function _getIdForDelete(array $rowData)
    {
        return $this->getExistEntityId($rowData);
    }

    protected function _saveEntities(array $toCreate, array $toUpdate)
    {
        if ($this->getBehavior() == Import::BEHAVIOR_REPLACE) {
            $quoteIds = array_column($toUpdate, static::COLUMN_ENTITY_ID);
            if (isset($this->quoteIdsDeleted) && !empty($this->quoteIdsDeleted)) {
                $quoteIds = $this->quoteIdsDeleted;
            }
            $this->_connection->delete(
                $this->getTableFromModel($this->getDataQuoteItemResourceModel()),
                [CartItemInterface::KEY_QUOTE_ID.' IN (?)' => $quoteIds]
            );
            $this->_connection->delete(
                $this->getTableFromModel($this->getDataNegotiableQuoteResourceModel()),
                [NegotiableQuoteInterface::QUOTE_ID.' IN (?)' => $quoteIds]
            );
            $this->_connection->delete(
                $this->getTableFromModel($this->getDataNegotiableQuoteGridResourceModel()),
                [QuoteGrid::QUOTE_ID.' IN (?)' => $quoteIds]
            );
        }

        return parent::_saveEntities($toCreate, $toUpdate);
    }
}
