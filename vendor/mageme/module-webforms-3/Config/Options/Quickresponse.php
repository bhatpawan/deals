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

namespace MageMe\WebForms\Config\Options;

use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Api\Data\QuickresponseInterface;
use MageMe\WebForms\Api\QuickresponseCategoryRepositoryInterface;
use MageMe\WebForms\Api\QuickresponseRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class Quickresponse implements OptionSourceInterface
{
    /**
     * @var QuickresponseRepositoryInterface
     */
    protected $quickresponseRepository;

    /**
     * @var QuickresponseCategoryRepositoryInterface
     */
    protected $quickresponseCategoryRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * Quickresponse constructor.
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository
     * @param QuickresponseRepositoryInterface $quickresponseRepository
     */
    public function __construct(
        SortOrderBuilder                         $sortOrderBuilder,
        SearchCriteriaBuilder                    $searchCriteriaBuilder,
        QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository,
        QuickresponseRepositoryInterface         $quickresponseRepository
    )
    {
        $this->quickresponseRepository         = $quickresponseRepository;
        $this->quickresponseCategoryRepository = $quickresponseCategoryRepository;
        $this->searchCriteriaBuilder           = $searchCriteriaBuilder;
        $this->sortOrderBuilder                = $sortOrderBuilder;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options        = [];
        $categories     = $this->getCategories();
        $quickresponses = $this->getQuickresponses();
        foreach ($categories as $category) {
            $value     = [];
            $filetered = array_filter($quickresponses, function ($quickresponse) use ($category) {
                return $quickresponse->getQuickresponseCategoryId() == $category->getId();
            });
            if (empty($filetered)) {
                continue;
            }
            foreach ($filetered as $quickresponse) {
                $value[] = [
                    'label' => $quickresponse->getTitle(),
                    'value' => $quickresponse->getId(),
                ];
            }
            $options[] = [
                'label' => $category->getName(),
                'value' => $value,
            ];
        }
        $value = [];
        foreach ($quickresponses as $quickresponse) {
            if ($quickresponse->getQuickresponseCategoryId() == null) {
                $value[] = [
                    'label' => $quickresponse->getTitle(),
                    'value' => $quickresponse->getId(),
                ];
            }
        }
        if (empty($options)) {
            return $value;
        }
        if (!empty($value)) {
            $options = array_merge($value, $options);
        }
        return $options;
    }

    /**
     * Get sorted categories
     *
     * @return \MageMe\WebForms\Model\QuickresponseCategory[]|QuickresponseCategoryInterface[]
     */
    protected function getCategories(): array
    {
        $sortOrder      = $this->sortOrderBuilder
            ->setField(QuickresponseCategoryInterface::POSITION)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addSortOrder($sortOrder)
            ->create();
        return $this->quickresponseCategoryRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Get sorted quickresponses
     *
     * @return \MageMe\WebForms\Model\Quickresponse[]|QuickresponseInterface[]
     */
    protected function getQuickresponses(): array
    {
        $sortOrder      = $this->sortOrderBuilder
            ->setField(QuickresponseInterface::TITLE)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addSortOrder($sortOrder)
            ->create();
        return $this->quickresponseRepository->getList($searchCriteria)->getItems();
    }
}
