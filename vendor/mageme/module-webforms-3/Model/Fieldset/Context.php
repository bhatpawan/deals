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

namespace MageMe\WebForms\Model\Fieldset;


use MageMe\Core\Helper\DateHelper;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Helper\CssHelper;
use MageMe\WebForms\Model\FieldsetFactory;
use MageMe\WebForms\Model\StoreFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Context
{

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FieldsetFactory
     */
    protected $fieldsetFactory;

    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DateHelper
     */
    protected $dateHelper;
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
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * Context constructor.
     * @param CssHelper $cssHelper
     * @param DateHelper $dateHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param FormKey $formKey
     * @param FormRepositoryInterface $formRepository
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param FieldsetFactory $fieldsetFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreFactory $storeFactory
     * @param \Magento\Framework\Model\Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        CssHelper                        $cssHelper,
        DateHelper                       $dateHelper,
        ScopeConfigInterface             $scopeConfig,
        FormKey                          $formKey,
        FormRepositoryInterface          $formRepository,
        FieldRepositoryInterface         $fieldRepository,
        FieldsetRepositoryInterface      $fieldsetRepository,
        FieldsetFactory                  $fieldsetFactory,
        SearchCriteriaBuilder            $searchCriteriaBuilder,
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
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->fieldsetFactory       = $fieldsetFactory;
        $this->fieldsetRepository    = $fieldsetRepository;
        $this->fieldRepository       = $fieldRepository;
        $this->formRepository        = $formRepository;
        $this->formKey               = $formKey;
        $this->scopeConfig           = $scopeConfig;
        $this->dateHelper            = $dateHelper;
        $this->cssHelper             = $cssHelper;
    }

    /**
     * @return SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder(): SearchCriteriaBuilder
    {
        return $this->searchCriteriaBuilder;
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
     * @return FormKey
     */
    public function getFormKey(): FormKey
    {
        return $this->formKey;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }

    /**
     * @return DateHelper
     */
    public function getDateHelper(): DateHelper
    {
        return $this->dateHelper;
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

    /**
     * @return \Magento\Framework\Model\Context
     */
    public function getContext(): \Magento\Framework\Model\Context
    {
        return $this->context;
    }


}