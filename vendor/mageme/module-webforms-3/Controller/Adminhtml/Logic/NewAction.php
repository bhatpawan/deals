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

use MageMe\WebForms\Api\Data\LogicInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Exception\LocalizedException;

class NewAction extends Action
{
    /**
     * @inheritDoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::save_form';

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * NewAction constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param AccessHelper $accessHelper
     * @param ForwardFactory $resultForwardFactory
     * @param Action\Context $context
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        AccessHelper             $accessHelper,
        ForwardFactory           $resultForwardFactory,
        Action\Context           $context
    )
    {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->accessHelper         = $accessHelper;
        $this->fieldRepository      = $fieldRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $fieldId   = (int)$this->getRequest()->getParam(LogicInterface::FIELD_ID);
        if ($fieldId) {
            $field  = $this->fieldRepository->getById($fieldId);
            $formId = $field->getFormId();
            if ($formId && !$this->accessHelper->isAllowed($formId)) {
                $isAllowed = false;
            }
        }
        return $isAllowed;
    }
}