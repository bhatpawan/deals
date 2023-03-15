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

namespace MageMe\WebForms\Model\Field;


use MageMe\Core\Helper\DateHelper;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Api\ResultValueRepositoryInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Config\Config as FieldConfig;
use MageMe\WebForms\Helper\CssHelper;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Model\StoreFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

class Context
{

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;
    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var DateHelper
     */
    protected $dateHelper;
    /**
     * @var FormKey
     */
    protected $formKey;
    /**
     * @var FilterProvider
     */
    protected $filterProvider;
    /**
     * @var FieldConfig
     */
    protected $fieldConfig;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var FieldFactory
     */
    protected $fieldFactory;
    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;
    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;
    /**
     * @var LogicRepositoryInterface
     */
    protected $logicRepository;
    /**
     * @var ResultValueRepositoryInterface
     */
    protected $resultValueRepository;
    /**
     * @var CssHelper
     */
    protected $cssHelper;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var AbstractResource|null
     */
    protected $resource;

    /**
     * @var AbstractDb|null
     */
    protected $resourceCollection;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Context constructor.
     * @param CssHelper $cssHelper
     * @param ResultValueRepositoryInterface $resultValueRepository
     * @param LogicRepositoryInterface $logicRepository
     * @param FormRepositoryInterface $formRepository
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldFactory $fieldFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param FieldConfig $fieldConfig
     * @param FilterProvider $filterProvider
     * @param FormKey $formKey
     * @param DateHelper $dateHelper
     * @param SessionFactory $sessionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param FilterBuilder $filterBuilder
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreFactory $storeFactory
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        CssHelper                        $cssHelper,
        ResultValueRepositoryInterface   $resultValueRepository,
        LogicRepositoryInterface         $logicRepository,
        FormRepositoryInterface          $formRepository,
        FieldRepositoryInterface         $fieldRepository,
        FieldFactory                     $fieldFactory,
        RequestInterface                 $request,
        UrlInterface                     $urlBuilder,
        FieldConfig                      $fieldConfig,
        FilterProvider                   $filterProvider,
        FormKey                          $formKey,
        DateHelper                       $dateHelper,
        SessionFactory                   $sessionFactory,
        ScopeConfigInterface             $scopeConfig,
        SearchCriteriaBuilder            $searchCriteriaBuilder,
        SortOrderBuilder                 $sortOrderBuilder,
        FilterBuilder                    $filterBuilder,
        StoreRepositoryInterface         $storeRepository,
        StoreFactory                     $storeFactory,
        \Magento\Framework\Model\Context $context,
        Registry                         $registry,
        CategoryRepositoryInterface      $categoryRepository,
        ProductRepositoryInterface       $productRepository,
        AbstractResource                 $resource = null,
        AbstractDb                       $resourceCollection = null,
        array                            $data = []
    )
    {
        $this->filterBuilder         = $filterBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig           = $scopeConfig;
        $this->session               = $sessionFactory->create();
        $this->dateHelper            = $dateHelper;
        $this->formKey               = $formKey;
        $this->filterProvider        = $filterProvider;
        $this->fieldConfig           = $fieldConfig;
        $this->urlBuilder            = $urlBuilder;
        $this->request               = $request;
        $this->fieldFactory          = $fieldFactory;
        $this->fieldRepository       = $fieldRepository;
        $this->formRepository        = $formRepository;
        $this->logicRepository       = $logicRepository;
        $this->resultValueRepository = $resultValueRepository;
        $this->cssHelper             = $cssHelper;
        $this->storeRepository       = $storeRepository;
        $this->storeFactory          = $storeFactory;
        $this->context               = $context;
        $this->registry              = $registry;
        $this->categoryRepository    = $categoryRepository;
        $this->productRepository     = $productRepository;
        $this->resource              = $resource;
        $this->resourceCollection    = $resourceCollection;
        $this->data                  = $data;
    }

    /**
     * @return CategoryRepositoryInterface
     */
    public function getCategoryRepository(): CategoryRepositoryInterface
    {
        return $this->categoryRepository;
    }

    /**
     * @return ProductRepositoryInterface
     */
    public function getProductRepository(): ProductRepositoryInterface
    {
        return $this->productRepository;
    }

    /**
     * @return FilterBuilder
     */
    public function getFilterBuilder(): FilterBuilder
    {
        return $this->filterBuilder;
    }

    /**
     * @return SortOrderBuilder
     */
    public function getSortOrderBuilder(): SortOrderBuilder
    {
        return $this->sortOrderBuilder;
    }

    /**
     * @return SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder(): SearchCriteriaBuilder
    {
        return $this->searchCriteriaBuilder;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return DateHelper
     */
    public function getDateHelper(): DateHelper
    {
        return $this->dateHelper;
    }

    /**
     * @return FormKey
     */
    public function getFormKey(): FormKey
    {
        return $this->formKey;
    }

    /**
     * @return FilterProvider
     */
    public function getFilterProvider(): FilterProvider
    {
        return $this->filterProvider;
    }

    /**
     * @return FieldConfig
     */
    public function getFieldConfig(): FieldConfig
    {
        return $this->fieldConfig;
    }

    /**
     * @return UrlInterface
     */
    public function getUrlBuilder(): UrlInterface
    {
        return $this->urlBuilder;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return FieldFactory
     */
    public function getFieldFactory(): FieldFactory
    {
        return $this->fieldFactory;
    }

    /**
     * @return FieldRepositoryInterface
     */
    public function getFieldRepository(): FieldRepositoryInterface
    {
        return $this->fieldRepository;
    }

    /**
     * @return FormRepositoryInterface
     */
    public function getFormRepository(): FormRepositoryInterface
    {
        return $this->formRepository;
    }

    /**
     * @return LogicRepositoryInterface
     */
    public function getLogicRepository(): LogicRepositoryInterface
    {
        return $this->logicRepository;
    }

    /**
     * @return ResultValueRepositoryInterface
     */
    public function getResultValueRepository(): ResultValueRepositoryInterface
    {
        return $this->resultValueRepository;
    }

    /**
     * @return CssHelper
     */
    public function getCssHelper(): CssHelper
    {
        return $this->cssHelper;
    }

    /**
     * @return StoreRepositoryInterface
     */
    public function getStoreRepository(): StoreRepositoryInterface
    {
        return $this->storeRepository;
    }

    /**
     * @return StoreFactory
     */
    public function getStoreFactory(): StoreFactory
    {
        return $this->storeFactory;
    }

    /**
     * @return \Magento\Framework\Model\Context
     */
    public function getContext(): \Magento\Framework\Model\Context
    {
        return $this->context;
    }

    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    /**
     * @return AbstractResource|null
     */
    public function getResource(): ?AbstractResource
    {
        return $this->resource;
    }

    /**
     * @return AbstractDb|null
     */
    public function getResourceCollection(): ?AbstractDb
    {
        return $this->resourceCollection;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }


}