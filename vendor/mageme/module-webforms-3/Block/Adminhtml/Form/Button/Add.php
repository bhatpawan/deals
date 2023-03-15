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

namespace MageMe\WebForms\Block\Adminhtml\Form\Button;


use MageMe\WebForms\Block\Adminhtml\Common\Button\AclGeneric;

/**
 *
 */
class Add extends AclGeneric
{
    /**
     *
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::add_form';

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        if (!$this->isAllowed()) {
            return [];
        }
        return [
            'label' => __('Add Form'),
            'on_click' => sprintf("location.href = '%s';", $this->getAddUrl()),
            'class' => 'primary',
            'sort_order' => 20
        ];
    }

    /**
     * Get URL for add form
     *
     * @return string
     */
    private function getAddUrl(): string
    {
        return $this->getUrl('webforms/form/new');
    }
}
