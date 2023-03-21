<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Quote;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\Quote\Api\Data\CartItemInterface;

class Item extends AbstractAdapter
{
    protected $tableName = 'quote_item';
    protected $retrieveField = CartItemInterface::KEY_QUOTE_ID;
    protected $columnPrefix = 'item';
}
