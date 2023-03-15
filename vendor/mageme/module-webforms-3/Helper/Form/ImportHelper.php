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


use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Api\Utility\StoreDataInterface;
use MageMe\WebForms\Config\Exception\UrlRewriteAlreadyExistsException;
use MageMe\WebForms\Config\Options\Field\DisplayOption;
use MageMe\WebForms\Helper\ConvertVersion\FieldConverter;
use MageMe\WebForms\Helper\ConvertVersion\FieldsetConverter;
use MageMe\WebForms\Helper\ConvertVersion\FormConverter;
use MageMe\WebForms\Helper\ConvertVersion\LogicConverter;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Model\FieldsetFactory;
use MageMe\WebForms\Model\FormFactory;
use MageMe\WebForms\Model\LogicFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ImportHelper
{
    /**
     * Transitional matrix for form elements
     *
     * @var array
     */
    protected $elementMatrix = [];

    /**
     * Errors while import process
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Warnings while import process
     *
     * @var array
     */
    protected $warnings = [];

    /**
     * @var FieldInterface[]
     */
    protected $postProcessedFields = [];

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var FormConverter
     */
    private $formConverter;

    /**
     * @var FormRepositoryInterface
     */
    private $formRepository;

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @var FieldConverter
     */
    private $fieldConverter;

    /**
     * @var FieldRepositoryInterface
     */
    private $fieldRepository;

    /**
     * @var FieldsetFactory
     */
    private $fieldsetFactory;

    /**
     * @var FieldsetConverter
     */
    private $fieldsetConverter;

    /**
     * @var FieldsetRepositoryInterface
     */
    private $fieldsetRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LogicFactory
     */
    private $logicFactory;

    /**
     * @var LogicConverter
     */
    private $logicConverter;

    /**
     * @var LogicRepositoryInterface
     */
    private $logicRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    private $isV2 = false;

    /**
     * ImportFormHelper constructor.
     * @param LogicRepositoryInterface $logicRepository
     * @param LogicConverter $logicConverter
     * @param LogicFactory $logicFactory
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param FieldsetConverter $fieldsetConverter
     * @param FieldsetFactory $fieldsetFactory
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldConverter $fieldConverter
     * @param FieldFactory $fieldFactory
     * @param FormRepositoryInterface $formRepository
     * @param FormConverter $formConverter
     * @param FormFactory $formFactory
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        LogicRepositoryInterface    $logicRepository,
        LogicConverter              $logicConverter,
        LogicFactory                $logicFactory,
        FieldsetRepositoryInterface $fieldsetRepository,
        FieldsetConverter           $fieldsetConverter,
        FieldsetFactory             $fieldsetFactory,
        FieldRepositoryInterface    $fieldRepository,
        FieldConverter              $fieldConverter,
        FieldFactory                $fieldFactory,
        FormRepositoryInterface     $formRepository,
        FormConverter               $formConverter,
        FormFactory                 $formFactory,
        StoreManagerInterface       $storeManager,
        ManagerInterface            $eventManager
    )
    {
        $this->storeManager       = $storeManager;
        $this->formFactory        = $formFactory;
        $this->formConverter      = $formConverter;
        $this->formRepository     = $formRepository;
        $this->fieldFactory       = $fieldFactory;
        $this->fieldConverter     = $fieldConverter;
        $this->fieldRepository    = $fieldRepository;
        $this->fieldsetFactory    = $fieldsetFactory;
        $this->fieldsetConverter  = $fieldsetConverter;
        $this->fieldsetRepository = $fieldsetRepository;
        $this->logicFactory       = $logicFactory;
        $this->logicConverter     = $logicConverter;
        $this->logicRepository    = $logicRepository;
        $this->eventManager       = $eventManager;
    }

    /**
     * Import errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Import warnings
     *
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Import form from json data
     *
     * @param $jsonData
     * @return FormInterface
     */
    public function import($jsonData): ?FormInterface
    {
        $this->errors              = [];
        $this->warnings            = [];
        $this->elementMatrix       = [];
        $this->postProcessedFields = [];

        $data = $this->getDataFromJSON($jsonData);

        if (!empty($this->errors)) {
            return null;
        }

        $this->isV2 = $this->isV2Form($data);

        /* import form */
        try {
            $form = $this->importForm($data);
        } catch (Exception $e) {
            $this->errors[] = __('Could not import form %1: %2', $data[FormInterface::NAME], $e->getMessage());
            return null;
        }

        /* import fields */
        foreach ($data['fields'] as $fieldData) {
            try {
                $this->importField($fieldData, $form->getId());
            } catch (Exception $e) {
                $this->errors[] = __('Could not import field %1: %2', $fieldData[FieldInterface::NAME], $e->getMessage());
                return null;
            }
        }

        /* import fieldsets */
        foreach ($data['fieldsets'] as $fieldsetData) {
            try {
                $this->importFieldset($fieldsetData, $form->getId());
            } catch (Exception $e) {
                $this->errors[] = __('Could not import fieldset %1: %2', $fieldsetData[FieldsetInterface::NAME], $e->getMessage());
                return null;
            }
        }

        /* import logic rules */
        foreach ($data['logic'] as $logicData) {
            try {
                $this->importLogic($logicData);
            } catch (Exception $e) {
                $this->errors[] = __('Could not import logic: %1', $e->getMessage());
                return null;
            }
        }

        /* fields post processing */
        foreach ($this->postProcessedFields as $field) {
            try {
                $this->fieldPostProcessing($field);
            } catch (Exception $e) {
                $this->warnings[] = __('Post processing error: %1', $e->getMessage());
                return null;
            }
        }

        $this->eventManager->dispatch('webforms_form_import', ['form' => $form, 'elementMatrix' => $this->elementMatrix]);

        return $form;
    }

    /**
     * Get form data from JSON
     *
     * @param $jsonData
     * @return array
     * @noinspection DuplicatedCode
     */
    public function getDataFromJSON($jsonData): array
    {
        $data = json_decode($jsonData, true);

        if (!$data) {
            $this->errors[] = __('Incorrect JSON data');
            return [];
        }
        if (empty($data[FormInterface::NAME])) {
            $this->errors[] = __('Missing form name');
        }

        if (empty($data['fields'])) {
            $data['fields'] = [];
        }
        if (empty($data['fieldsets'])) {
            $data['fieldsets'] = [];
        }
        if (empty($data['logic'])) {
            $data['logic'] = [];
        }
        if (empty($data[StoreDataInterface::STORE_DATA])) {
            $data[StoreDataInterface::STORE_DATA] = [];
        }
        $this->checkStoreData($data[StoreDataInterface::STORE_DATA]);

        /* check fields */
        foreach ($data['fields'] as $field) {
            if (empty($field[FieldInterface::NAME])) {
                $this->errors[] = __('Missing field name');
            }
            if (empty($field[FieldInterface::TYPE])) {
                $this->errors[] = __('Field type not defined');
            }
            if (empty($field[StoreDataInterface::STORE_DATA])) {
                $field[StoreDataInterface::STORE_DATA] = [];
            }
            $this->checkStoreData($field[StoreDataInterface::STORE_DATA]);
        }

        /* check fieldsets */
        foreach ($data['fieldsets'] as $fieldset) {
            if (empty($fieldset['name'])) {
                $errors[] = __('Fieldset found and missing name');
            }
            if (empty($fieldset['fields'])) {
                $fieldset['fields'] = [];
            }
            if (empty($fieldset[StoreDataInterface::STORE_DATA])) {
                $fieldset[StoreDataInterface::STORE_DATA] = [];
            }
            $this->checkStoreData($fieldset[StoreDataInterface::STORE_DATA]);

            foreach ($fieldset['fields'] as $field) {
                if (empty($field[FieldInterface::NAME])) {
                    $this->errors[] = __('Missing field name');
                }
                if (empty($field[FieldInterface::TYPE])) {
                    $this->errors[] = __('Field type not defined');
                }
                if (empty($field[StoreDataInterface::STORE_DATA])) {
                    $field[StoreDataInterface::STORE_DATA] = [];
                }
                $this->checkStoreData($field[StoreDataInterface::STORE_DATA]);
            }
        }

        /* check logic */
        foreach ($data['logic'] as $logic) {
            if (empty($logic[LogicInterface::FIELD_ID])) {
                $this->warnings[] = __('Logic rule is missing trigger field');
            }
            if (empty($logic[LogicInterface::VALUE])) {
                $this->warnings[] = __('Logic rule is missing value');
            }
            if (empty($logic[LogicInterface::TARGET])) {
                $this->warnings[] = __('Logic rule is missing target');
            }
            if (empty($logic[LogicInterface::ACTION])) {
                $this->warnings[] = __('Logic rule is missing action');
            }
            if (empty($logic[StoreDataInterface::STORE_DATA])) {
                $logic[StoreDataInterface::STORE_DATA] = [];
            }
            $this->checkStoreData($logic[StoreDataInterface::STORE_DATA]);
        }

        return $data;
    }

    /**
     * Check store view exists for store data
     *
     * @param array $data
     */
    protected function checkStoreData(array $data)
    {
        foreach ($data as $storeCode => $storeData) {
            try {
                $this->storeManager->getStore($storeCode);
            } catch (NoSuchEntityException $e) {
                $text = __('Store view contained within data not found: %1', $storeCode);
                if (!in_array($text, $this->warnings)) {
                    $this->warnings[] = $text;
                }
            }
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function isV2Form(array $data): bool
    {
        return array_key_exists(FormConverter::MENU, $data);
    }

    /**
     * Import form
     *
     * @param array $data
     * @return FormInterface
     * @throws CouldNotSaveException
     */
    protected function importForm(array $data): FormInterface
    {
        $formData = $this->isV2 ? $this->formConverter->convertV2Data($data) : $data;
        unset($formData[FormInterface::ID]);
        $form = $this->formFactory->create();
        $form->setData($formData);
        $form->setIsActive(true);
        try {
            $this->formRepository->save($form);
        } catch (UrlRewriteAlreadyExistsException $exception) {
            $this->warnings[] = __('SEO data cleaned: %1.', $exception->getMessage());
            unset(
                $data[FormInterface::URL_KEY]
            );
            return $this->importForm($data);
        }

        /* import store data */
        foreach ($data[StoreDataInterface::STORE_DATA] as $storeCode => $storeData) {
            try {
                $store = $this->storeManager->getStore($storeCode);
                if ($this->isV2) {
                    $storeData = $this->formConverter->convertV2StoreData($storeData);
                }
                $form->saveStoreData($store->getId(), $storeData);
            } catch (NoSuchEntityException $e) {
                continue;
            } catch (Exception $e) {
                $this->warnings[] = __('Could not import store data for form %1: Store view: %2, Error: %3',
                    $form->getName(),
                    $storeCode,
                    $e->getMessage()
                );
                continue;
            }
        }

        return $form;
    }

    /**
     * Import field
     *
     * @param array $data
     * @param int $formId
     * @param int|null $fieldsetId
     * @return FieldInterface
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    protected function importField(array $data, int $formId, ?int $fieldsetId = null): FieldInterface
    {
        $fieldData = $this->isV2 ? $this->fieldConverter->convertV2Data($data) : $data;
        unset($fieldData[FieldInterface::ID]);
        $fieldData[FieldInterface::FORM_ID]     = $formId;
        $fieldData[FieldInterface::FIELDSET_ID] = $fieldsetId;

        // Fix display in result
        if (isset($fieldData[FieldInterface::DISPLAY_IN_RESULT])) {
            $fieldData[FieldInterface::DISPLAY_IN_RESULT] = in_array($fieldData[FieldInterface::DISPLAY_IN_RESULT], [
                DisplayOption::OPTION_ON,
                DisplayOption::OPTION_OFF,
                DisplayOption::OPTION_VALUE
            ]) ? $fieldData[FieldInterface::DISPLAY_IN_RESULT] : DisplayOption::OPTION_ON;
        }

        $field = $this->fieldFactory->create($fieldData[FieldInterface::TYPE]);
        $field->setData($fieldData);
        $this->fieldRepository->save($field);

        /* import store data */
        foreach ($data[StoreDataInterface::STORE_DATA] as $storeCode => $storeData) {
            try {
                $store = $this->storeManager->getStore($storeCode);
                if ($this->isV2) {
                    $storeData = $this->fieldConverter->convertV2StoreData($storeData, $field->getType());
                }
                $field->saveStoreData($store->getId(), $storeData);
            } catch (NoSuchEntityException $e) {
                continue;
            } catch (Exception $e) {
                $this->warnings[] = __('Could not import store data for field %1: Store view: %2, Error: %3',
                    $field->getName(),
                    $storeCode,
                    $e->getMessage()
                );
                continue;
            }
        }

        /* add to element matrix */
        $this->elementMatrix['field_' . $data['tmp_id']] = $field->getId();

        /* check field for post processing  */
        $this->checkPostProcessedField($field);

        return $field;
    }

    /**
     * @param FieldInterface $field
     */
    protected function checkPostProcessedField(FieldInterface $field)
    {
        if ($field->isImportPostProcess()) {
            $this->postProcessedFields[] = $field;
        }
    }

    /**
     * Import fieldset
     *
     * @param array $data
     * @param int $formId
     * @return FieldsetInterface
     * @throws CouldNotSaveException
     * @throws Exception
     */
    protected function importFieldset(array $data, int $formId): FieldsetInterface
    {
        $fieldsetData = $this->isV2 ? $this->fieldsetConverter->convertV2Data($data) : $data;
        unset($fieldsetData[FieldsetInterface::ID]);
        $fieldsetData[FieldsetInterface::FORM_ID] = $formId;
        $fieldset                                 = $this->fieldsetFactory->create();
        $fieldset->setData($fieldsetData);
        $this->fieldsetRepository->save($fieldset);

        /* import store data */
        foreach ($data[StoreDataInterface::STORE_DATA] as $storeCode => $storeData) {
            try {
                $store = $this->storeManager->getStore($storeCode);
                if ($this->isV2) {
                    $storeData = $this->fieldsetConverter->convertV2StoreData($storeData);
                }
                $fieldset->saveStoreData($store->getId(), $storeData);
            } catch (NoSuchEntityException $e) {
                continue;
            } catch (Exception $e) {
                $this->warnings[] = __('Could not import store data for fieldset %1: Store view: %2, Error: %3',
                    $fieldset->getName(),
                    $storeCode,
                    $e->getMessage()
                );
                continue;
            }
        }

        /* import fields */
        foreach ($data['fields'] as $fieldData) {
            try {
                $this->importField($fieldData, $formId, $fieldset->getId());
            } catch (Exception $e) {
                throw new Exception(__('Could not import field %1: %2', $fieldData[FieldInterface::NAME], $e->getMessage()));
            }
        }

        /* add to logic matrix */
        $this->elementMatrix['fieldset_' . $data['tmp_id']] = $fieldset->getId();

        return $fieldset;
    }

    /**
     * Import logic rule
     *
     * @param array $data
     * @return LogicInterface
     * @throws CouldNotSaveException
     */
    protected function importLogic(array $data): LogicInterface
    {
        $logicData = $this->isV2 ? $this->logicConverter->convertV2Data($data) : $data;
        unset($logicData[LogicInterface::ID]);
        $logicData[LogicInterface::FIELD_ID] = $this->elementMatrix['field_' . $logicData[LogicInterface::FIELD_ID]];
        $logicData[LogicInterface::TARGET]   = $this->updateLogicTargetsWithMatrix($logicData[LogicInterface::TARGET]);
        $logic                               = $this->logicFactory->create();
        $logic->setData($logicData);
        $this->logicRepository->save($logic);

        /* import store data */
        foreach ($data[StoreDataInterface::STORE_DATA] as $storeCode => $storeData) {
            try {
                $store = $this->storeManager->getStore($storeCode);
                if ($this->isV2) {
                    $storeData = $this->logicConverter->convertV2StoreData($storeData);
                }
                if (isset($storeData[LogicInterface::TARGET])) {
                    $storeData[LogicInterface::TARGET] = $this->updateLogicTargetsWithMatrix($storeData[LogicInterface::TARGET]);
                }
                $logic->saveStoreData($store->getId(), $storeData);
            } catch (NoSuchEntityException $e) {
                continue;
            } catch (Exception $e) {
                $this->warnings[] = __('Could not import store data for logic %1: Store view: %2, Error: %3',
                    $logic->getId(),
                    $storeCode,
                    $e->getMessage()
                );
                continue;
            }
        }

        return $logic;
    }

    /**
     * Return targets with new ids
     *
     * @param mixed $targets
     * @return array
     */
    protected function updateLogicTargetsWithMatrix($targets): array
    {
        if (!is_array($targets)) {
            return [];
        }
        $updated = [];
        foreach ($targets as $target) {
            $prefix = strstr((string)$target, 'fieldset_') ? 'fieldset_' : 'field_';
            if (!empty($this->elementMatrix[$target])) {
                $updated[] = $prefix . $this->elementMatrix[$target];
            }
            if ($target == 'submit') {
                $updated[] = 'submit';
            }
        }
        return $updated;
    }

    /**
     *  Process field after get all new values
     * @param FieldInterface $field
     */
    protected function fieldPostProcessing(FieldInterface $field)
    {
        $field->importPostProcess($this->elementMatrix);
    }
}
