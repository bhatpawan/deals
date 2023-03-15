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

namespace MageMe\WebForms\Plugin\Adminhtml;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use Magento\Framework\Acl\AclResource\Provider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

class TreeBuilder
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * TreeBuilder constructor.
     * @param FormRepositoryInterface $formRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        SortOrderBuilder        $sortOrderBuilder,
        SearchCriteriaBuilder   $searchCriteriaBuilder
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->formRepository        = $formRepository;
    }

    public function afterGetAclResources(Provider $provider, $tree)
    {
        if (count($tree)) {
            foreach ($tree as &$resourceList) {
                foreach ($resourceList['children'] as &$resourceList2) {
                    if ($resourceList2['id'] == 'MageMe_Core::extensions') {
                        foreach ($resourceList2['children'] as &$resourceList3)
                            if ($resourceList3['id'] == 'MageMe_WebForms::webforms') {
                                $resourceList3['children'] = array_merge($resourceList3['children'], $this->getChildren());
                            }
                    }
                }
            }
        }
        return $tree;
    }

    /**
     * @return array
     */
    protected function getChildren()
    {
        $formList       = [];
        $sortOrder      = $this->sortOrderBuilder
            ->setField(FormInterface::NAME)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addSortOrder($sortOrder)
            ->create();
        $forms          = $this->formRepository->getList($searchCriteria)->getItems();
        $i              = 1;
        foreach ($forms as $form) {
            $formList[] = [
                'id' => 'MageMe_WebForms::form' . $form->getId(),
                'title' => $form->getName(),
                'sortOrder' => 100 + $i,
                'disabled' => false,
                'children' => []
            ];
            $i++;
        }
        return $formList;
    }
}
