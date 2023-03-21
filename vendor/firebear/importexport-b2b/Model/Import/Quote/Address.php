<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote;

use Magento\Quote\Model\Quote\Address as QuoteAddress;

class Address extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = 'address_id';
    const COLUMN_QUOTE_ID = 'quote_id';
    const COLUMN_ADDRESS_TYPE = 'address_type';

    public function getAllFields()
    {
        return [];
    }

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_QUOTE_ID.' = ?', $this->getQuoteIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $quoteId = $this->getQuoteIdFromMap();
        if ($quoteId && !$this->getExistEntityId($rowData)) {
            $toCreate[] = [
                static::COLUMN_ENTITY_ID => $this->_getNextEntityId(),
                static::COLUMN_QUOTE_ID => $quoteId,
                static::COLUMN_ADDRESS_TYPE => QuoteAddress::ADDRESS_TYPE_SHIPPING
            ];
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }
}
