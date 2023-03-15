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

namespace MageMe\WebForms\Controller\Adminhtml\Quickresponse;


use MageMe\WebForms\Api\Data\QuickresponseInterface;
use MageMe\WebForms\Api\QuickresponseRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Get extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var QuickresponseRepositoryInterface
     */
    protected $quickresponseRepository;

    /**
     * Get constructor.
     * @param QuickresponseRepositoryInterface $quickresponseRepository
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        QuickresponseRepositoryInterface $quickresponseRepository,
        JsonFactory                      $resultJsonFactory,
        Context                          $context)
    {
        parent::__construct($context);
        $this->resultJsonFactory       = $resultJsonFactory;
        $this->quickresponseRepository = $quickresponseRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id     = (int)$this->getRequest()->getParam(QuickresponseInterface::ID);
        $result = [];
        if (!$id) {
            $result['error'] = __('Quickresponse identifier is not specified.');
            return $this->resultJsonFactory->create()->setJsonData(json_encode($result));
        }
        try {
            $quickresponse = $this->quickresponseRepository->getById($id);
            $result        = $quickresponse->getData();
        } catch (NoSuchEntityException $exception) {
            $result['error'] = __('This quickresponse no longer exists.');
        }
        return $this->resultJsonFactory->create()->setJsonData(json_encode($result));
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageMe_WebForms::webforms');
    }
}