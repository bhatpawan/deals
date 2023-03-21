<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Quote;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\Quote\Api\Data\CartInterface;

class Entity extends AbstractAdapter
{
    protected $tableName = 'quote';
    protected $retrieveField = CartInterface::KEY_ENTITY_ID;
}
