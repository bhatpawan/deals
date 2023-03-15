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

namespace MageMe\WebForms\Model\ResourceModel\Quickresponse;


use MageMe\WebForms\Api\Data\QuickresponseInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Class Collection
 * @package MageMe\WebForms\Model\ResourceModel\Quickresponse
 */
class Collection extends SearchResult
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $collection  = $this->addOrder(QuickresponseInterface::TITLE, 'asc');
        $optionArray = [];
        foreach ($collection as $element) {
            $optionArray[] = ['value' => $element->getId(), 'label' => $element->getTitle()];
        }
        return $optionArray;
    }


}