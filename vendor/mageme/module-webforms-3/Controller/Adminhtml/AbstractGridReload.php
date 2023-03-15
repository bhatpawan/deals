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

namespace MageMe\WebForms\Controller\Adminhtml;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\Form;
use MageMe\WebForms\Model\FormFactory;
use MageMe\WebForms\Model\Repository\FormRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;

abstract class AbstractGridReload extends Action
{
    /**
     * @var Registry
     */
    protected $registry = null;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var FormRepository
     */
    protected $formRepository;

    /**
     * AbstractGridReload constructor.
     * @param FormRepository $formRepository
     * @param FormFactory $formFactory
     * @param AccessHelper $accessHelper
     * @param LayoutFactory $resultLayoutFactory
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        FormRepository $formRepository,
        FormFactory    $formFactory,
        AccessHelper   $accessHelper,
        LayoutFactory  $resultLayoutFactory,
        Registry       $registry,
        Context        $context)
    {
        parent::__construct($context);
        $this->registry            = $registry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->accessHelper        = $accessHelper;
        $this->formFactory         = $formFactory;
        $this->formRepository      = $formRepository;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->_initForm();
        return $this->resultLayoutFactory->create();
    }

    /**
     * @return FormInterface|Form
     * @throws NoSuchEntityException
     */
    protected function _initForm()
    {
        $formId = (int)$this->getRequest()->getParam(FormInterface::ID);
        $form   = !$formId ? $this->formFactory->create() : $this->formRepository->getById($formId);
        $this->registry->register('webforms_form', $form);
        return $form;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed(): bool
    {
        $formId = (int)$this->getRequest()->getParam(FormInterface::ID);
        if ($formId) {
            return $this->accessHelper->isAllowed($formId);
        }
        return $this->_authorization->isAllowed('MageMe_WebForms::manage_forms');
    }
}
