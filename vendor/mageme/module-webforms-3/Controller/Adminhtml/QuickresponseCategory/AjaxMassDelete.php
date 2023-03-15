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


use MageMe\WebForms\Api\QuickresponseCategoryRepositoryInterface;
use MageMe\WebForms\Controller\Adminhtml\AbstractAjaxMassAction;
use MageMe\WebForms\Model\ResourceModel\QuickresponseCategory\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;

class AjaxMassDelete extends AbstractAjaxMassAction
{
    const ADMIN_RESOURCE = 'MageMe_WebForms::quickresponse';
    const ACTION = 'delete';

    /**
     * @var QuickresponseCategoryRepositoryInterface
     */
    protected $repository;

    /**
     * AjaxMassDelete constructor.
     * @param QuickresponseCategoryRepositoryInterface $quickresponseRepository
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        QuickresponseCategoryRepositoryInterface $quickresponseRepository,
        CollectionFactory                        $collectionFactory,
        Filter                                   $filter,
        JsonFactory                              $jsonFactory,
        Context                                  $context)
    {
        parent::__construct($filter, $jsonFactory, $context);
        $this->collection = $collectionFactory->create();
        $this->repository = $quickresponseRepository;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException|CouldNotDeleteException
     */
    protected function action(AbstractDb $collection): Phrase
    {
        foreach ($collection as $item) {
            $model = $this->repository->getById($item->getId());
            $this->repository->delete($model);
        }
        return __('A total of %1 record(s) have been deleted.', $collection->getSize());
    }
}