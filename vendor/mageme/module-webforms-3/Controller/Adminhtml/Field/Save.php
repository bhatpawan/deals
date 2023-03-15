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

namespace MageMe\WebForms\Controller\Adminhtml\Field;

use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\Utility\Field\HiddenInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\FieldFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
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
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * Save constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldFactory $fieldFactory
     * @param AccessHelper $accessHelper
     * @param Action\Context $context
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        FieldFactory             $fieldFactory,
        AccessHelper             $accessHelper,
        Action\Context           $context
    )
    {
        parent::__construct($context);
        $this->accessHelper    = $accessHelper;
        $this->fieldFactory    = $fieldFactory;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * Save action
     *
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute(): ResultInterface
    {
        $store        = (int)$this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
        $data         = $this->getRequest()->getPostValue();
        $redirectBack = $this->getRequest()->getParam('back', false);

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$data) {
            return $resultRedirect->setPath('webforms/form/');
        }
        $id = !empty($data[FieldInterface::ID]) ? $data[FieldInterface::ID] : $this->getRequest()->getParam(FieldInterface::ID);

        /** @var FieldInterface|AbstractField $field */
        $field = $id ? $this->fieldRepository->getById($id,
            $store) : $this->fieldFactory->create($data[FieldInterface::TYPE]);
        if (!$field->getId()) {
            $field->setFormId((int)$data[FieldInterface::FORM_ID]);
            $this->fieldRepository->save($field);
            $data[FieldInterface::ID] = $field->getId();
        }
        $field = $field->getType() == $data[FieldInterface::TYPE] ? $field : $this->fieldFactory->create($data[FieldInterface::TYPE])->setData($field->getData());
        $field->processTypeAttributesOnSave($data, $store);

        $this->_eventManager->dispatch(
            'webforms_field_prepare_save',
            ['field' => $field, 'request' => $this->getRequest()]
        );

        /**
         * Save store information
         */
        if ($store) {
            $storeValues = $this->getRequest()->getParam('use_default');
            $storeData   = $this->getStoreData($storeValues, $data);

            unset($data[FieldInterface::ID]);
            unset($data[FieldInterface::FORM_ID]);
            $field->saveStoreData($store, $storeData);
        }

        try {
            if (!$store) {
                $field->setData($data);
                $this->fieldRepository->save($field);
            }

            $this->messageManager->addSuccessMessage(__('You saved this field.'));

            /** @noinspection PhpUndefinedMethodInspection */
            $this->_getSession()->setFormData(false);
            if ($redirectBack === 'new') {
                return $resultRedirect->setPath('*/*/new',
                    [FormInterface::ID => $field->getFormId()]);
            } elseif ($redirectBack === 'duplicate') {
                return $resultRedirect->setPath('*/*/duplicate',
                    [FieldInterface::ID => $field->getId()]);
            } elseif ($redirectBack) {
                return $resultRedirect->setPath('*/*/edit',
                    [FieldInterface::ID => $field->getId(), 'store' => $store, '_current' => true]);
            }
            return $resultRedirect->setPath('*/form/edit',
                [FormInterface::ID => $field->getFormId(), 'active_tab' => 'fields_section', 'store' => $store]);
        } catch (LocalizedException | RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the field.'));
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->_getSession()->setFormData($data);
        return $resultRedirect->setPath('*/*/edit',
            [
                FieldInterface::ID => $id,
                FormInterface::ID => $this->getRequest()->getParam(FormInterface::ID),
                'store' => $store
            ]);
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
                } else {
                    if (preg_match('/(^' . $data[FieldInterface::TYPE] . '_)(.+)/', (string)$key, $matches)) {
                        if (isset($data[$matches[2]])) {
                            $storeData[$matches[2]] = $data[$matches[2]];
                        }
                    }
                }
            }
        }
        return $storeData;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $id        = (int)$this->getRequest()->getParam(FieldInterface::ID);
        $formId    = (int)$this->getRequest()->getParam(FormInterface::ID);
        $data      = $this->getRequest()->getPostValue('field');
        if ($id) {
            $model  = $this->fieldRepository->getById($id);
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
