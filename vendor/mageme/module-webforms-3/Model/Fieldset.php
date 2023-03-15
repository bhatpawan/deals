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

namespace MageMe\WebForms\Model;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Model\Fieldset\AbstractFieldset;
use Magento\Framework\Exception\LocalizedException;

class Fieldset extends AbstractFieldset
{
    const CLONE_WITH_FIELDS = 'clone_with_fields';

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function duplicate(): FieldsetInterface
    {
        return $this->clone([
            self::NAME => $this->getName() . ' ' . __('(new copy)'),
            self::IS_ACTIVE => false,
            self::CLONE_WITH_FIELDS => true
        ]);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function clone(array $parameters = []): FieldsetInterface
    {
        $duplicateFields = false;

        // clone fieldset
        $fieldset = $this->fieldsetFactory->create()
            ->setData($this->getData())
            ->setId(null)
            ->setCreatedAt($this->dateHelper->currentDate())
            ->setUpdatedAt($this->dateHelper->currentDate());
        foreach ($parameters as $key => $data) {
            switch ($key) {
                case self::FORM_ID:
                {
                    $fieldset->setFormId($data);
                    break;
                }
                case self::NAME:
                {
                    $fieldset->setName($data);
                    break;
                }
                case self::IS_ACTIVE:
                {
                    $fieldset->setIsActive($data);
                    break;
                }
                case self::CLONE_WITH_FIELDS:
                {
                    $duplicateFields = $data;
                    break;
                }
            }
        }
        $this->fieldsetRepository->save($fieldset);

        // duplicate store data
        $stores = $this->storeRepository->getListByEntity($this->getEntityType(), $this->getId())->getItems();

        foreach ($stores as $store) {
            $newStore = $this->storeFactory->create()
                ->setData($store->getData())
                ->setId(null)
                ->setEntityId($fieldset->getId());
            $this->storeRepository->save($newStore);
        }

        // duplicate fields
        if ($duplicateFields) {
            $fields = $this->fieldRepository->getListByFieldsetId($this->getId())->getItems();
            foreach ($fields as $field) {
                $field->clone([
                    FieldInterface::FIELDSET_ID => $fieldset->getId()
                ]);
            }
        }

        return $fieldset;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Fieldset::class);
    }
}
