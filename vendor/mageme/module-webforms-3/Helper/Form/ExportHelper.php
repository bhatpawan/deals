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

namespace MageMe\WebForms\Helper\Form;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Api\Utility\StoreDataInterface;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\Form;
use MageMe\WebForms\Model\ResourceModel\Field as ResourceField;
use MageMe\WebForms\Model\ResourceModel\Fieldset as FieldsetResource;
use MageMe\WebForms\Model\ResourceModel\Form as FormResource;
use MageMe\WebForms\Model\ResourceModel\Logic as LogicResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ExportHelper
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var FieldsetRepositoryInterface
     */
    private $fieldsetRepository;

    /**
     * ExportFormHelper constructor.
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        FieldsetRepositoryInterface $fieldsetRepository,
        StoreRepositoryInterface    $storeRepository,
        StoreManagerInterface       $storeManager
    )
    {
        $this->storeManager       = $storeManager;
        $this->storeRepository    = $storeRepository;
        $this->fieldsetRepository = $fieldsetRepository;
    }

    /**
     * Export form to JSON
     *
     * @param FormInterface|Form $form
     * @return bool|false|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function convertToJson(FormInterface $form)
    {
        $data = $this->prepareFormData($form);

        foreach ($form->getFieldsToFieldsets(true) as $fieldsetId => $fieldsetDataArray) {
            if (!$fieldsetId) {
                foreach ($fieldsetDataArray['fields'] as $field) {
                    $data['fields'][] = $this->getFieldDataForJSON($field);
                }
            } else {
                $fieldset               = $this->fieldsetRepository->getById($fieldsetId);
                $fieldsetData           = $fieldset->getData();
                $fieldsetData['tmp_id'] = $fieldsetId;
                unset(
                    $fieldsetData[FieldsetInterface::ID],
                    $fieldsetData[FieldsetInterface::FORM_ID],
                    $fieldsetData[FieldsetInterface::CREATED_AT],
                    $fieldsetData[FieldsetInterface::UPDATED_AT]
                );
                $fieldsetData[StoreDataInterface::STORE_DATA] = [];

                /* export store view data */
                $storeDataItems = $this->storeRepository->getListByEntity(FieldsetResource::ENTITY_TYPE, $fieldset->getId())->getItems();
                foreach ($storeDataItems as $storeData) {
                    $storeCode                                                = $this->storeManager->getStore($storeData->getStoreId())->getCode();
                    $fieldsetData[StoreDataInterface::STORE_DATA][$storeCode] = $storeData->getStoreData();
                }

                $fieldsetData['fields'] = [];
                foreach ($fieldsetDataArray['fields'] as $field) {
                    $fieldsetData['fields'][] = $this->getFieldDataForJSON($field);
                }
                $data['fieldsets'][] = $fieldsetData;
            }
        }

        /* export logic */
        $data['logic'] = [];
        $logicItems    = $form->getLogic();
        foreach ($logicItems as $logic) {
            $logicData = $logic->getData();
            unset(
                $logicData[LogicInterface::ID],
                $logicData[LogicInterface::CREATED_AT],
                $logicData[LogicInterface::UPDATED_AT],
                $logicData[LogicInterface::VALUE_SERIALIZED],
                $logicData[LogicInterface::TARGET_SERIALIZED]
            );

            /* export store view data */
            $logicData[StoreDataInterface::STORE_DATA] = [];
            $storeDataItems                            = $this->storeRepository->getListByEntity(LogicResource::ENTITY_TYPE, $logic->getId())->getItems();
            foreach ($storeDataItems as $storeData) {
                $storeCode                                             = $this->storeManager->getStore($storeData->getStoreId())->getCode();
                $logicData[StoreDataInterface::STORE_DATA][$storeCode] = $storeData->getStoreData();
            }

            $data['logic'][] = $logicData;
        }
        return json_encode($data);
    }

    /**
     * @param FormInterface $form
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function prepareFormData(FormInterface $form)
    {
        $data = $form->getData();

        unset(
            $data[FormInterface::ID],
            $data[FormInterface::ADMIN_NOTIFICATION_TEMPLATE_ID],
            $data[FormInterface::CUSTOMER_NOTIFICATION_TEMPLATE_ID],
            $data[FormInterface::EMAIL_REPLY_TEMPLATE_ID],
            $data[FormInterface::APPROVAL_NOTIFICATION_APPROVED_TEMPLATE_ID],
            $data[FormInterface::APPROVAL_NOTIFICATION_COMPLETED_TEMPLATE_ID],
            $data[FormInterface::APPROVAL_NOTIFICATION_NOTAPPROVED_TEMPLATE_ID],
            $data[FormInterface::CREATED_AT],
            $data[FormInterface::UPDATED_AT],
            $data[FormInterface::IS_ACTIVE],
            $data[FormInterface::ACCESS_GROUPS],
            $data[FormInterface::ACCESS_GROUPS_SERIALIZED],
            $data[FormInterface::DASHBOARD_GROUPS],
            $data[FormInterface::DASHBOARD_GROUPS_SERIALIZED],
            $data[FormInterface::IS_CUSTOMER_ACCESS_LIMITED],
            $data[FormInterface::IS_CUSTOMER_DASHBOARD_ENABLED]
        );

        /* export store view data */
        $data[StoreDataInterface::STORE_DATA] = [];
        $storeDataItems                       = $this->storeRepository->getListByEntity(FormResource::ENTITY_TYPE, $form->getId())->getItems();
        foreach ($storeDataItems as $storeData) {
            $storeCode                                        = $this->storeManager->getStore($storeData->getStoreId())->getCode();
            $data[StoreDataInterface::STORE_DATA][$storeCode] = $storeData->getStoreData();
        }

        $data['fields']    = [];
        $data['fieldsets'] = [];

        return $data;
    }

    /**
     * Get fields data for json export
     *
     * @param FieldInterface|AbstractField $field
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getFieldDataForJSON(FieldInterface $field): array
    {
        $data           = $field->getData();
        $data['tmp_id'] = $field->getId();
        unset(
            $data[FieldInterface::ID],
            $data[FieldInterface::FORM_ID],
            $data[FieldInterface::FIELDSET_ID],
            $data[FieldInterface::CREATED_AT],
            $data[FieldInterface::UPDATED_AT],
            $data[FieldInterface::TYPE_ATTRIBUTES_SERIALIZED]
        );

        /* export store view data */
        $data[StoreDataInterface::STORE_DATA] = [];
        $storeDataItems                       = $this->storeRepository->getListByEntity(ResourceField::ENTITY_TYPE, $field->getId())->getItems();
        foreach ($storeDataItems as $storeData) {
            $storeCode                                        = $this->storeManager->getStore($storeData->getStoreId())->getCode();
            $data[StoreDataInterface::STORE_DATA][$storeCode] = $storeData->getStoreData();
        }

        return $data;
    }
}
