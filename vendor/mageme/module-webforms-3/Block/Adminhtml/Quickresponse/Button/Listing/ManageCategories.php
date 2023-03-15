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

namespace MageMe\WebForms\Block\Adminhtml\Quickresponse\Button\Listing;


use MageMe\WebForms\Block\Adminhtml\Common\Button\Generic;

class ManageCategories extends Generic
{
    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Manage Categories'),
            'on_click' => sprintf("location.href = '%s';", $this->getCategoriesUrl()),
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for manage quickresponse categories
     *
     * @return string
     */
    private function getCategoriesUrl(): string
    {
        return $this->getUrl('webforms/quickresponsecategory/index');
    }
}