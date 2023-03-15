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


use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Block\Adminhtml\Common\Button\AclGeneric;

class AddFieldset extends AclGeneric
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
            'label' => __('Add Fieldset'),
            'on_click' => sprintf("location.href = '%s';", $this->getAddFieldsetUrl($id)),
            'class' => 'add action-secondary',
            'sort_order' => 40,
        ];
        if (!$id) {
            $button['style'] = 'display:none';
        }
        return $button;
    }

    /**
     * Get URL for add fieldset
     *
     * @param int $formId
     * @return string
     */
    private function getAddFieldsetUrl(int $formId): string
    {
        return $this->getUrl('webforms/fieldset/new', [FieldsetInterface::FORM_ID => $formId]);
    }
}
