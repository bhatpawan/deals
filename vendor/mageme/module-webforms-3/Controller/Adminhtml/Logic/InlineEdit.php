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


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\RepositoryInterface;
use MageMe\WebForms\Controller\Adminhtml\AbstractInlineEdit;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends AbstractInlineEdit
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
     * @param AccessHelper $accessHelper
     * @param RepositoryInterface $repository
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        AccessHelper        $accessHelper,
        RepositoryInterface $repository,
        JsonFactory         $jsonFactory,
        Context             $context
    )
    {
        parent::__construct($repository, $jsonFactory, $context);
        $this->accessHelper = $accessHelper;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $formId    = (int)$this->getRequest()->getParam(FormInterface::ID);
        if ($formId && !$this->accessHelper->isAllowed($formId)) {
            $isAllowed = false;
        }
        return $isAllowed;
    }
}