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


use MageMe\WebForms\Block\Adminhtml\Common\Button\Generic;

class SaveAndEmail extends Generic
{
    const TARGET_NAME = 'webforms_result_reply_form.webforms_result_reply_form';

    /**
     * @inheritDoc
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Save Reply And E-mail'),
            'class' => 'save primary',
            'sort_order' => 60,
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => self::TARGET_NAME,
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    ['send_email' => 1],
                                ]
                            ]
                        ]
                    ]
                ],

            ]
        ];
    }
}
