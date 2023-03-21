<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Quote\Negotiable;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\NegotiableQuote\Api\Data\CommentInterface;
use Magento\NegotiableQuote\Model\ResourceModel\Comment as NegotiableQuoteComment;

class Comment extends AbstractAdapter
{
    protected $tableName = NegotiableQuoteComment::NEGOTIABLE_QUOTE_COMMENT_TABLE;
    protected $retrieveField = CommentInterface::PARENT_ID;
    protected $columnPrefix = 'negotiable_comment';
}
