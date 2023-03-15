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

namespace MageMe\WebForms\Block\Adminhtml\Quickresponse\Button\Form;


use MageMe\WebForms\Api\Data\QuickresponseInterface;
use MageMe\WebForms\Block\Adminhtml\Common\Button\Generic;

class Delete extends Generic
{
    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        $data            = [];
        $quickresponseId = (int)$this->request->getParam(QuickresponseInterface::ID);
        if (!$quickresponseId) {
            return $data;
        }
        return [
            'label' => __('Delete Quick Response'),
            'class' => 'delete',
            'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to delete this quick response?'
                ) . '\', \'' . $this->getDeleteUrl($quickresponseId) . '\', {"data": {}})',
            'sort_order' => 20,
        ];
    }

    /**
     * Get delete URL
     *
     * @param int $id
     * @return string
     */
    public function getDeleteUrl(int $id): string
    {
        return $this->getUrl('webforms/quickresponse/delete', [QuickresponseInterface::ID => $id]);
    }
}
