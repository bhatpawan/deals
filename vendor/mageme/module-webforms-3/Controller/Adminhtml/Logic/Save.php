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

namespace MageMe\WebForms\Controller\Adminhtml\Logic;

use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\LogicFactory;
use Magento\Backend\App\Action;
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
     * @var LogicFactory
     */
    protected $logicFactory;

    /**
     * @var LogicRepositoryInterface
     */
    protected $logicRepository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * Save constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param LogicRepositoryInterface $logicRepository
     * @param LogicFactory $logicFactory
     * @param AccessHelper $accessHelper
     * @param Action\Context $context
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        LogicRepositoryInterface $logicRepository,
        LogicFactory             $logicFactory,
        AccessHelper             $accessHelper,
        Action\Context           $context
    )
    {
        parent::__construct($context);
        $this->accessHelper    = $accessHelper;
        $this->logicFactory    = $logicFactory;
        $this->logicRepository = $logicRepository;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * @inheritdoc
     * @return Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function execute(): Redirect
    {
        $store  = (int)$this->getRequest()->getParam('store');
        $formId = (int)$this->getRequest()->getParam(FormInterface::ID);
        $data   = $this->getRequest()->getPostValue();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$data) {
            return $resultRedirect->setPath('*/form/');
        }
        $id    = !empty($data[LogicInterface::ID]) ? $data[LogicInterface::ID] : (int)$this->getRequest()->getParam(LogicInterface::ID);
        $model = $id ? $this->logicRepository->getById($id) : $this->logicFactory->create();
        if ($store) {
            $storeValues = $this->getRequest()->getParam('use_default');
            $storeData   = $this->getStoreData($storeValues, $data);

            unset($data[LogicInterface::ID]);
            unset($data[LogicInterface::FIELD_ID]);
            $model->saveStoreData($store, $storeData);
        }

        $this->_eventManager->dispatch(
            'webforms_logic_prepare_save',
            ['logic' => $model, 'request' => $this->getRequest()]
        );

        try {
            if (!$store) {
                $model->setData($data);
                $this->logicRepository->save($model);
            }
            $this->messageManager->addSuccessMessage(__('You saved this logic.'));
            if ($this->getRequest()->getParam('back')) {
                $params = [
                    LogicInterface::ID => $model->getId(),
                    'store' => $store
                ];
                if ($formId) {
                    $params[FormInterface::ID] = $formId;
                }
                return $resultRedirect->setPath(
                    '*/*/edit',
                    $params
                );
            }
            if ($formId) {
                return $resultRedirect->setPath('*/form/edit', [
                    FormInterface::ID => $formId,
                    'active_tab' => 'logic_section',
                    'store' => $store
                ]);
            }
            return $resultRedirect->setPath('*/field/edit',
                [FieldInterface::ID => $model->getFieldId(), 'active_tab' => 'logic_section', 'store' => $store]);
        } catch (LocalizedException | RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the logic.'));
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->_getSession()->setFormData($data);
        return $resultRedirect->setPath('*/*/edit',
            [LogicInterface::ID => $id, FieldInterface::ID => $this->getRequest()->getParam(FieldInterface::ID), 'store' => $store]);
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
        $id        = (int)$this->getRequest()->getParam(LogicInterface::ID);
        if ($id) {
            $logic   = $this->logicRepository->getById($id);
            $data    = $this->getRequest()->getPostValue('logic');
            $fieldId = $logic->getFieldId() ? $logic->getFieldId() : (int)$data[FieldInterface::ID];
            if ($fieldId) {
                $field  = $this->fieldRepository->getById($fieldId);
                $formId = $field->getFormId();
                if ($formId && !$this->accessHelper->isAllowed($formId)) {
                    $isAllowed = false;
                }
            }
        }
        return $isAllowed;
    }
}
