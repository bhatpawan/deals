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

namespace MageMe\WebForms\Ui\Component\Field\Listing\MassAction\SubAction;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Config\Options\YesNo;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

class AjaxUpdateRequired extends Action
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var array
     */
    protected $options;

    /**
     * AjaxUpdateRequired constructor.
     * @param YesNo $options
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param null $actions
     */
    public function __construct(
        YesNo            $options,
        UrlInterface     $urlBuilder,
        ContextInterface $context, array $components = [], array $data = [], $actions = null)
    {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder = $urlBuilder;
        $this->options    = $options->toOptionArray();
    }

    public function prepare()
    {
        $actions = [];
        if ($this->options) {
            foreach ($this->options as $option) {
                $label     = $option['label'];
                $value     = $option['value'];
                $actions[] = [
                    'type' => $label,
                    'label' => __($label),
                    'url' => $this->urlBuilder->getUrl(
                        'webforms/field/ajaxMassRequired',
                        [FieldInterface::IS_REQUIRED => $value]
                    ),
                    'isAjax' => true,
                    'isSourceReloaded' => true,
                    'confirm' => [
                        'title' => __('Change value'),
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