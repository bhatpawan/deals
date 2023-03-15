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

namespace MageMe\WebForms\Block\Adminhtml\Result\Reply\Button;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Block\Adminhtml\Common\Button\AclGeneric;

class EditResult extends AclGeneric
{
    const ADMIN_RESOURCE = 'MageMe_WebForms::edit_result';

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        if (!$this->isAllowed()) {
            return [];
        }
        return [
            'label' => __('Edit Result'),
            'on_click' => sprintf("location.href = '%s';", $this->getEditUrl()),
            'sort_order' => 30
        ];
    }

    public function getEditUrl(): string
    {
        $Ids = $this->request->getParam(ResultInterface::ID);

        if (!is_array($Ids)) {
            $Ids = [$Ids];
        }

        if (count($Ids) !== 1) {
            return '';
        }

        return $this->getUrl('*/*/edit', [ResultInterface::ID => $Ids[0]]);
    }
}
