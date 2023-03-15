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

namespace MageMe\WebForms\Block\Adminhtml\Field\Edit\Button;


use MageMe\WebForms\Api\Data\FormInterface;

/**
 * Class Back
 * @package MageMe\WebForms\Block\Adminhtml\Field\Edit\Button
 */
class Back extends Generic
{

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back
     *
     * @return string
     */
    private function getBackUrl(): string
    {
        return $this->getUrl('*/form/edit', [
            FormInterface::ID => $this->registry->registry('webforms_form')->getId(),
            'active_tab' => 'fields_section',
            'store' => $this->getStoreId()
        ]);
    }
}
