<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote\Negotiable;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\NegotiableQuote\Api\Data\CommentInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * @method DateTime getDataDateTime
 */
class Comment extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CommentInterface::ENTITY_ID;
    const COLUMN_PARENT_ID = CommentInterface::PARENT_ID;
    const COLUMN_CREATOR_TYPE = CommentInterface::CREATOR_TYPE;
    const COLUMN_CREATOR_ID = CommentInterface::CREATOR_ID;
    const COLUMN_COMMENT = CommentInterface::COMMENT;
    const COLUMN_CREATED_AT = CommentInterface::CREATED_AT;

    /**
     * Error Codes
     */
    const ERROR_COMMENT_IS_EMPTY = 'negotiableQuoteCommentIsEmpty';

    protected $_messageTemplates = [
        self::ERROR_COMMENT_IS_EMPTY => 'Negotiable quote comment %s is empty'
    ];

    protected $columnPrefix = 'negotiable_comment';

    protected $excludeFields = [
        self::COLUMN_ENTITY_ID,
        self::COLUMN_PARENT_ID
    ];

    protected $optGroupName = 'Negotiable Quote Comment';

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_PARENT_ID.' = ?', $this->getQuoteIdFromMap());

        foreach ([
            static::COLUMN_CREATOR_TYPE,
            static::COLUMN_CREATOR_ID,
            static::COLUMN_COMMENT,
            static::COLUMN_CREATED_AT] as $column) {
            $select->where($column.' = ?', $rowData[$column]);
        }

        return $this->_connection->fetchOne($select);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        $columnComment = static::COLUMN_COMMENT;
        if (empty($rowData[$columnComment])) {
            $this->addRowError(static::ERROR_COMMENT_IS_EMPTY, $rowNumber, $columnComment);
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $quoteId = $this->getQuoteIdFromMap();
        $negotiableQuote = null;
        if ($quoteId) {
            $fields = [
                NegotiableQuoteInterface::CREATOR_TYPE,
                NegotiableQuoteInterface::CREATOR_ID
            ];

            $negotiableQuote = $this->getNegotiableQuote($quoteId, $fields);
        }

        if ($negotiableQuote) {
            if (empty($rowData[static::COLUMN_CREATOR_TYPE])) {
                $rowData[static::COLUMN_CREATOR_TYPE] = $negotiableQuote[NegotiableQuoteInterface::CREATOR_TYPE];
            }
            if (empty($rowData[static::COLUMN_CREATOR_ID])) {
                $rowData[static::COLUMN_CREATOR_ID] = $negotiableQuote[NegotiableQuoteInterface::CREATOR_ID];
            }
            if (empty($rowData[static::COLUMN_CREATED_AT])) {
                $rowData[static::COLUMN_CREATED_AT] = $this->getDataDateTime()->gmtDate();
            }

            $entityId = $this->getExistEntityId($rowData);
            $newEntity = false;
            if (!$entityId) {
                /* create new entity id */
                $newEntity = true;
                $entityId = $this->_getNextEntityId();
            }

            $commentIdsMap = $this->storage->getCommentIdsMap();
            $commentIdsMap[$this->incrementCommentId] = $entityId;
            $this->storage->setCommentIdsMap($commentIdsMap);

            $entityRow = [
                static::COLUMN_ENTITY_ID => $entityId,
                static::COLUMN_PARENT_ID => $quoteId
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
