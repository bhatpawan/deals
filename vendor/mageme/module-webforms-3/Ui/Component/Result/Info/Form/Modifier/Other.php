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

use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\Form as FormResource;
use Magento\Backend\Model\Authorization\RoleLocator;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\Form;

class Other extends AbstractModifier
{
    /**
     * @var RoleLocator
     */
    private $roleLocator;
    /**
     * @var AuthorizationInterface
     */
    private $authorization;
    /**
     * @var FormResource
     */
    private $formResource;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Other constructor.
     * @param UrlInterface $urlBuilder
     * @param FormResource $formResource
     * @param AuthorizationInterface $authorization
     * @param RoleLocator $roleLocator
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param FormRepositoryInterface $formRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param TimezoneInterface $timezone
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface              $urlBuilder,
        FormResource              $formResource,
        AuthorizationInterface    $authorization,
        RoleLocator               $roleLocator,
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        ScopeConfigInterface      $scopeConfig,
        FormRepositoryInterface   $formRepository,
        ResultRepositoryInterface $resultRepository,
        TimezoneInterface         $timezone,
        RequestInterface          $request
    )
    {
        parent::__construct($searchCriteriaBuilder, $scopeConfig, $formRepository, $resultRepository, $timezone,
            $request);
        $this->roleLocator   = $roleLocator;
        $this->authorization = $authorization;
        $this->formResource  = $formResource;
        $this->urlBuilder    = $urlBuilder;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    public function modifyData(array $data): array
    {
        $result              = $this->getResult();
        $data['customer_id'] = $result->getCustomerId();
        $data['nresult_id']  = $result->getId();
        return $data;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    public function modifyMeta(array $meta): array
    {
        $result = $this->getResult();
        if ($result->getCustomerId()) {
            $searchCriteria = $this->searchCriteriaBuilder;
            if (!$this->authorization->isAllowed('Magento_Backend::all')) {
                $ids            = $this->formResource->getRoleFormsIds($this->roleLocator->getAclRoleId());
                $searchCriteria = $searchCriteria->addFilter(ResultInterface::FORM_ID, $ids, 'in');
            }
            $searchCriteria = $searchCriteria
                ->addFilter(ResultInterface::CUSTOMER_ID, $result->getCustomerId())
                ->addFilter(ResultInterface::ID, $result->getId(), 'neq')
                ->create();
            $resultCount    = $this->resultRepository->getList($searchCriteria)->getTotalCount();
            if ($resultCount) {
                $meta['other'] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Form\Fieldset::NAME,
                                'label' => __('Other Results (%1)', [$resultCount]),
                                'collapsible' => true,
                                'opened' => false,
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
                                            'nresult_id' => '${ $.externalProvider }:params.nresult_id',
                                            'customer_id' => '${ $.externalProvider }:params.customer_id',
                                            '__disableTmpl' => false,
                                        ],
                                        'imports' => [
                                            'nresult_id' => '${ $.provider }:data.nresult_id',
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
