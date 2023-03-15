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

namespace MageMe\WebForms\Ui\Component\Result\Listing;

use Magento\Framework\Api\Filter;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        if (strstr($filter->getField(), 'field_')) {
            $field_id = str_replace('field_', '', $filter->getField());
            $filter->setField('results_values_' . $field_id . '.value');
        }

        parent::addFilter($filter);
    }
}
