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

namespace MageMe\WebForms\Block\Adminhtml\Common\Button;


use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

abstract class SaveContainer implements ButtonProviderInterface
{
    /**
     * Action target
     *
     * @var string
     */
    protected $target = '';
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * SaveContainer constructor.
     * @param RequestInterface $request
     * @param string|null $target
     */
    public function __construct(
        RequestInterface $request,
        string           $target = null
    )
    {
        if ($target) {
            $this->target = $target;
        }
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->target,
                                'actionName' => 'save',
                                'params' => $this->getParams(false),
                            ]
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
            'sort_order' => 100,
        ];
    }

    /**
     * @param bool $redirect
     * @return array
     */
    protected function getParams(bool $redirect): array
    {
        return [$redirect];
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            $this->getSaveClose(),
        ];
    }

    /**
     * Get "save & close" meta
     *
     * @return array
     */
    protected function getSaveClose(): array
    {
        return [
            'id_hard' => 'save_and_close',
            'label' => __('Save & Close'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->target,
                                'actionName' => 'save',
                                'params' => $this->getParams(true),
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
