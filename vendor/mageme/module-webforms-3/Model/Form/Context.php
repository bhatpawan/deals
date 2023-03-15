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

namespace MageMe\WebForms\Model\Form;


use MageMe\Core\Helper\DateHelper;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Model\FieldsetFactory;
use MageMe\WebForms\Model\FormFactory;
use MageMe\WebForms\Model\LogicFactory;
use MageMe\WebForms\Model\StoreFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Context
 * @package MageMe\WebForms\Model\Form
 */
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
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var LogicFactory
     */
    protected $logicFactory;

    /**
     * @var LogicRepositoryInterface
     */
    protected $logicRepository;

    /**
     * @var FieldsetFactory
     */
    protected $fieldsetFactory;

    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;

    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

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
     * Context constructor.
     * @param DateHelper $dateHelper
     * @param SessionFactory $sessionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldFactory $fieldFactory
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param FieldsetFactory $fieldsetFactory
     * @param LogicRepositoryInterface $logicRepository
     * @param LogicFactory $logicFactory
     * @param FormRepositoryInterface $formRepository
     * @param FormFactory $formFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param FilterBuilder $filterBuilder
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreFactory $storeFactory
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        DateHelper                       $dateHelper,
        SessionFactory                   $sessionFactory,
        ScopeConfigInterface             $scopeConfig,
        FieldRepositoryInterface         $fieldRepository,
        FieldFactory                     $fieldFactory,
        FieldsetRepositoryInterface      $fieldsetRepository,
        FieldsetFactory                  $fieldsetFactory,
        LogicRepositoryInterface         $logicRepository,
        LogicFactory                     $logicFactory,
        FormRepositoryInterface          $formRepository,
        FormFactory                      $formFactory,
        SearchCriteriaBuilder            $searchCriteriaBuilder,
        SortOrderBuilder                 $sortOrderBuilder,
        FilterBuilder                    $filterBuilder,
        StoreRepositoryInterface         $storeRepository,
        StoreFactory                     $storeFactory,
        \Magento\Framework\Model\Context $context,
        Registry                         $registry,
        AbstractResource                 $resource = null,
        AbstractDb                       $resourceCollection = null,
        array                            $data = []
    )
    {
        $this->storeRepository       = $storeRepository;
        $this->storeFactory          = $storeFactory;
        $this->context               = $context;
        $this->registry              = $registry;
        $this->resource              = $resource;
        $this->resourceCollection    = $resourceCollection;
        $this->data                  = $data;
        $this->filterBuilder         = $filterBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->formFactory           = $formFactory;
        $this->formRepository        = $formRepository;
        $this->logicFactory          = $logicFactory;
        $this->logicRepository       = $logicRepository;
        $this->fieldsetFactory       = $fieldsetFactory;
        $this->fieldsetRepository    = $fieldsetRepository;
        $this->fieldFactory          = $fieldFactory;
        $this->fieldRepository       = $fieldRepository;
        $this->scopeConfig           = $scopeConfig;
        $this->session               = $sessionFactory->create();
        $this->dateHelper            = $dateHelper;
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
     * @return FormFactory
     */
    public function getFormFactory(): FormFactory
    {
        return $this->formFactory;
    }

    /**
     * @return FormRepositoryInterface
     */
    public function getFormRepository(): FormRepositoryInterface
    {
        return $this->formRepository;
    }

    /**
     * @return LogicFactory
     */
    public function getLogicFactory(): LogicFactory
    {
        return $this->logicFactory;
    }

    /**
     * @return LogicRepositoryInterface
     */
    public function getLogicRepository(): LogicRepositoryInterface
    {
        return $this->logicRepository;
    }

    /**
     * @return FieldsetFactory
     */
    public function getFieldsetFactory(): FieldsetFactory
    {
        return $this->fieldsetFactory;
    }

    /**
     * @return FieldsetRepositoryInterface
     */
    public function getFieldsetRepository(): FieldsetRepositoryInterface
    {
        return $this->fieldsetRepository;
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