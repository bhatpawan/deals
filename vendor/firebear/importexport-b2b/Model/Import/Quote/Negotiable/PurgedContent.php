<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote\Negotiable;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\NegotiableQuote\Model\PurgedContent as ModelPurgedContent;

/**
 * @method SerializerInterface getDataSerializer
 */
class PurgedContent extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = ModelPurgedContent::QUOTE_ID;
    const COLUMN_PURGED_DATA = ModelPurgedContent::PURGED_DATA;

    public function getAllFields()
    {
        return [];
    }

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_ENTITY_ID.' = ?', $this->getQuoteIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $quoteId = $this->getQuoteIdFromMap();
        if ($quoteId && $this->getNegotiableQuote($quoteId) && !$this->getExistEntityId($rowData)) {
            $toCreate[] = [
                static::COLUMN_ENTITY_ID => $quoteId,
                static::COLUMN_PURGED_DATA => $this->getDataSerializer()->serialize([])
            ];
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }
}
