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

namespace MageMe\WebForms\Model\ResourceModel\Message;


use MageMe\WebForms\Model\Message;
use MageMe\WebForms\Model\ResourceModel\AbstractCollection;
use MageMe\WebForms\Model\ResourceModel\Message as MessageResource;
use Zend_Db_Select;

/**
 * Class Collection
 * @package MageMe\WebForms\Model\ResourceModel\Message
 */
class Collection extends AbstractCollection
{
    /**
     * Returns select count sql
     *
     * @return string
     */
    public function getSelectCountSql(): string
    {
        $select      = parent::getSelectCountSql();
        $countSelect = clone $this->getSelect();

        $countSelect->reset(Zend_Db_Select::HAVING);

        return $select;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(Message::class, MessageResource::class);
    }

}
