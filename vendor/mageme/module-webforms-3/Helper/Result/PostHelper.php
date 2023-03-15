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

namespace MageMe\WebForms\Helper\Result;


use MageMe\Core\Helper\ConfigHelper;
use MageMe\Core\Helper\NetworkHelper;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use MageMe\WebForms\Config\Options\Result\Permission;
use MageMe\WebForms\Controller\ResultAction;
use MageMe\WebForms\Helper\SurveyHelper;
use MageMe\WebForms\Mail\AdminNotification;
use MageMe\WebForms\Mail\CustomerNotification;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\Result;
use MageMe\WebForms\Model\ResultFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Zend_Validate_Exception;


class PostHelper
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var SurveyHelper
     */
    protected $surveyHelper;
    /**
     * @var NetworkHelper
     */
    protected $networkHelper;

    /**
     * @var CustomerNotification
     */
    protected $customerNotification;

    /**
     * @var AdminNotification
     */
    protected $adminNotification;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $customerSessionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var bool
     */
    private $customerIdFromPost = false;


    /**
     * PostHelper constructor.
     * @param CustomerNotification $customerNotification
     * @param AdminNotification $adminNotification
     * @param NetworkHelper $networkHelper
     * @param SurveyHelper $surveyHelper
     * @param ResultRepositoryInterface $resultRepository
     * @param ResultFactory $resultFactory
     * @param ConfigHelper $configHelper
     * @param StoreManager $storeManager
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param FieldRepositoryInterface $fieldRepository
     * @param SessionFactory $sessionFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CustomerNotification                      $customerNotification,
        AdminNotification                         $adminNotification,
        NetworkHelper                             $networkHelper,
        SurveyHelper                              $surveyHelper,
        ResultRepositoryInterface                 $resultRepository,
        ResultFactory                             $resultFactory,
        ConfigHelper                              $configHelper,
        StoreManager                              $storeManager,
        ManagerInterface                          $messageManager,
        RequestInterface                          $request,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        FieldRepositoryInterface                  $fieldRepository,
        SessionFactory                            $sessionFactory,
        ScopeConfigInterface                      $scopeConfig
    )
    {
        $this->request                = $request;
        $this->messageManager         = $messageManager;
        $this->storeManager           = $storeManager;
        $this->configHelper           = $configHelper;
        $this->resultFactory          = $resultFactory;
        $this->resultRepository       = $resultRepository;
        $this->surveyHelper           = $surveyHelper;
        $this->networkHelper          = $networkHelper;
        $this->adminNotification      = $adminNotification;
        $this->customerNotification   = $customerNotification;
        $this->eventManager           = $eventManager;
        $this->fieldRepository        = $fieldRepository;
        $this->scopeConfig            = $scopeConfig;
        $this->customerSessionFactory = $sessionFactory;
    }

    /**
     * @param FormInterface $form
     * @param array $config
     * @return bool|Result
     * @throws Zend_Validate_Exception
     */
    public function savePostResult(FormInterface $form, array $config = [])
    {
        $result = $this->postResult($form, $config);
        return $result['model'] ?: false;
    }

    /**
     * TODO: comment
     *
     * @param FormInterface $form
     * @param array $config
     * @return array
     * @throws Zend_Validate_Exception
     */
    public function postResult(FormInterface $form, array $config = []): array
    {
        $resultData = [
            'success' => false,
            'errors' => [],
            'model' => false,
        ];
        try {
            $postData    = $this->getPost($form, $config);
            $result      = empty($postData['result_id']) ? $this->resultFactory->create() :
                $this->resultRepository->getById($postData['result_id']);
            $isNewResult = !$result->getId();

            // set empty values, so It's possible to save deselected fields
            if (!$isNewResult) {
                $result->addFieldArray(false);
                foreach ($result->getData('field') as $key => $value) {
                    if (!array_key_exists($key, $postData['field'])) {
                        $postData['field'][$key] = '';
                    }
                }
            }

            if (empty($postData['field'])) {
                $postData['field'] = [];
            }

            $errors = $this->validatePostResult($form, $postData, $config);

            $validate = new DataObject();
            $validate->setData('errors', $errors);
            $this->eventManager->dispatch('webforms_validate_post_result', ['form' => $form, 'postData' => $postData, 'validate' => $validate]);
            $errors = $validate->getData('errors');

            if (count($errors)) {
                foreach ($errors as $error) {
                    $this->messageManager->addErrorMessage($error);
                    if ($this->scopeConfig->getValue('webforms/general/store_temp_submission_data',
                        ScopeInterface::SCOPE_STORE)) {

                        /** @noinspection PhpUndefinedMethodInspection */
                        $this->getCustomerSession()->setData('webform_result_tmp_' . $form->getId(), $postData);
                    }
                }
                $resultData['success'] = false;
                $resultData['errors']  = $errors;
                return $resultData;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $this->getCustomerSession()->setData('webform_result_tmp_' . $form->getId(), false);
            foreach ($form->_getFieldsToFieldsets() as $fieldset) {

                /** @var FieldInterface $field */
                foreach ($fieldset['fields'] as $field) {
                    $field->preparePostData($postData, $config, $result, $this->configHelper->isAdmin());
                }
            }

            $this->setPostResultModelData($form, $result, $postData, $isNewResult);
            $this->resultRepository->save($result);

            foreach ($form->_getFieldsToFieldsets() as $fieldset) {

                /** @var FieldInterface $field */
                foreach ($fieldset['fields'] as $field) {
                    $field->processPostResult($result);
                }
            }

            $this->eventManager->dispatch('webforms_result_submit', ['result' => $result]);

            // send e-mail
            if ($isNewResult) {

                // send admin notification
                if ($form->getIsAdminNotificationEnabled()) {
                    $this->adminNotification->sendEmail($result);
                }

                // send customer notification
                if ($form->getIsCustomerNotificationEnabled()) {
                    $this->customerNotification->sendEmail($result);
                }

                // email contact
                $fields_to_fieldsets = $form->_getFieldsToFieldsets();
                foreach ($fields_to_fieldsets as $fieldset) {

                    /** @var FieldInterface|AbstractField $field */
                    foreach ($fieldset['fields'] as $field) {
                        $field->processNewResult($result);
                    }
                }

            }

            if ($form->getIsSurvey()) {
                $this->surveyHelper->setCookie($form->getId(), $result->getId());
            }
            $resultData['success'] = true;
            $resultData['model']   = $result;

            if (!$this->configHelper->isAdmin()) {
                if ($form->getIsSubmissionsNotStored()) {
                    $this->resultRepository->delete($result);
                }
            }

            return $resultData;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultData['success']  = false;
            $resultData['errors'][] = $e->getMessage();
            return $resultData;
        }
    }

    /**
     * TODO: comment
     *
     * @param FormInterface $form
     * @param array $config
     * @return mixed
     */
    public function getPost(FormInterface $form, array $config)
    {
        $postData        = $this->request->getParams();
        $emptyFieldArray = false;
        if (!empty($config['prefix'])) {
            $postData = $this->request->getParam($config['prefix']);
        }
        if (empty($postData['field'])) {
            $postData['field'] = [];
            $emptyFieldArray   = true;
        }
        $postData['logic_visibility'] = [];

        // fill fields data
        $fields_to_fieldsets = $form->getFieldsToFieldsets();
        $logic_rules         = $form->getLogic(false);
        foreach ($fields_to_fieldsets as $fieldset) {

            /** @var FieldInterface $field */
            foreach ($fieldset['fields'] as $field) {

                // check visibility
                $target_field     = [
                    "id" => 'field_' . $field->getId(),
                    'logic_visibility' => $field->getData('logic_visibility'),
                ];
                $field_visibility = $form->getTargetVisibility($target_field, $logic_rules, $postData['field']);
                $field->setData('visible', $field_visibility);

                $postData['field'][$field->getId()] = $field->getPostValue($postData, $config, $field_visibility, $emptyFieldArray);
                $postData['logic_visibility'][$field->getId()] = $field_visibility;
            }
        }
        return $postData;
    }

    /**
     * TODO: comment
     *
     * @param FormInterface $form
     * @param array $postData
     * @param array $config
     * @return array
     * @throws NoSuchEntityException
     * @throws Zend_Validate_Exception
     */
    public function validatePostResult(FormInterface $form, array $postData, array $config = []): array
    {
        $errors = [];

        // check access settings
        $errors = array_merge($errors, $this->validateAccess($form, $postData, $config));

        // validate result permissions
        $errors = array_merge($errors, $this->validateResultPermissions($form, $postData, $config));

        // check custom validation
        $errors = array_merge($errors, $this->validateFields($form, $postData, $config));

        // check captcha
        if (empty($errors)) {
            $errors = $this->validateCaptcha($form, $postData, $config);
        }

        return $errors;
    }

    /**
     * @param FormInterface $form
     * @param array $postData
     * @param array $config
     * @return array
     */
    public function validateAccess(FormInterface $form, array $postData, array $config = []): array {
        $errors = [];
        if (!$form->canAccess()) {
            $errors[] = __('You don\'t have enough permissions to access this content. Please login.');
        }
        return $errors;
    }

    /**
     * @param FormInterface $form
     * @param array $postData
     * @param array $config
     * @return array
     * @throws NoSuchEntityException
     */
    public function validateResultPermissions(FormInterface $form, array $postData, array $config = []): array {
        $errors = [];
        $result_id = empty($postData[ResultInterface::ID]) ? false : $postData[ResultInterface::ID];
        if (!$this->configHelper->isAdmin() && $result_id) {
            $customer      = $this->getCustomerSession()->getCustomer();
            $_result       = $this->resultRepository->getById($result_id);
            $accessAllowed = ResultAction::ALLOWED;
            $access        = new DataObject([$accessAllowed => false]);

            if ($_result->getCustomerId() == $customer->getId()) {
                $access->setData($accessAllowed, true);
            }

            $this->eventManager->dispatch('webforms_controller_result_access',
                ['access' => $access, 'result' => $_result]);

            if (!in_array(Permission::EDIT, $form->getCustomerResultPermissionsByResult($_result))) {
                $access->setData($accessAllowed, false);
            }

            if (!$access->getData($accessAllowed)) {
                $errors[] = __('Access denied.');
            }
        }
        return $errors;
    }

    /**
     * @param FormInterface $form
     * @param array $postData
     * @param array $config
     * @return array
     * @throws Zend_Validate_Exception
     */
    public function validateFields(FormInterface $form, array $postData, array $config = []): array {
        $errors = [];
        $logicRules                                        = $form->getLogic();
        $fields_to_fieldsets                               = $form->getFieldsToFieldsets();
        $fields_to_fieldsets['hidden']['fields']           = $form->_getHidden();
        $fields_to_fieldsets['hidden']['logic_visibility'] = true;
        foreach ($fields_to_fieldsets as $fieldset_id => $fieldset) {

            /** @var FieldInterface|AbstractField $field */
            foreach ($fieldset['fields'] as $field) {

                // get logic visibility
                $target_field    = [
                    'id' => 'field_' . $field->getId(),
                    'logic_visibility' => $field->getData('logic_visibility'),
                ];
                $targetFieldset  = [
                    'id' => 'fieldset_' . $fieldset_id,
                    'logic_visibility' => $fieldset['logic_visibility'],
                ];
                $logicVisibility = $form->getTargetVisibility($target_field, $logicRules, $postData['field']) &&
                    $form->getTargetVisibility($targetFieldset, $logicRules, $postData['field']);

                $errors = array_merge($errors,
                    $field->getPostErrors($postData, $logicVisibility, $config));
            }
        }
        return $errors;
    }

    /**
     * @param FormInterface $form
     * @param array $postData
     * @param array $config
     * @return array
     */
    public function validateCaptcha(FormInterface $form, array $postData, array $config = []): array
    {
        $errors = [];
        if (!$this->getCustomerSession()->getData('captcha_verified') && $form->useCaptcha()) {
            if ($this->request->getParam('g-recaptcha-response')) {
                $verify = $form->getCaptcha()->verify($this->request->getParam('g-recaptcha-response'));
                if (!$verify) {
                    $errors[] = $this->scopeConfig->getValue('webforms/captcha/validation_failure_message', ScopeInterface::SCOPE_STORE);
                } else {
                    $this->getCustomerSession()->setData('captcha_verified', true);
                }
            } else {
                $errors[] = $this->scopeConfig->getValue('webforms/captcha/technical_failure_message', ScopeInterface::SCOPE_STORE);
            }
        }
        return $errors;
    }

    /**
     * @param FormInterface $form
     * @param ResultInterface|Result $result
     * @param array $postData
     * @param bool $isNewResult
     * @throws NoSuchEntityException
     */
    public function setPostResultModelData(FormInterface $form, ResultInterface &$result, array $postData, bool $isNewResult)
    {
        if ($isNewResult) {
            $status = $form->getIsApprovalControlsEnabled() ? ApprovalStatus::STATUS_PENDING : ApprovalStatus::STATUS_APPROVED;
            $result->setApproved($status);
            $submittedFrom = isset($postData[ResultInterface::SUBMITTED_FROM]) ? json_decode($postData[ResultInterface::SUBMITTED_FROM], true) : [];
            $referrer      = $postData[ResultInterface::REFERRER_PAGE] ?? '';
            $result->setSubmittedFrom($submittedFrom);
            $result->setReferrerPage($referrer);
        }
        $customerId = (int)$this->getCustomerSession()->getCustomerId();
        if ($this->isCustomerIdFromPost() && isset($postData['customer_id'])) {
            $customerId = (int)$postData['customer_id'];
        }

        $result->setData('field', $postData['field'])
            ->setFormId($form->getId())
            ->setStoreId($this->storeManager->getStore()->getId())
            ->setCustomerId($customerId);

        if ($isNewResult && $this->scopeConfig->getValue('webforms/general/collect_customer_ip', ScopeInterface::SCOPE_STORE)) {
            $result->setCustomerIp($this->networkHelper->getRealIp());
        }

        $result->setForm($form);
    }

    /**
     * @return bool
     */
    public function isCustomerIdFromPost(): bool
    {
        return $this->customerIdFromPost;
    }

    /**
     * @param bool $customerIdFromPost
     * @return PostHelper
     */
    public function setCustomerIdFromPost(bool $customerIdFromPost): PostHelper
    {
        $this->customerIdFromPost = $customerIdFromPost;
        return $this;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return Session
     **/
    private function getCustomerSession(): Session
    {
        if ($this->customerSession === null) {
            $this->customerSession = $this->customerSessionFactory->create();
        }

        return $this->customerSession;
    }
}
