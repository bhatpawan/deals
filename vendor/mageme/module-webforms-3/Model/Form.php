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
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\StoreInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Api\Utility\Field\HiddenInterface;
use MageMe\WebForms\Config\Options\Logic\Action;
use MageMe\WebForms\Config\Options\Result\Permission;
use MageMe\WebForms\Config\Options\Status;
use MageMe\WebForms\Helper\CaptchaHelper as Captcha;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Model\Form\AbstractForm;
use MageMe\WebForms\Model\ResourceModel\Form as FormResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;


/**
 * Class Form
 * @package MageMe\WebForms\Model
 */
class Form extends AbstractForm
{
    /**
     * @var array
     */
    protected $_fields_to_fieldsets = [];

    /**
     * @var array
     */
    protected $_hidden = [];

    /**
     * @var array
     */
    protected $_logic_target = [];

    /**
     * Empty model for get target visibility
     *
     * @var Logic|null
     */
    protected $emptyLogicModel = null;

    /**
     * @var Captcha
     */
    protected $captcha;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var FormResource
     */
    protected $formResource;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * Form constructor.
     * @param ResultRepositoryInterface $resultRepository
     * @param FormResource $formResource
     * @param AccessHelper $accessHelper
     * @param Captcha $captcha
     * @param Form\Context $context
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        FormResource              $formResource,
        AccessHelper              $accessHelper,
        Captcha                   $captcha,
        Form\Context              $context
    )
    {
        parent::__construct($context);
        $this->captcha          = $captcha;
        $this->accessHelper     = $accessHelper;
        $this->formResource     = $formResource;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function duplicate(): FormInterface
    {
        return $this->clone([
            self::NAME => $this->getName() . ' ' . __('(new copy)'),
            self::IS_ACTIVE => false
        ]);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function clone(array $parameters = []): FormInterface
    {
        // duplicate form
        $form = $this->formFactory->create()
            ->setData($this->getData())
            ->setId(null)
            ->setUrlKey(null)
            ->setCreatedAt($this->dateHelper->currentDate())
            ->setUpdatedAt($this->dateHelper->currentDate());
        foreach ($parameters as $key => $data) {
            switch ($key) {
                case self::NAME:
                {
                    $form->setName($data);
                    break;
                }
                case self::IS_ACTIVE:
                {
                    $form->setIsActive($data);
                    break;
                }
            }
        }
        $this->formRepository->save($form);

        // duplicate store data
        $stores = $this->storeRepository->getListByEntity($this->getEntityType(), $this->getId())->getItems();

        foreach ($stores as $store) {
            $newStore = $this->storeFactory->create()
                ->setData($store->getData())
                ->setId(null)
                ->setEntityId($form->getId());
            $this->storeRepository->save($newStore);
        }

        $fieldsetUpdate      = [];
        $fieldUpdate         = [];
        $postProcessedFields = [];

        // transitional element matrix
        $elementMatrix = [];

        // duplicate fieldsets and fields
        $fieldsToFieldsets = $this->getFieldsToFieldsets(true);
        foreach ($fieldsToFieldsets as $fieldsetId => $fieldset) {
            if ($fieldsetId) {
                $newFieldset                              = $this->fieldsetRepository->getById($fieldsetId)->clone([
                    FieldsetInterface::FORM_ID => $form->getId()
                ]);
                $newFieldsetId                            = $newFieldset->getId();
                $fieldsetUpdate[$fieldsetId]              = $newFieldsetId;
                $elementMatrix['fieldset_' . $fieldsetId] = $newFieldset->getId();
            } else {
                $newFieldsetId = 0;
            }

            /** @var FieldInterface $field */
            foreach ($fieldset['fields'] as $field) {
                $newField = $field->clone([
                    FieldInterface::FIELDSET_ID => $newFieldsetId,
                    FieldInterface::FORM_ID => $form->getId()
                ]);

                $fieldUpdate[$field->getId()]              = $newField->getId();
                $elementMatrix['field_' . $field->getId()] = $newField->getId();
                if ($newField->isImportPostProcess())
                    $postProcessedFields[] = $newField;
            }
        }

        // duplicate logic
        $logicRules = $this->getLogic();

        /** @var Logic $logic */
        foreach ($logicRules as $logic) {
            $newFieldId = $fieldUpdate[$logic->getFieldId()];
            $newTarget  = [];
            foreach ($logic->getTarget() as $target) {
                foreach ($fieldsetUpdate as $oldId => $newId) {
                    if ($target == 'fieldset_' . $oldId) {
                        $newTarget[] = 'fieldset_' . $newId;
                    }
                }
                foreach ($fieldUpdate as $oldId => $newId) {
                    if ($target == 'field_' . $oldId) {
                        $newTarget[] = 'field_' . $newId;
                    }
                }
            }
            $newLogic = $this->logicFactory->create()
                ->setData($logic->getData())
                ->setId(null)
                ->setCreatedAt($this->dateHelper->currentDate())
                ->setUpdatedAt($this->dateHelper->currentDate())
                ->setFieldId($newFieldId)
                ->setTarget($newTarget);
            $this->logicRepository->save($newLogic);

            // duplicate store data
            /** @var StoreInterface[] $stores */
            $stores = $this->storeRepository->getListByEntity($logic->getEntityType(), $logic->getId())->getItems();

            foreach ($stores as $store) {
                $newTarget = [];
                $storeData = $store->getStoreData();
                if (!empty($storeData['target'])) {
                    foreach ($storeData['target'] as $target) {
                        foreach ($fieldsetUpdate as $oldId => $newId) {
                            if ($target == 'fieldset_' . $oldId) {
                                $newTarget[] = 'fieldset_' . $newId;
                            }
                        }
                        foreach ($fieldUpdate as $oldId => $newId) {
                            if ($target == 'field_' . $oldId) {
                                $newTarget[] = 'field_' . $newId;
                            }
                        }
                    }
                }
                $store->setData('target', $newTarget);
                $newStore = $this->storeFactory->create()
                    ->setData($store->getData())
                    ->setId(null)
                    ->setEntityId($newLogic->getId());
                $this->storeRepository->save($newStore);
            }
        }

        foreach ($postProcessedFields as $field) {
            $field->importPostProcess($elementMatrix);
        }

        $this->_eventManager->dispatch('webforms_form_duplicate', ['form' => $form, 'elementMatrix' => $elementMatrix]);

        return $form;
    }

    /**
     * Get form structure
     *
     * @param bool $all
     * @param Result|null $result
     * @param array $options
     * @return array
     * @throws LocalizedException
     */
    public function getFieldsToFieldsets(bool $all = false, Result $result = null, array $options = []): array
    {
        // options
        $includeHidden = $options['include_hidden'] ?? false;

        $fields     = $this->getFields($all);
        $fieldsets  = $this->getFieldsets($all);
        $logicRules = $this->getLogic(false);

        $defaultData       = [];
        $requiredFields    = [];
        $hidden            = [];
        $fieldsToFieldsets = [];

        // prepare fields
        foreach ($fields as $field) {

            // set default data
            if ($field instanceof Type\AbstractOption && $field->getIsLogicType()) {
                $options        = $field->getOptionsArray();
                $checkedOptions = [];
                foreach ($options as $o) {
                    if ($o['checked']) {
                        $checkedOptions[] = $o['value'];
                    }
                }
                if (count($checkedOptions)) {
                    $defaultData[$field->getId()] = $checkedOptions;
                }
            }

            // set default visibility
            $field->setData('logic_visibility', Logic::VISIBILITY_VISIBLE);

            // make zero fieldset
            if (!$field->getFieldsetId()) {
                if ($all || $field->getIsActive()) {
                    if (!$all && ($field instanceof HiddenInterface)) {
                        $hidden[] = $field;
                        if ($includeHidden) {
                            $fieldsToFieldsets[0]['fields'][] = $field;
                        }
                    } else {
                        if ($field->getIsRequired()) {
                            $requiredFields[] = 'field_' . $field->getId();
                        }
                        $fieldsToFieldsets[0]['fields'][] = $field;
                    }
                }
            }
        }

        // prepare fieldsets
        foreach ($fieldsets as $fieldset) {
            foreach ($fields as $field) {
                if ($field->getFieldsetId() == $fieldset->getId()) {
                    if ($all || $field->getIsActive()) {
                        if (!$all && ($field instanceof HiddenInterface)) {
                            $hidden[] = $field;
                            if ($includeHidden) {
                                $fieldsToFieldsets[$fieldset->getId()]['fields'][] = $field;
                            }
                        } else {
                            if ($field->getIsRequired()) {
                                $requiredFields[] = 'field_' . $field->getId();
                            }
                            $fieldsToFieldsets[$fieldset->getId()]['fields'][] = $field;
                        }
                    }
                }
            }
            if ($all || !empty($fieldsToFieldsets[$fieldset->getId()]['fields'])) {
                $fieldsToFieldsets[$fieldset->getId()][FieldsetInterface::NAME]                        = $fieldset->getName();
                $fieldsToFieldsets[$fieldset->getId()][FieldsetInterface::IS_NAME_DISPLAYED_IN_RESULT] = $fieldset->getIsNameDisplayedInResult();
                $fieldsToFieldsets[$fieldset->getId()][FieldsetInterface::CSS_CLASS]                   = $fieldset->getCssClass() . " " . $fieldset->getResponsiveCss();
                $fieldsToFieldsets[$fieldset->getId()][FieldsetInterface::CSS_STYLE]                   = $fieldset->getCssStyle();
                if (empty($fieldsToFieldsets[$fieldset->getId()]['fields'])) {
                    $fieldsToFieldsets[$fieldset->getId()]['fields'] = [];
                }
            }
        }

        $logicTarget   = [];
        $hiddenTargets = [];
        $target        = [];

        // set logic attributes
        foreach ($fieldsToFieldsets as $fieldsetId => $fieldset) {
            $fieldsToFieldsets[$fieldsetId]['logic_visibility'] = Logic::VISIBILITY_VISIBLE;
            foreach ($logicRules as $logic) {
                if ($logic->getAction() == Action::ACTION_SHOW && $logic->getIsActive()) {

                    // check fieldset visibility
                    if (in_array('fieldset_' . $fieldsetId, $logic->getTarget())) {
                        $fieldsToFieldsets[$fieldsetId]['logic_visibility'] = Logic::VISIBILITY_HIDDEN;
                    }

                    // check fields visibility
                    foreach ($fieldset['fields'] as $field) {
                        if (in_array('field_' . $field->getId(), $logic->getTarget())) {
                            $field->setData('logic_visibility', Logic::VISIBILITY_HIDDEN);
                        }
                    }
                }
            }
        }

        $field_map = [];

        // create field map
        foreach ($fieldsToFieldsets as $fieldsetId => $fieldset) {
            foreach ($fieldset['fields'] as $field) {
                $field_map['fieldset_' . $fieldsetId][] = $field->getId();
            }
        }

        // get values from result
        if ($result && $result->getId()) {
            $result->addFieldArray();
            $defaultData = $result->getData('field');
        }

        // check field values and assign visibility
        foreach ($fieldsToFieldsets as $fieldsetId => $fieldset) {
            $target['id']                                       = 'fieldset_' . $fieldsetId;
            $target['logic_visibility']                         = $fieldset['logic_visibility'];
            $visibility                                         = $this->getLogicTargetVisibility($defaultData, $logicRules, $field_map, $target);
            $fieldsToFieldsets[$fieldsetId]['logic_visibility'] = $visibility ?
                Logic::VISIBILITY_VISIBLE :
                Logic::VISIBILITY_HIDDEN;
            if (!$visibility) {
                $hiddenTargets[] = "fieldset_" . $fieldsetId;
            }

            // check fields visibility
            foreach ($fieldset['fields'] as $field) {
                $target['id']               = 'field_' . $field->getId();
                $target['logic_visibility'] = $field->getData('logic_visibility');
                $visibility                 = $this->getLogicTargetVisibility($defaultData, $logicRules, $field_map, $target);
                $field->setData('logic_visibility', $visibility ?
                    Logic::VISIBILITY_VISIBLE :
                    Logic::VISIBILITY_HIDDEN);
                if (!$visibility) {
                    $hiddenTargets[] = "field_" . $field->getId();
                }
            }

        }

        // check submit button visibility
        $target['id']               = 'submit';
        $target['logic_visibility'] = true;
        $visibility                 = $this->getLogicTargetVisibility($defaultData, $logicRules, $field_map, $target);
        if (!$visibility) {
            $hiddenTargets[] = $target['id'];
        }

        // set logic target
        foreach ($logicRules as $logic) {
            if ($logic->getIsActive()) {
                foreach ($logic->getTarget() as $target) {
                    $required = false;
                    if (in_array($target, $requiredFields)) {
                        $required = true;
                    }
                    if (!in_array($target, $logicTarget)) {
                        $logicTarget[] = [
                            "id" => $target,
                            "logic_visibility" =>
                                in_array($target, $hiddenTargets) ?
                                    Logic::VISIBILITY_HIDDEN :
                                    Logic::VISIBILITY_VISIBLE,
                            "required" => $required
                        ];
                    }
                }
            }
        }

        $this->_setLogicTarget($logicTarget);
        $this->_setFieldsToFieldsets($fieldsToFieldsets);
        $this->_setHidden($hidden);

        $this->_fields_to_fieldsets = $fieldsToFieldsets;
        return $fieldsToFieldsets;
    }

    /**
     * Get form structure
     *
     * @return array
     */
    public function _getFieldsToFieldsets(): array
    {
        return $this->_fields_to_fieldsets;
    }

    /**
     * Set form structure
     *
     * @param $fieldsToFieldsets
     * @return $this
     */
    public function _setFieldsToFieldsets($fieldsToFieldsets): Form
    {
        $this->_fields_to_fieldsets = $fieldsToFieldsets;
        return $this;
    }

    /**
     * Get form fields
     *
     * @param bool $all
     * @return FieldInterface[]|AbstractField[]
     */
    public function getFields(bool $all = false): array
    {
        $sortOrder      = $this->sortOrderBuilder
            ->setField(FieldInterface::POSITION)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldInterface::FORM_ID, $this->getId());
        if (!$all) {
            $searchCriteria->addFilter(FieldInterface::IS_ACTIVE, Status::STATUS_ENABLED);
        }
        $searchCriteria = $searchCriteria
            ->addSortOrder($sortOrder)
            ->create();
        return $this->fieldRepository->getList($searchCriteria, $this->getStoreId())->getItems();
    }

    /**
     * Get form fieldsets
     *
     * @param bool $all
     * @return FieldsetInterface[]
     */
    public function getFieldsets(bool $all = false): array
    {
        $sortOrder      = $this->sortOrderBuilder
            ->setField(FieldsetInterface::POSITION)
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldsetInterface::FORM_ID, $this->getId());
        if (!$all) {
            $searchCriteria->addFilter(FieldsetInterface::IS_ACTIVE, Status::STATUS_ENABLED);
        }
        $searchCriteria = $searchCriteria
            ->addSortOrder($sortOrder)
            ->create();
        return $this->fieldsetRepository->getList($searchCriteria, $this->getStoreId())->getItems();
    }

    /**
     * Get logic target visibility
     *
     * @param mixed $data
     * @param LogicInterface[] $logicRules
     * @param array $fieldMap
     * @param array $target
     * @return bool
     */
    protected function getLogicTargetVisibility($data, array $logicRules, array $fieldMap, array $target): bool
    {
        if (!$this->emptyLogicModel) {
            $this->emptyLogicModel = $this->logicFactory->create();
        }
        return $this->emptyLogicModel->getTargetVisibility($data, $logicRules, $fieldMap, $target);
    }

    /**
     * Check customer form access
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canAccess(): bool
    {
        if ($this->accessHelper->isAllowed((int)$this->getId())) {
            return true;
        }

        if ($this->getIsCustomerAccessLimited()) {
            $groupId = $this->session->getCustomerGroupId();

            if (in_array($groupId, $this->getAccessGroups())) {
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Get logic target visibility
     *
     * @param array $target
     * @param LogicInterface[] $logicRules
     * @param mixed $data
     * @return bool
     */
    public function getTargetVisibility(array $target, array $logicRules, $data): bool
    {
        $field_map = [];
        foreach ($this->_fields_to_fieldsets as $fieldsetId => $fieldset) {
            foreach ($fieldset["fields"] as $field) {
                $field_map['fieldset_' . $fieldsetId][] = $field->getId();
            }
        }
        if (empty($field_map['fieldset_0'])) {
            $field_map['fieldset_0'] = [];
        }
        $field_map['fieldset_0'][] = 'submit';
        return $this->getLogicTargetVisibility($data, $logicRules, $field_map, $target);
    }

    /**
     * Check if captcha should be used
     *
     * @return bool
     */
    public function useCaptcha(): bool
    {
        $useCaptcha = true;
        if ($this->getCaptchaMode() != 'default') {
            $captcha_mode = $this->getCaptchaMode();
        } else {
            $captcha_mode = $this->scopeConfig->getValue('webforms/captcha/mode', ScopeInterface::SCOPE_STORE);
        }
        if ($captcha_mode == "off") {
            $useCaptcha = false;
        }
        if ($this->session->getCustomerId() && $captcha_mode == "auto") {
            $useCaptcha = false;
        }
        if ($this->getData('disable_captcha')) {
            $useCaptcha = false;
        }
        if (!$this->getCaptcha()) {
            $useCaptcha = false;
        }
        return $useCaptcha;
    }

    /**
     * Get captcha object
     *
     * @return bool|Captcha
     */
    public function getCaptcha()
    {
        if ($this->captcha->getPublicKey() && $this->captcha->getPrivateKey()) {
            return $this->captcha;
        }
        return false;
    }

    /**
     * Get logic target in appropriate format
     *
     * @param string|bool $uid
     * @return array
     */
    public function _getLogicTarget($uid = false): array
    {
        $logicTarget = $this->_logic_target;
        // apply unique id
        if ($uid) {
            $logicTarget = [];
            foreach ($this->_logic_target as $target) {
                if (strstr((string)$target['id'], 'field_')) {
                    $target['id'] = str_replace('field_', 'field_' . $uid, (string)$target['id']);
                }
                if (strstr((string)$target['id'], 'fieldset_')) {
                    $target['id'] = str_replace('fieldset_', 'fieldset_' . $uid, (string)$target['id']);
                }
                if (strstr((string)$target['id'], 'submit')) {
                    $target['id'] = str_replace('submit', 'submit' . $uid, (string)$target['id']);
                }
                $logicTarget[] = $target;
            }
        }
        return $logicTarget;
    }

    /**
     * Set logic target
     *
     * @param $logicTarget
     * @return $this
     */
    public function _setLogicTarget($logicTarget): Form
    {
        $this->_logic_target = $logicTarget;
        return $this;
    }

    /**
     * Get hidden fields
     *
     * @return array
     */
    public function _getHidden(): array
    {
        return $this->_hidden;
    }

    /**
     * Set hidden fields
     *
     * @param $hidden
     * @return $this
     */
    public function _setHidden($hidden): Form
    {
        $this->_hidden = $hidden;
        return $this;
    }

    /**
     * Get field by code
     *
     * @param $fieldCode
     * @return FieldInterface|null
     */
    public function getFieldByCode($fieldCode): ?FieldInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldInterface::FORM_ID, $this->getId())
            ->addFilter(FieldInterface::CODE, $fieldCode)
            ->create();
        $items          = $this->fieldRepository->getList($searchCriteria)->getItems();
        /** @var FieldInterface[] $items */
        if (count($items) > 0) {
            return $items[0];
        }
        return null;
    }

    /**
     * Get form upload limit
     *
     * @param string $type
     * @return int
     */
    public function getUploadLimit(string $type = 'file'): int
    {
        $upload_limit = $this->scopeConfig->getValue('webforms/files/upload_limit', ScopeInterface::SCOPE_STORE);
        if ($this->getFilesUploadLimit()) {
            $upload_limit = $this->getFilesUploadLimit();
        }
        if ($type == 'image') {
            $upload_limit = $this->scopeConfig->getValue('webforms/images/upload_limit', ScopeInterface::SCOPE_STORE);
            if ($this->getImagesUploadLimit()) {
                $upload_limit = $this->getImagesUploadLimit();
            }
        }
        return intval($upload_limit);
    }

    /**
     * @inheritDoc
     */
    public function saveStoreData(int $storeId, $data): AbstractModel
    {
        if (isset($data[self::URL_KEY])) {
            $this->formResource->manageUrlRewrites($this->getId(), $storeId, $data[self::URL_KEY]);
        }
        return parent::saveStoreData($storeId, $data);
    }

    /**
     * @inheritDoc
     */
    public function getResults(): array
    {
        return $this->resultRepository->getListByFormId($this->getId())->getItems();
    }

    /**
     * @param mixed $type
     * @param array $config
     * @return array
     *
     * config params:
     *  'ne_field_ids' type:array default:false - (not equal field ids) exclude field with id in array
     *  'with_fieldset' type:bool default:true - wrap fields with fieldset
     *
     */
    public function getFieldsAsOptions($type = false, array $config = []): array
    {
        $neFieldIds   = empty($config['ne_field_ids']) ? false : $config['ne_field_ids'];
        $withFieldset = !isset($config['with_fieldset']) || $config['with_fieldset'];
        $options      = [];
        try {
            foreach ($this->getFieldsToFieldsets(true) as $fieldsetId => $fieldset) {
                $fieldOpts = [];

                /** @var FieldInterface $field */
                foreach ($fieldset['fields'] as $field) {
                    if ($neFieldIds) {
                        if (in_array($field->getId(), $neFieldIds)) {
                            continue;
                        }
                    }
                    if (is_array($type)) {
                        foreach ($type as $item) {
                            if (is_a($field, $item)) {
                                $fieldOpts[] = [
                                    'label' => $field->getName(),
                                    'value' => $field->getId()
                                ];
                            }
                        }
                    } else {
                        if (!$type || is_a($field, $type)) {
                            $fieldOpts[] = [
                                'label' => $field->getName(),
                                'value' => $field->getId()
                            ];
                        }
                    }
                }
                if ($withFieldset && $fieldsetId && count($fieldOpts)) {
                    $options[] = [
                        'label' => $fieldset['name'],
                        'value' => $fieldOpts,
                    ];
                } elseif (count($fieldOpts)) {
                    $options = array_merge($options, $fieldOpts);
                }
            }
        } catch (LocalizedException $e) {
        }
        return $options;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(FormResource::class);
    }

    /**
     * @param ResultInterface $result
     * @return array
     */
    public function getCustomerResultPermissionsByResult(ResultInterface $result): array
    {
        $permissions = $this->getCustomerResultPermissions();
        $allowedStatuses = (string)$this->scopeConfig->getValue('webforms/general/result_allowed_statuses');
        $allowedStatuses = $allowedStatuses ? explode(',', $allowedStatuses) : [];
        if (!in_array($result->getApproved(), $allowedStatuses)) {
            $permissions = array_diff($permissions, [Permission::EDIT, Permission::DELETE]);
        }
        return $permissions;
    }
}
