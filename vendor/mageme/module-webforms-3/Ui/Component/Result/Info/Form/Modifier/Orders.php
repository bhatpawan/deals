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

namespace MageMe\WebForms\Ui\Component\Result\Info\Form\Modifier;

use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Ui\Component\Form;

class Orders extends AbstractModifier
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Orders constructor.
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     * @param OrderRepository $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param FormRepositoryInterface $formRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param TimezoneInterface $timezone
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface              $urlBuilder,
        AuthorizationInterface    $authorization,
        OrderRepository           $orderRepository,
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        ScopeConfigInterface      $scopeConfig,
        FormRepositoryInterface   $formRepository,
        ResultRepositoryInterface $resultRepository,
        TimezoneInterface         $timezone,
        RequestInterface          $request)
    {
        parent::__construct($searchCriteriaBuilder, $scopeConfig, $formRepository, $resultRepository, $timezone,
            $request);
        $this->orderRepository       = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->authorization         = $authorization;
        $this->urlBuilder            = $urlBuilder;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    public function modifyMeta(array $meta): array
    {
        if (!$this->authorization->isAllowed('Magento_Sales::actions_view')) {
            return $meta;
        }
        $result = $this->getResult();
        if ($result->getCustomerId()) {
            $orderCount = $this->orderRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter(OrderInterface::CUSTOMER_ID, $result->getCustomerId())
                    ->create())->getTotalCount();
            if ($orderCount) {
                $meta['order_history'] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Fieldset::NAME,
                                'label' => __('Customer Orders (%1)', [$orderCount]),
                                'collapsible' => true,
                                'opened' => false,
                            ]
                        ]
                    ],
                    'children' => [
                        'webforms_result_order_listing' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'insertListing',
                                        'autoRender' => true,
                                        'ns' => 'webforms_result_order_listing',
                                        'externalProvider' => 'webforms_result_order_listing.sales_order_grid_data_source',
                                        'exports' => [
                                            'customer_id' => '${ $.externalProvider }:params.customer_id',
                                            '__disableTmpl' => false,
                                        ],
                                        'imports' => [
                                            'customer_id' => '${ $.provider }:data.customer_id',
                                            '__disableTmpl' => false,
                                        ],
                                        'render_url' => $this->getRenderUrl(),
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }
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
