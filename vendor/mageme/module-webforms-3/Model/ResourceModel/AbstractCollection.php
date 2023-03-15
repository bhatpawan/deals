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


/**
 * Class AbstractCollection
 * @package MageMe\WebForms\Model\ResourceModel
 */
abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @inheritdoc
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this as $item) {
            $this->_resource->deserializeFieldsFromJSON($item);
        }
        return $this;
    }
}
