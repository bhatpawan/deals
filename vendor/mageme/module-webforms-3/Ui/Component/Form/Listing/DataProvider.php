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

namespace MageMe\WebForms\Ui\Component\Form\Listing;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Model\ResourceModel\Form as FormResource;
use MageMe\WebForms\Ui\Component\Common\Listing\AbstractDataProvider;
use Magento\Backend\Model\Authorization\RoleLocator;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var bool
     */
    private $isAllAllowed;
    /**
     * @var RoleLocator
     */
    private $roleLocator;
    /**
     * @var FormResource
     */
    private $formResource;

    /**
     * DataProvider constructor.
     * @param AuthorizationInterface $authorization
     * @param FormResource $formResource
     * @param RoleLocator $roleLocator
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        FormResource           $formResource,
        RoleLocator            $roleLocator,
        string                 $name,
        string                 $primaryFieldName,
        string                 $requestFieldName,
        ReportingInterface     $reporting,
        SearchCriteriaBuilder  $searchCriteriaBuilder,
        RequestInterface       $request,
        FilterBuilder          $filterBuilder,
        array                  $meta = [],
        array                  $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request,
            $filterBuilder, $meta, $data);
        $this->roleLocator  = $roleLocator;
        $this->formResource = $formResource;
        $this->isAllAllowed = $authorization->isAllowed('Magento_Backend::all');
    }

    /**
     * @inheritdoc
     */
    public function getSearchResult(): SearchResultInterface
    {
        if (!$this->isAllAllowed) {
            $ids = $this->formResource->getRoleFormsIds($this->roleLocator->getAclRoleId());
            $this->addFilter($this->filterBuilder->setField(FormInterface::ID)->setValue($ids)->setConditionType('in')->create());
        }
        return parent::getSearchResult();
    }
}
