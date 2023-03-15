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

namespace MageMe\WebForms\Ui\Component\Common\Listing\MassAction\SubAction;


use JsonSerializable;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

abstract class AbstractAjaxUpdateStatus extends Action
{

    /**
     * Action controller URL
     */
    protected $controllerUrl = '';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param OptionSourceInterface $status
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param array|JsonSerializable|null $actions
     */
    public function __construct(
        OptionSourceInterface $status,
        UrlInterface          $urlBuilder,
        ContextInterface      $context,
        array                 $components = [],
        array                 $data = [],
                              $actions = null
    )
    {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder = $urlBuilder;
        $this->options    = $status->toOptionArray();
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $actions = [];
        if ($this->options) {
            foreach ($this->options as $option) {
                $status_name = $option['label'];
                $status_id   = $option['value'];
                $actions[]   = [
                    'type' => $status_name,
                    'label' => __($status_name),
                    'url' => $this->urlBuilder->getUrl(
                        $this->controllerUrl,
                        ['status' => $status_id]
                    ),
                    'isAjax' => true,
                    'isSourceReloaded' => true,
                    'confirm' => [
                        'title' => __('Change status'),
                        'message' => __('Are you sure?'),
                        '__disableTmpl' => true,
                    ],
                ];
            }
        }

        $this->actions = $actions;
        parent::prepare();
    }

}
