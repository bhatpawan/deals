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

namespace MageMe\WebForms\Ui\Component\Quickresponse\Listing\MassAction\SubAction;

use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Config\Options\QuickresponseCategory;
use MageMe\WebForms\Config\Options\QuickresponseCategoryInline;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

class AjaxUpdateCategory extends Action
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var array
     */
    private $options;

    /**
     * @param QuickresponseCategoryInline $category
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param $actions
     */
    public function __construct(
        QuickresponseCategory $category,
        UrlInterface          $urlBuilder,
        ContextInterface      $context,
        array                 $components = [],
        array                 $data = [],
        array                 $actions = []
    )
    {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder = $urlBuilder;
        $this->options    = $category->toOptionArray();
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $actions = [];
        if ($this->options) {
            foreach ($this->options as $option) {
                $category_name = $option['label'];
                $category_id   = $option['value'];
                $actions[]     = [
                    'type' => $category_name,
                    'label' => $category_name,
                    'url' => $this->urlBuilder->getUrl(
                        'webforms/quickresponse/ajaxMassCategory',
                        [QuickresponseCategoryInterface::ID => $category_id]
                    ),
                    'isAjax' => true,
                    'isSourceReloaded' => true,
                    'confirm' => [
                        'title' => __('Change category'),
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