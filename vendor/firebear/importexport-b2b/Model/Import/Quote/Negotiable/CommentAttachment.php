<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote\Negotiable;

use Magento\NegotiableQuote\Api\Data\CommentAttachmentInterface;

class CommentAttachment extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CommentAttachmentInterface::ATTACHMENT_ID;
    const COLUMN_COMMENT_ID = CommentAttachmentInterface::COMMENT_ID;
    const COLUMN_FILE_NAME = CommentAttachmentInterface::FILE_NAME;
    const COLUMN_FILE_PATH = CommentAttachmentInterface::FILE_PATH;

    /**
     * Error Codes
     */
    const ERROR_FILE_NAME_IS_EMPTY = 'negotiableQuoteCommentAttachmentFileNameIsEmpty';
    const ERROR_FILE_PATH_IS_EMPTY = 'negotiableQuoteCommentAttachmentFilePathIsEmpty';

    protected $_messageTemplates = [
        self::ERROR_FILE_NAME_IS_EMPTY => 'Negotiable quote comment attachment %s is empty',
        self::ERROR_FILE_PATH_IS_EMPTY => 'Negotiable quote comment attachment %s is empty'
    ];

    protected $columnPrefix = 'negotiable_comment_attachment';

    protected $excludeFields = [
        self::COLUMN_ENTITY_ID,
        self::COLUMN_COMMENT_ID
    ];

    protected $optGroupName = 'Negotiable Quote Comment Attachment';

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_COMMENT_ID.' = ?', $this->getCommentIdFromMap());

        foreach ([static::COLUMN_FILE_NAME, static::COLUMN_FILE_PATH] as $column) {
            $select->where($column.' = ?', $rowData[$column]);
        }

        return $this->_connection->fetchOne($select);
    }

    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        $columnFileName = static::COLUMN_FILE_NAME;
        if (empty($rowData[$columnFileName])) {
            $this->addRowError(static::ERROR_FILE_NAME_IS_EMPTY, $rowNumber, $columnFileName);
        }

        $columnFilePath = static::COLUMN_FILE_PATH;
        if (empty($rowData[$columnFilePath])) {
            $this->addRowError(static::ERROR_FILE_PATH_IS_EMPTY, $rowNumber, $columnFilePath);
        }
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $commentId = $this->getCommentIdFromMap();
        if ($commentId) {
            $entityId = $this->getExistEntityId($rowData);
            $newEntity = false;
            if (!$entityId) {
                /* create new entity id */
                $newEntity = true;
                $entityId = $this->_getNextEntityId();
            }

            $entityRow = [
                static::COLUMN_ENTITY_ID => $entityId,
                static::COLUMN_COMMENT_ID => $commentId
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
