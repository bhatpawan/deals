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


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\LogicRepositoryInterface;
use MageMe\WebForms\Controller\Adminhtml\AbstractAjaxMassAction;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\ResourceModel\Logic\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;

abstract class AbstractAjaxLogicMassAction extends AbstractAjaxMassAction
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
     * @var LogicRepositoryInterface
     */
    protected $repository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * AbstractAjaxLogicMassAction constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param LogicRepositoryInterface $repository
     * @param CollectionFactory $collectionFactory
     * @param AccessHelper $accessHelper
     * @param Filter $filter
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        LogicRepositoryInterface $repository,
        CollectionFactory        $collectionFactory,
        AccessHelper             $accessHelper,
        Filter                   $filter,
        JsonFactory              $jsonFactory,
        Context                  $context)
    {
        parent::__construct($filter, $jsonFactory, $context);
        $this->accessHelper    = $accessHelper;
        $this->collection      = $collectionFactory->create();
        $this->repository      = $repository;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $fieldId   = (int)$this->getRequest()->getParam(FieldInterface::ID);
        $formId    = (int)$this->getRequest()->getParam(FieldInterface::FORM_ID);
        if ($fieldId) {
            $field  = $this->fieldRepository->getById($this->getRequest()->getParam(FieldInterface::ID));
            $formId = $field->getFormId();
        }
        if ($formId && !$this->accessHelper->isAllowed($formId)) {
            $isAllowed = false;
        }
        return $isAllowed;
    }
}