<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote\Negotiable;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\QuoteFactory as QuoteModelFactory;
use Magento\Quote\Model\ResourceModel\QuoteFactory as QuoteResourceModelFactory;

/**
 * @method QuoteModelFactory getDataQuoteModelFactory
 * @method QuoteResourceModelFactory getDataQuoteResourceModelFactory
 */
class Entity extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = NegotiableQuoteInterface::QUOTE_ID;
    const COLUMN_IS_REGULAR_QUOTE = NegotiableQuoteInterface::IS_REGULAR_QUOTE;
    const COLUMN_STATUS = NegotiableQuoteInterface::QUOTE_STATUS;
    const COLUMN_QUOTE_NAME = NegotiableQuoteInterface::QUOTE_NAME;
    const COLUMN_NEGOTIATED_PRICE_TYPE = NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE;
    const COLUMN_NEGOTIATED_PRICE_VALUE = NegotiableQuoteInterface::NEGOTIATED_PRICE_VALUE;
    const COLUMN_CREATOR_ID = NegotiableQuoteInterface::CREATOR_ID;
    const COLUMN_ORIGINAL_TOTAL_PRICE = NegotiableQuoteInterface::ORIGINAL_TOTAL_PRICE;
    const COLUMN_BASE_ORIGINAL_TOTAL_PRICE = NegotiableQuoteInterface::BASE_ORIGINAL_TOTAL_PRICE;
    const COLUMN_NEGOTIATED_TOTAL_PRICE = NegotiableQuoteInterface::NEGOTIATED_TOTAL_PRICE;
    const COLUMN_BASE_NEGOTIATED_TOTAL_PRICE = NegotiableQuoteInterface::BASE_NEGOTIATED_TOTAL_PRICE;

    /**
     * Error Codes
     */
    const ERROR_STATUS_IS_INVALID = 'negotiableQuoteStatusIsInvalid';
    const ERROR_QUOTE_NAME_IS_EMPTY = 'negotiableQuoteNameIsEmpty';
    const ERROR_NEGOTIATED_PRICE_VALUE_IS_INVALID = 'negotiableQuoteNegotiatedPriceValueIsInvalid';

    protected $_messageTemplates = [
        self::ERROR_STATUS_IS_INVALID => 'Negotiable quote %s is invalid',
        self::ERROR_QUOTE_NAME_IS_EMPTY => 'Negotiable quote %s is empty',
        self::ERROR_NEGOTIATED_PRICE_VALUE_IS_INVALID => 'Negotiable quote %s is invalid'
    ];

    /**
     * Available Statuses
     *
     * @var array
     */
    protected $statuses = [
        NegotiableQuoteInterface::STATUS_CREATED,
        NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
        NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN,
        NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER,
        NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN,
        NegotiableQuoteInterface::STATUS_ORDERED,
        NegotiableQuoteInterface::STATUS_EXPIRED,
        NegotiableQuoteInterface::STATUS_DECLINED,
        NegotiableQuoteInterface::STATUS_CLOSED
    ];

    protected $columnPrefix = 'negotiable';

    protected $excludeFields = [
        self::COLUMN_ENTITY_ID,
        self::COLUMN_CREATOR_ID,
        self::COLUMN_ORIGINAL_TOTAL_PRICE,
        self::COLUMN_BASE_ORIGINAL_TOTAL_PRICE,
        self::COLUMN_NEGOTIATED_TOTAL_PRICE,
        self::COLUMN_BASE_NEGOTIATED_TOTAL_PRICE
    ];

    protected $optGroupName = 'Negotiable Quote';

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_ENTITY_ID.' = ?', $this->getQuoteIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    /**
     * Validate Status
     *
     * @param $status
     * @return bool
     */
    protected function validateStatus($status)
    {
        return in_array($status, $this->statuses);
    }

    /**
     * Validate Negotiated Price Value
     *
     * @param $value
     * @return bool
     */
    protected function validateNegotiatedPriceValue($value)
    {
        return is_numeric($value) && ($value > 0);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        $columnQuoteName = static::COLUMN_QUOTE_NAME;
        if (empty($rowData[$columnQuoteName])) {
            $this->addRowError(static::ERROR_QUOTE_NAME_IS_EMPTY, $rowNumber, $columnQuoteName);
        }

        $columnStatus = static::COLUMN_STATUS;
        if (!empty($rowData[$columnStatus]) && !$this->validateStatus($rowData[$columnStatus])) {
            $this->addRowError(static::ERROR_STATUS_IS_INVALID, $rowNumber, $columnStatus);
        }

        $columnNegotiatedPriceValue = static::COLUMN_NEGOTIATED_PRICE_VALUE;
        if (!empty($rowData[$columnNegotiatedPriceValue]) &&
            !$this->validateNegotiatedPriceValue($rowData[$columnNegotiatedPriceValue])
        ) {
            $this->addRowError(
                static::ERROR_NEGOTIATED_PRICE_VALUE_IS_INVALID,
                $rowNumber,
                $columnNegotiatedPriceValue
            );
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $quoteId = $this->getQuoteIdFromMap();
        if ($quoteId) {
            $newEntity = !$this->getExistEntityId($rowData);

            $isRegularQuote = 1;
            if (isset($rowData[static::COLUMN_IS_REGULAR_QUOTE])) {
                $isRegularQuote = intval($rowData[static::COLUMN_IS_REGULAR_QUOTE]);
            }

            $status = NegotiableQuoteInterface::STATUS_CREATED;
            if (isset($rowData[static::COLUMN_STATUS])) {
                $status = $rowData[static::COLUMN_STATUS];
            }

            // Quote will calculate automatically during loading,
            // if it has flag trigger_recollect
            $this->getDataQuoteResourceModelFactory()->create()->load(
                $this->getDataQuoteModelFactory()->create(),
                $quoteId
            );

            $fields = [
                'customer_id',
                TotalsInterface::KEY_GRAND_TOTAL,
                TotalsInterface::KEY_BASE_GRAND_TOTAL
            ];
            $quote = $this->getQuote($quoteId, $fields);

            $negotiatedTotalPrice = $quote[TotalsInterface::KEY_GRAND_TOTAL];
            if (isset($rowData[static::COLUMN_NEGOTIATED_PRICE_VALUE])) {
                $negotiatedPriceType = isset($rowData[static::COLUMN_NEGOTIATED_PRICE_TYPE]) ?
                    $rowData[static::COLUMN_NEGOTIATED_PRICE_TYPE] :
                    NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT;
                $negotiatedPriceValue = $rowData[static::COLUMN_NEGOTIATED_PRICE_VALUE] ?: 0;

                if ($negotiatedPriceType == NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT) {
                    $negotiatedTotalPrice -= $negotiatedPriceValue;
                } elseif ($negotiatedPriceType == NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL) {
                    $negotiatedTotalPrice = $negotiatedPriceValue;
                } else {
                    $negotiatedTotalPrice -= $negotiatedTotalPrice * ($negotiatedPriceValue / 100);
                }

                if ($negotiatedTotalPrice < 0) {
                    $negotiatedTotalPrice = 0;
                }
            }

            $entityRow = [
                static::COLUMN_ENTITY_ID => $quoteId,
                static::COLUMN_IS_REGULAR_QUOTE => $isRegularQuote,
                static::COLUMN_STATUS => $status,
                static::COLUMN_CREATOR_ID => $quote['customer_id'],
                static::COLUMN_ORIGINAL_TOTAL_PRICE => $quote[TotalsInterface::KEY_GRAND_TOTAL],
                static::COLUMN_BASE_ORIGINAL_TOTAL_PRICE => $quote[TotalsInterface::KEY_BASE_GRAND_TOTAL],
                static::COLUMN_NEGOTIATED_TOTAL_PRICE => $negotiatedTotalPrice,
                static::COLUMN_BASE_NEGOTIATED_TOTAL_PRICE => $negotiatedTotalPrice
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
