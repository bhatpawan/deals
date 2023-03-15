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

namespace MageMe\WebForms\Plugin\Store;


use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\Form as FormResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ResourceModel\Store as StoreResource;

/**
 * Plugin which is listening store resource model and on save replace webform url rewrites
 *
 * @see StoreResource
 */
class View
{
    /**
     * @var FormResource
     */
    protected $formResource;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * Update store view plugin constructor
     *
     * @param FormRepositoryInterface $formRepository
     * @param FormResource $formResource
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        FormResource            $formResource
    )
    {
        $this->formResource   = $formResource;
        $this->formRepository = $formRepository;
    }

    /**
     * Replace cms page url rewrites on store view save
     *
     * @param $object
     * @param $result
     * @param $store
     * @return void
     * @throws LocalizedException
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterSave($object, $result, $store): void
    {
        if ($store->isObjectNew() || $store->dataHasChangedFor('group_id')) {
            $forms = $this->formRepository->getList()->getItems();
            foreach ($forms as $form) {
                $this->formResource->manageUrlRewrites($form->getId(), (int)$store->getId(), $form->getUrlKey());
            }
        }
    }

}
