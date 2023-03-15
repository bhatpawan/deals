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


use MageMe\WebForms\Api\QuickresponseCategoryRepositoryInterface;
use Magento\Framework\Data\OptionSourceInterface;

class QuickresponseCategory implements OptionSourceInterface
{
    /**
     * @var QuickresponseCategoryRepositoryInterface
     */
    protected $quickresponseCategoryRepository;

    /**
     * Quickresponse constructor.
     * @param QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository
     */
    public function __construct(
        QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository
    )
    {
        $this->quickresponseCategoryRepository = $quickresponseCategoryRepository;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options    = [];
        $categories = $this->quickresponseCategoryRepository->getList()->getItems();
        foreach ($categories as $category) {
            $options[] = [
                'label' => $category->getName(),
                'value' => $category->getId(),
            ];
        }
        return $options;
    }
}