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


use JsonSerializable;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Config\Options\Field\Fieldset;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

class AjaxUpdateFieldset extends Action
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
     * AjaxUpdateFieldset constructor.
     * @param Fieldset $fieldset
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param array|JsonSerializable|null $actions
     */
    public function __construct(
        Fieldset         $fieldset,
        UrlInterface     $urlBuilder,
        ContextInterface $context,
        array            $components = [],
        array            $data = [],
                         $actions = null)
    {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder = $urlBuilder;
        $this->options    = $fieldset->toOptionArray();
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $actions = [];
        if ($this->options) {
            foreach ($this->options as $option) {
                $fieldset_name = $option['label'];
                $fieldset_id   = $option['value'];
                $actions[]     = [
                    'type' => $fieldset_name,
                    'label' => $fieldset_name,
                    'url' => $this->urlBuilder->getUrl(
                        'webforms/field/ajaxMassFieldset',
                        [FieldsetInterface::ID => $fieldset_id]
                    ),
                    'isAjax' => true,
                    'isSourceReloaded' => true,
                    'confirm' => [
                        'title' => __('Change fieldset'),
                        'message' => __('Are you sure?'),
                        '__disableTmpl' => true,
                    ],
                ];
            }
        }

        // Hide if empty
        if (empty($actions)) {
            $config                  = $this->getConfiguration();
            $config['actionDisable'] = true;
            $this->setData('config', $config);
        }

        $this->actions = $actions;
        parent::prepare();
    }

}