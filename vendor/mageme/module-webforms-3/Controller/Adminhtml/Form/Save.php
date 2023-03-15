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

namespace MageMe\WebForms\Controller\Adminhtml\Form;

use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\TmpFileCustomerNotificationRepositoryInterface;
use MageMe\WebForms\File\CustomerNotificationUploader;
use MageMe\WebForms\Model\FormFactory;
use Magento\Authorization\Model\ResourceModel\Rules as RulesResource;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Authorization\RoleLocator;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use RuntimeException;


class Save extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::save_form';

    /**
     * @var RoleLocator
     */
    protected $roleLocator;

    /**
     * @var RulesResource
     */
    protected $rulesResource;

    /**
     * @var RulesFactory
     */
    protected $rulesFactory;

    /**
     * @var CollectionFactory
     */
    protected $rulesCollectionFactory;

    /**
     * @var Builder
     */
    protected $aclBuilder;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var TmpFileCustomerNotificationRepositoryInterface
     */
    protected $tmpFileCustomerNotificationRepository;

    /**
     * @var CustomerNotificationUploader
     */
    protected $customerNotificationUploader;

    /**
     * Save constructor.
     * @param CustomerNotificationUploader $customerNotificationUploader
     * @param TmpFileCustomerNotificationRepositoryInterface $tmpFileCustomerNotificationRepository
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param FormRepositoryInterface $formRepository
     * @param FormFactory $formFactory
     * @param Builder $aclBuilder
     * @param CollectionFactory $rulesCollectionFactory
     * @param RulesFactory $rulesFactory
     * @param RulesResource $rulesResource
     * @param RoleLocator $roleLocator
     * @param Context $context
     */
    public function __construct(
        CustomerNotificationUploader                   $customerNotificationUploader,
        TmpFileCustomerNotificationRepositoryInterface $tmpFileCustomerNotificationRepository,
        FieldRepositoryInterface                       $fieldRepository,
        FieldsetRepositoryInterface                    $fieldsetRepository,
        FormRepositoryInterface                        $formRepository,
        FormFactory                                    $formFactory,
        Builder                                        $aclBuilder,
        CollectionFactory                              $rulesCollectionFactory,
        RulesFactory                                   $rulesFactory,
        RulesResource                                  $rulesResource,
        RoleLocator                                    $roleLocator,
        Context                                        $context
    )
    {
        parent::__construct($context);
        $this->roleLocator                           = $roleLocator;
        $this->rulesResource                         = $rulesResource;
        $this->rulesFactory                          = $rulesFactory;
        $this->rulesCollectionFactory                = $rulesCollectionFactory;
        $this->aclBuilder                            = $aclBuilder;
        $this->formFactory                           = $formFactory;
        $this->formRepository                        = $formRepository;
        $this->fieldsetRepository                    = $fieldsetRepository;
        $this->fieldRepository                       = $fieldRepository;
        $this->tmpFileCustomerNotificationRepository = $tmpFileCustomerNotificationRepository;
        $this->customerNotificationUploader          = $customerNotificationUploader;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $data         = $this->getRequest()->getPostValue();
        $store        = (int)$this->getRequest()->getParam('store');
        $redirectBack = $this->getRequest()->getParam('back', false);

        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }
        $id = empty($data[FormInterface::ID]) ? false : $data[FormInterface::ID];
        try {
            $form                                                   = $id ? $this->formRepository->getById($id) : $this->formFactory->create();
            $data[FormInterface::CUSTOMER_NOTIFICATION_ATTACHMENTS] = $this->processCustomerAttachments($data, $form);

            $this->_eventManager->dispatch(
                'webforms_form_prepare_save',
                ['form' => $form, 'request' => $this->getRequest()]
            );

            if ($store) {
                $storeValues = $this->getRequest()->getParam('use_default');
                $storeData   = $this->getStoreData($storeValues, $data);

                unset($data[FormInterface::ID]);
                $form->saveStoreData($store, $storeData);
            } else {
                $form->setData($data);
                $this->formRepository->save($form);
            }

            $form->updateFieldPositions();
            $form->updateFieldsetPositions();

            if (!$this->_authorization->isAllowed('Magento_Backend::all')) {
                try {
                    $this->updateRolePermission($form);
                } catch (AlreadyExistsException $e) {
                    $this->messageManager->addErrorMessage(__('Error happened during update role permission: %1',
                        $e->getMessage()));
                }
            }
            $this->messageManager->addSuccessMessage(__('You saved this form.'));

            /** @noinspection PhpUndefinedMethodInspection */
            $this->_getSession()->setFormData(false);

            if ($redirectBack === 'new') {
                return $resultRedirect->setPath('*/*/new');
            } elseif ($redirectBack === 'duplicate') {
                return $resultRedirect->setPath('*/*/duplicate',
                    [FormInterface::ID => $form->getId()]);
            } elseif ($redirectBack) {
                return $resultRedirect->setPath('*/*/edit',
                    [FormInterface::ID => $form->getId(), '_current' => true, 'store' => $store]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException | RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the form.'));
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->_getSession()->setFormData($data);
        return $resultRedirect->setPath('*/*/edit',
            [FormInterface::ID => $this->getRequest()->getParam(FormInterface::ID), 'store' => $store]);
    }

    private function processCustomerAttachments(array $data, FormInterface $form): array
    {
        if (!$form->getId() || empty($data[FormInterface::CUSTOMER_NOTIFICATION_ATTACHMENTS])) {
            return [];
        }
        $result = [];
        foreach ($data[FormInterface::CUSTOMER_NOTIFICATION_ATTACHMENTS] as $item) {
            if (is_numeric($item['id'])) {
                $result[] = [
                    'id' => $item['id'],
                    'file' => $item['file'],
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'hash' => $item['hash'],
                    'url' => $item['url'],
                    'size' => $item['size'],
                ];
                continue;
            }
            try {
                $tmpFile = $this->tmpFileCustomerNotificationRepository->getByHash($item['hash']);
                if ($tmpFile->getId()) {
                    $file = $this->customerNotificationUploader->copyFileFromTmpDir($tmpFile, $form->getId());
                    $this->tmpFileCustomerNotificationRepository->delete($tmpFile);

                    $result[] = [
                        'id' => $file->getId(),
                        'file' => $file->getName(),
                        'name' => $file->getName(),
                        'type' => $file->getMimeType(),
                        'hash' => $file->getLinkHash(),
                        'url' => $this->getUrl('webforms/file/customerNotificationDownload',
                            ['hash' => $file->getLinkHash()]),
                        'size' => $file->getSize(),
                    ];
                }
            } catch (Exception $e) {
                continue;
            }
        }
        return $result;
    }

    /**
     * Get data for store scope
     *
     * @param $storeValues
     * @param $data
     * @return array
     */
    private function getStoreData($storeValues, $data): array
    {
        $storeData = [];
        if (is_array($storeValues)) {
            $values = array_filter($storeValues, function ($value) {
                return !$value;
            });
            foreach ($values as $key => $value) {
                if (isset($data[$key])) {
                    $storeData[$key] = $data[$key];
                }
            }
        }
        return $storeData;
    }

    /**
     * @param FormInterface $form
     * @throws AlreadyExistsException
     */
    protected function updateRolePermission(FormInterface $form)
    {
        $collection = $this->rulesCollectionFactory->create()
            ->addFilter('role_id', $this->roleLocator->getAclRoleId())
            ->addFilter('resource_id', 'MageMe_WebForms::form' . $form->getId())
            ->addFilter('permission', 'allow');
        if ($collection->count() === 0) {
            $rules = $this->rulesFactory->create()->setData([
                'role_id' => $this->roleLocator->getAclRoleId(),
                'resource_id' => 'MageMe_WebForms::form' . $form->getId(),
                'permission' => 'allow'
            ]);
            $this->rulesResource->save($rules);
        }
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(FormInterface::ID);
        if ($id) {
            if (!$this->_authorization->isAllowed('Magento_Backend::all')) {
                $collection = $this->rulesCollectionFactory->create()
                    ->addFilter('role_id', $this->roleLocator->getAclRoleId())
                    ->addFilter('resource_id', 'MageMe_WebForms::form' . $id)
                    ->addFilter('permission', 'allow');
                if ($collection->count() === 0) {
                    $isAllowed = false;
                }
            }
        }
        return $isAllowed;
    }
}
