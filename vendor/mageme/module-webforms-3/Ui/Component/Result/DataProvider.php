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

namespace MageMe\WebForms\Ui\Component\Result;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\ResourceModel\Form as FormResource;
use Magento\Backend\Model\Authorization\RoleLocator;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObject;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
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
     * DataProvider constructor.
     * @param FormResource $formResource
     * @param AuthorizationInterface $authorization
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
        FormResource           $formResource,
        AuthorizationInterface $authorization,
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
        $this->roleLocator   = $roleLocator;
        $this->authorization = $authorization;
        $this->formResource  = $formResource;
    }

    /**
     * @inheritdoc
     */
    public function getSearchResult(): SearchResultInterface
    {
        $nresultId = (int)$this->request->getParam('nresult_id');
        if ($nresultId) {
            $this->addFilter(
                $this->filterBuilder
                    ->setField(ResultInterface::ID)
                    ->setValue($nresultId)
                    ->setConditionType('neq')
                    ->create()
            );
        }
        if (!$this->authorization->isAllowed('Magento_Backend::all')) {
            $ids = $this->formResource->getRoleFormsIds($this->roleLocator->getAclRoleId());
            $this->addFilter($this->filterBuilder->setField(ResultInterface::FORM_ID)->setValue($ids)->setConditionType('in')->create());
        }
        return parent::getSearchResult();
    }

    /**
     * @inheritdoc
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult): array
    {
        $arrItems = [];

        $arrItems['items'] = [];

        /** @var DocumentInterface|DataObject $item */
        foreach ($searchResult->getItems() as $item) {
            $arrItems['items'][] = $item->getData();
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }
}
