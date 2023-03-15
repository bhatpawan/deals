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

namespace MageMe\WebForms\Controller\Adminhtml\Result;


use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Controller\Adminhtml\AbstractAjaxMassAction;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Ui\Component\MassAction\Filter;

abstract class AbstractAjaxResultMassAction extends AbstractAjaxMassAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::manage_forms';

    /**
     * @var ResultRepositoryInterface
     */
    protected $repository;

    /**
     * AbstractAjaxResultMassAction constructor.
     * @param ResultRepositoryInterface $repository
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        ResultRepositoryInterface $repository,
        CollectionFactory         $collectionFactory,
        Filter                    $filter,
        JsonFactory               $jsonFactory,
        Context                   $context
    )
    {
        parent::__construct($filter, $jsonFactory, $context);
        $this->collection = $collectionFactory->create();
        $this->repository = $repository;
    }
}