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

namespace MageMe\WebForms\Model\ResourceModel;


use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Setup\Table\QuickresponseCategoryTable;
use Magento\Framework\Exception\LocalizedException;

class QuickresponseCategory extends AbstractDb
{
    const DB_TABLE = QuickresponseCategoryTable::TABLE_NAME;
    const ID_FIELD = QuickresponseCategoryInterface::ID;

    /**
     * Get next category position
     *
     * @return int
     * @throws LocalizedException
     */
    public function getNextPosition(): int
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), QuickresponseCategoryInterface::POSITION)
            ->order(QuickresponseCategoryInterface::POSITION . ' DESC');

        $position = (int)$this->getConnection()->fetchOne($select);
        return ++$position;
    }
}
