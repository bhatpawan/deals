<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Model\ResourceModel\TmpFileMessage;

use MageMe\WebForms\Model\ResourceModel\AbstractCollection;
use MageMe\WebForms\Model\ResourceModel\TmpFileMessage as TmpFileMessageResource;
use MageMe\WebForms\Model\TmpFileMessage;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(TmpFileMessage::class, TmpFileMessageResource::class);
    }
}
