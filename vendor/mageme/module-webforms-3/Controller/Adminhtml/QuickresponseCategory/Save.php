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

namespace MageMe\WebForms\Controller\Adminhtml\QuickresponseCategory;


use Exception;
use MageMe\WebForms\Api\Data\QuickresponseCategoryInterface;
use MageMe\WebForms\Api\QuickresponseCategoryRepositoryInterface;
use MageMe\WebForms\Model\QuickresponseCategoryFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use RuntimeException;

class Save extends Action
{
    /**
     * @var QuickresponseCategoryFactory
     */
    protected $quickresponseCategoryFactory;

    /**
     * @var QuickresponseCategoryRepositoryInterface
     */
    protected $quickresponseCategoryRepository;

    /**
     * Save constructor.
     * @param QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository
     * @param QuickresponseCategoryFactory $quickresponseCategoryFactory
     * @param Context $context
     */
    public function __construct(
        QuickresponseCategoryRepositoryInterface $quickresponseCategoryRepository,
        QuickresponseCategoryFactory             $quickresponseCategoryFactory,
        Context                                  $context
    )
    {
        parent::__construct($context);
        $this->quickresponseCategoryFactory    = $quickresponseCategoryFactory;
        $this->quickresponseCategoryRepository = $quickresponseCategoryRepository;
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }
        $id    = empty($data[QuickresponseCategoryInterface::ID]) ? false : (int)$data[QuickresponseCategoryInterface::ID];
        $model = $id ? $this->quickresponseCategoryRepository->getById($id) : $this->quickresponseCategoryFactory->create();

        $this->_eventManager->dispatch(
            'webforms_quickresponse_prepare_save',
            ['form' => $model, 'request' => $this->getRequest()]
        );

        try {
            $model->setData($data);
            $this->quickresponseCategoryRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved this quick response category.'));

            /** @noinspection PhpUndefinedMethodInspection */
            $this->_getSession()->setFormData(false);
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit',
                    [QuickresponseCategoryInterface::ID => $model->getId(), '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException | RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the quick response category.'));
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->_getSession()->setFormData($data);
        return $resultRedirect->setPath('*/*/edit',
            [QuickresponseCategoryInterface::ID => $this->getRequest()->getParam(QuickresponseCategoryInterface::ID)]);
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageMe_WebForms::quickresponse');
    }
}