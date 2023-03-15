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

namespace MageMe\WebForms\Block\Adminhtml\Form\Edit\Button;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Block\Adminhtml\Common\Button\AclGeneric;

class AddField extends AclGeneric
{
    const ADMIN_RESOURCE = 'MageMe_WebForms::save_form';

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        if (!$this->isAllowed()) {
            return [];
        }
        $id     = (int)$this->request->getParam(FormInterface::ID);
        $button = [
            'label' => __('Add Field'),
            'on_click' => sprintf("location.href = '%s';", $this->getAddFieldUrl($id)),
            'class' => 'add action-secondary',
            'sort_order' => 50,
        ];
        if (!$id) {
            $button['style'] = 'display:none';
        }
        return $button;
    }

    /**
     * Get URL for add field
     *
     * @param int $formId
     * @return string
     */
    private function getAddFieldUrl(int $formId): string
    {
        return $this->getUrl('webforms/field/new', [FieldInterface::FORM_ID => $formId]);
    }
}
