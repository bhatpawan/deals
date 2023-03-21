<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Quote\Negotiable;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote;

class Entity extends AbstractAdapter
{
    protected $tableName = NegotiableQuote::NEGOTIABLE_QUOTE_TABLE;
    protected $retrieveField = NegotiableQuoteInterface::QUOTE_ID;
    protected $columnPrefix = 'negotiable';

    protected function _getHeaderColumns()
    {
        return array_filter(
            parent::_getHeaderColumns(),
            function ($field) {
                return $field != NegotiableQuoteInterface::SNAPSHOT;
            }
        );
    }
}
