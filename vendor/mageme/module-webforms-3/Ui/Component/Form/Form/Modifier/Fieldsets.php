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

namespace MageMe\WebForms\Ui\Component\Form\Form\Modifier;


use MageMe\WebForms\Api\Data\FormInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\Form;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Fieldsets implements ModifierInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * LogicRules constructor.
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface     $urlBuilder,
        RequestInterface $request
    )
    {
        $this->request    = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        $formId = (int)$this->request->getParam(FormInterface::ID);
        if (!$formId) return $meta;
        $meta['fieldsets_section'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('Fieldsets'),
                        'collapsible' => true,
                        'opened' => false,
                        'sortOrder' => 210,
                    ]
                ]
            ],
            'children' => [
                'webforms_form_fieldset_listing' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'insertListing',
                                'autoRender' => true,
                                'ns' => 'webforms_form_fieldset_listing',
                                'externalProvider' => 'webforms_form_fieldset_listing.webforms_form_fieldset_listing_data_source',
                                'exports' => [
                                    'store_id' => '${ $.externalProvider }:params.store_id',
                                    'form_id' => '${ $.externalProvider }:params.form_id',
                                    '__disableTmpl' => false,
                                ],
                                'imports' => [
                                    'store_id' => '${ $.provider }:data.store_id',
                                    'form_id' => '${ $.provider }:data.form_id',
                                    '__disableTmpl' => false,
                                ],
                                'render_url' => $this->getRenderUrl(),
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $meta;
    }

    /**
     * @return string
     */
    public function getRenderUrl(): string
    {
        return $this->getUrl('mui/index/render', ['_current' => true]);
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
