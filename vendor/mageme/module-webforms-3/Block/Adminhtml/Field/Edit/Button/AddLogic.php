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


use MageMe\WebForms\Api\Data\LogicInterface;

/**
 * Class AddLogic
 * @package MageMe\WebForms\Block\Adminhtml\Field\Edit\Button
 */
class AddLogic extends Generic
{

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Add logic'),
            'on_click' => sprintf("location.href = '%s';", $this->getAddLogicUrl()),
            'class' => 'add action-secondary',
            'sort_order' => 30,
            'style' => 'display:none'
        ];
    }

    /**
     * Get URL for add logic
     *
     * @return string
     */
    private function getAddLogicUrl(): string
    {
        return $this->getUrl('*/logic/new', [LogicInterface::FIELD_ID => $this->registry->registry('webforms_field')->getId()]);
    }
}
