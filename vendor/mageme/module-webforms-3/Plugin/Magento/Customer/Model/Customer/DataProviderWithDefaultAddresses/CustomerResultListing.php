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

namespace MageMe\WebForms\Plugin\Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses;

use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\Form;

class CustomerResultListing
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface $urlBuilder,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param DataProviderWithDefaultAddresses $subject
     * @param array $meta
     * @return array
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PluginInspection
     */
    public function afterGetMeta(DataProviderWithDefaultAddresses $subject, array $meta): array {
        if (!$this->request->getParam(CustomerInterface::ID)) {
            return $meta;
        }
        $meta['webforms'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Fieldset::NAME,
                        'label' => __('WebForms'),
                        'sortOrder' => 100
                    ]
                ]
            ],
            'children' => [
                'webforms_customer_result_listing' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'insertListing',
                                'autoRender' => true,
                                'ns' => 'webforms_customer_result_listing',
                                'externalProvider' => 'webforms_customer_result_listing.webforms_customer_result_listing_data_source',
                                'exports' => [
                                    'customer_id' => '${ $.externalProvider }:params.customer_id',
                                    '__disableTmpl' => false,
                                ],
                                'imports' => [
                                    'customer_id' => '${ $.provider }:data.customer.entity_id',
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
    private function getRenderUrl(): string
    {
        return $this->getUrl('mui/index/render', ['_current' => true]);
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
