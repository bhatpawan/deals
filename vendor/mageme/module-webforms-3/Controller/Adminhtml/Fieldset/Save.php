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

namespace MageMe\WebForms\Controller\Adminhtml\Fieldset;

use Exception;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\FieldsetFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use RuntimeException;

class Save extends Action
{
    /**
     * @inheritDoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::save_form';

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var FieldsetFactory
     */
    protected $fieldsetFactory;

    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;

    /**
     * Save constructor.
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param FieldsetFactory $fieldsetFactory
     * @param AccessHelper $accessHelper
     * @param Context $context
     */
    public function __construct(
        FieldsetRepositoryInterface $fieldsetRepository,
        FieldsetFactory             $fieldsetFactory,
        AccessHelper                $accessHelper,
        Context                     $context)
    {
        parent::__construct($context);
        $this->accessHelper       = $accessHelper;
        $this->fieldsetFactory    = $fieldsetFactory;
        $this->fieldsetRepository = $fieldsetRepository;
    }

    /**
     * @inheritdoc
     * @return Redirect
     * @throws LocalizedException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function execute(): Redirect
    {
        $store        = (int)$this->getRequest()->getParam('store');
        $data         = $this->getRequest()->getPostValue();
        $redirectBack = $this->getRequest()->getParam('back', false);

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$data) {
            return $resultRedirect->setPath('webforms/form/');
        }
        $id       = !empty($data[FieldsetInterface::ID]) ? $data[FieldsetInterface::ID] : $this->getRequest()->getParam(FieldsetInterface::ID);
        $fieldset = $id ? $this->fieldsetRepository->getById($id) : $this->fieldsetFactory->create();

        if ($store) {
            $storeValues = $this->getRequest()->getParam('use_default');
            $storeData   = $this->getStoreData($storeValues, $data);

            unset($data[FieldsetInterface::ID]);
            unset($data[FieldsetInterface::FORM_ID]);
            $fieldset->saveStoreData($store, $storeData);
        }

        $this->_eventManager->dispatch(
            'webforms_fieldset_prepare_save',
            ['fieldset' => $fieldset, 'request' => $this->getRequest()]
        );

        try {
            if (!$store) {
                $fieldset->setData($data);
                $this->fieldsetRepository->save($fieldset);
            }
            $this->messageManager->addSuccessMessage(__('You saved this fieldset.'));

            /** @noinspection PhpUndefinedMethodInspection */
            $this->_getSession()->setFormData(false);
            if ($redirectBack === 'new') {
                return $resultRedirect->setPath('*/*/new',
                    [FormInterface::ID => $fieldset->getFormId()]);
            } elseif ($redirectBack) {
                return $resultRedirect->setPath('*/*/edit', [FieldsetInterface::ID => $fieldset->getId(), '_current' => true, 'store' => $store]);
            }
            return $resultRedirect->setPath('webforms/form/edit',
                [FormInterface::ID => $fieldset->getFormId(), 'active_tab' => 'fieldsets_section', 'store' => $store]);
        } catch (LocalizedException | RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the fieldset.'));
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->_getSession()->setFormData($data);
        return $resultRedirect->setPath('*/*/edit',
            [FieldsetInterface::ID => $id, FormInterface::ID => $this->getRequest()->getParam(FormInterface::ID), 'store' => $store]);
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
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(FieldsetInterface::ID);
        $formId    = (int)$this->getRequest()->getParam(FormInterface::ID);
        $data      = $this->getRequest()->getPostValue('fieldset');
        if ($id) {
            $model  = $this->fieldsetRepository->getById($id);
            $formId = $model->getFormId();
        }
        if (!empty($data[FormInterface::ID])) {
            $formId = $data[FormInterface::ID];
        }
        if ($formId && !$this->accessHelper->isAllowed($formId)) {
            $isAllowed = false;
        }
        return $isAllowed;
    }
}
