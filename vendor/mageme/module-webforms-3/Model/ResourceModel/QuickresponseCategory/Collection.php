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

namespace MageMe\WebForms\Model\ResourceModel\QuickresponseCategory;


use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Model\QuickresponseCategory;
use MageMe\WebForms\Model\ResourceModel\AbstractCollection;
use MageMe\WebForms\Model\ResourceModel\QuickresponseCategory as QuickresponseCategoryResource;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Class Collection
 * @package MageMe\WebForms\Model\ResourceModel\QuickresponseCategory
 */
class Collection extends SearchResult
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $collection  = $this->addOrder(QuickresponseCategoryInterface::POSITION, 'asc');
        $optionArray = [];
        foreach ($collection as $element) {
            $optionArray[] = ['value' => $element->getId(), 'label' => $element->getName()];
        }
        return $optionArray;
    }
}