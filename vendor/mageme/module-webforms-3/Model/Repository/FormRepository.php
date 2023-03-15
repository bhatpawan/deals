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

namespace MageMe\WebForms\Model\Repository;

use Exception;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\FormSearchResultInterface;
use MageMe\WebForms\Api\FormSearchResultInterfaceFactory;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Config\Exception\UrlRewriteAlreadyExistsException;
use MageMe\WebForms\Config\Options\Captcha\Mode as CaptchaMode;
use MageMe\WebForms\Helper\Result\PostHelper;
use MageMe\WebForms\Model\FormFactory;
use MageMe\WebForms\Model\ResourceModel\Form as ResourceForm;
use MageMe\WebForms\Model\ResourceModel\Form\CollectionFactory as FormCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class FormRepository implements FormRepositoryInterface
{
    /**
     * @var ResourceForm
     */
    protected $resource;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var FormCollectionFactory
     */
    protected $formCollectionFactory;

    /**
     * @var FormSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var PostHelper
     */
    protected $postHelper;

    /**
     * FormRepository constructor.
     * @param PostHelper $postHelper
     * @param ResultRepositoryInterface $resultRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FormSearchResultInterfaceFactory $searchResultInterfaceFactory
     * @param FormCollectionFactory $formCollectionFactory
     * @param FormFactory $formFactory
     * @param ResourceForm $resource
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        PostHelper                       $postHelper,
        ResultRepositoryInterface        $resultRepository,
        SearchCriteriaBuilder            $searchCriteriaBuilder,
        FormSearchResultInterfaceFactory $searchResultInterfaceFactory,
        FormCollectionFactory            $formCollectionFactory,
        FormFactory                      $formFactory,
        ResourceForm                     $resource,
        CollectionProcessorInterface     $collectionProcessor
    )
    {
        $this->resource              = $resource;
        $this->formFactory           = $formFactory;
        $this->collectionProcessor   = $collectionProcessor;
        $this->formCollectionFactory = $formCollectionFactory;
        $this->searchResultFactory   = $searchResultInterfaceFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resultRepository      = $resultRepository;
        $this->postHelper            = $postHelper;
    }

    /**
     * @inheritDoc
     */
    public function getByCode(string $code, ?int $storeId = null)
    {
        $form = $this->formFactory->create();
        if (!is_null($storeId)) {
            $form->setStoreId($storeId);
        }
        $this->resource->load($form, $code, FormInterface::CODE);
        if (!$form->getId()) {
            throw new NoSuchEntityException(__('Unable to find form with code "%1"', $code));
        }
        return $form;
    }

    /**
     * @inheritDoc
     * @throws UrlRewriteAlreadyExistsException
     */
    public function save(FormInterface $form): FormInterface
    {
        try {
            $this->resource->save($form);
        } catch (UrlRewriteAlreadyExistsException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $form;
    }

    /**
     * @inheritDoc
     */
    public function delete(FormInterface $form): bool
    {
        try {
            $this->resource->delete($form);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null, ?int $storeId = null): FormSearchResultInterface
    {
        if (!$searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }
        $collection = $this->formCollectionFactory->create();
        if (!is_null($storeId)) {
            $collection->setStoreId($storeId);
        }
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getDataById(int $id): array
    {
        $form = $this->getById($id);

        // For JSON content type
        $fieldsets = [];
        foreach ($form->getFieldsToFieldsets() as $key => $fieldset) {
            foreach ($fieldset['fields'] as &$field) {
                $field = $field->getData();
            }
            $fieldset[FieldsetInterface::ID] = $key;
            $fieldsets[]                     = $fieldset;
        }

        return [
            'form' => [
                FormInterface::ID => $form->getId(),
                FormInterface::NAME => $form->getName(),
                FormInterface::CODE => $form->getCode(),
                FormInterface::REDIRECT_URL => $form->getRedirectUrl(),
                FormInterface::IS_SUCCESS_SESSION_DISPLAYED => $form->getIsSuccessSessionDisplayed(),
                FormInterface::DESCRIPTION => $form->getDescription(),
                FormInterface::SUCCESS_TEXT => $form->getSuccessText(),
                FormInterface::IS_ADMIN_NOTIFICATION_ENABLED => $form->getIsAdminNotificationEnabled(),
                FormInterface::IS_CUSTOMER_NOTIFICATION_ENABLED => $form->getIsCustomerNotificationEnabled(),
                FormInterface::ADMIN_NOTIFICATION_EMAIL => $form->getAdminNotificationEmail(),
                FormInterface::CUSTOMER_NOTIFICATION_REPLY_TO => $form->getCustomerNotificationReplyTo(),
                FormInterface::IS_APPROVAL_NOTIFICATION_ENABLED => $form->getIsApprovalNotificationEnabled(),
                FormInterface::IS_ADMIN_NOTIFICATION_ATTACHMENT_ENABLED => $form->getIsAdminNotificationAttachmentEnabled(),
                FormInterface::IS_CUSTOMER_NOTIFICATION_ATTACHMENT_ENABLED => $form->getIsCustomerNotificationAttachmentEnabled(),
                FormInterface::IS_SURVEY => $form->getIsSurvey(),
                FormInterface::IS_APPROVAL_CONTROLS_ENABLED => $form->getIsApprovalControlsEnabled(),
                FormInterface::CAPTCHA_MODE => $form->getCaptchaMode(),
                FormInterface::FILES_UPLOAD_LIMIT => $form->getFilesUploadLimit(),
                FormInterface::IMAGES_UPLOAD_LIMIT => $form->getImagesUploadLimit(),
                FormInterface::CREATED_AT => $form->getCreatedAt(),
                FormInterface::IS_ACTIVE => $form->getIsActive(),
                FormInterface::SUBMIT_BUTTON_TEXT => $form->getSubmitButtonText(),
                FormInterface::CUSTOMER_NOTIFICATION_SENDER_NAME => $form->getCustomerNotificationSenderName(),
                FormInterface::ADMIN_NOTIFICATION_BCC => $form->getAdminNotificationBcc(),
                FormInterface::CUSTOMER_NOTIFICATION_BCC => $form->getCustomerNotificationBcc(),
                FormInterface::APPROVAL_NOTIFICATION_BCC => $form->getApprovalNotificationBcc(),
                FormInterface::IS_URL_PARAMETERS_ACCEPTED => $form->getIsUrlParametersAccepted(),
                FormInterface::IS_FRONTEND_DOWNLOAD_ALLOWED => $form->getIsFrontendDownloadAllowed(),
                FormInterface::IS_SUBMISSIONS_NOT_STORED => $form->getIsSubmissionsNotStored(),
                FormInterface::IS_CLEANUP_ENABLED => $form->getIsCleanupEnabled(),
                FormInterface::CLEANUP_PERIOD => $form->getCleanupPeriod(),
                FormInterface::URL_KEY => $form->getUrlKey(),
                FormInterface::META_KEYWORDS => $form->getMetaKeywords(),
                FormInterface::META_DESCRIPTION => $form->getMetaDescription(),
                FormInterface::META_TITLE => $form->getMetaTitle(),
                FormInterface::BELOW_TEXT => $form->getBelowText(),
                'fieldsets' => $fieldsets,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id, ?int $storeId = null)
    {
        $form = $this->formFactory->create();
        if (!is_null($storeId)) {
            $form->setStoreId($storeId);
        }
        $this->resource->load($form, $id);
        if (!$form->getId()) {
            throw new NoSuchEntityException(__('Unable to find form with ID "%1"', $id));
        }
        return $form;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getResultsById(int $id, ?int $customerId = null): array
    {
        $form        = $this->getById($id);
        $results     = $form->getResults();
        $fields      = $form->getFields();
        $resultsData = [];
        foreach ($results as $result) {
            if ($customerId) {
                if ($result->getCustomerId() == $customerId) {
                    $resultsData[] = $this->resultRepository->getResultData($result, $fields);
                }
            } else {
                $resultsData[] = $this->resultRepository->getResultData($result, $fields);
            }
        }
        return $resultsData;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws Exception
     */
    public function submitById(int $id)
    {
        $form = $this->getById($id);
        $form->setCaptchaMode(CaptchaMode::MODE_OFF);
        $result = $this->postHelper->setCustomerIdFromPost(true)->postResult($form);
        if ($result) {
            return [
                'result' => [
                    'success' => $result['success'],
                    'errors' => $result['errors'],
                    'result' => $result['model'] ? $result['model']->getId() : false,
                ],
            ];
        }
        return false;
    }
}
