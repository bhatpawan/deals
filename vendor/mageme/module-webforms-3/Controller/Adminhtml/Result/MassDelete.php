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

use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\RepositoryInterface;
use MageMe\WebForms\Controller\Adminhtml\AbstractMassDelete;
use MageMe\WebForms\Helper\Form\AccessHelper;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

class MassDelete extends AbstractMassDelete
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::edit_result';
    const ID_FIELD = 'selected';
    const REDIRECT_URL = 'webforms/result/index';

    /**
     * @inheritdoc
     */
    protected $redirect_params = ['_current' => true];

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassDelete constructor.
     * @param CollectionFactory $collectionFactory
     * @param RepositoryInterface $repository
     * @param AccessHelper $accessHelper
     * @param Context $context
     */
    public function __construct(
        CollectionFactory   $collectionFactory,
        RepositoryInterface $repository,
        AccessHelper        $accessHelper,
        Context             $context
    )
    {
        parent::__construct($repository, $accessHelper, $context);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function getIds(): array
    {
        if ($this->getRequest()->getParam('excluded') !== 'false') {
            return parent::getIds();
        }
        $Ids       = [];
        $webformId = (int)$this->getRequest()->getParam(ResultInterface::FORM_ID);
        if ($webformId) {
            $filters    = $this->getRequest()->getParam('filters');
            $collection = $this->collectionFactory->create();
            $collection->addFilter(ResultInterface::FORM_ID, $webformId);
            foreach ($filters as $fieldName => $value) {
                if (strstr((string)$fieldName, 'field_')) {
                    $fieldID = (int)str_replace('field_', '', (string)$fieldName);
                    $collection->addFieldFilter($fieldID, $value);
                }
            }
            if (isset($filters[ResultInterface::CREATED_AT])) {
                $from = $to = false;
                if (!empty($filters[ResultInterface::CREATED_AT]['from'])) {
                    $from = date('Y-m-d', strtotime($filters[ResultInterface::CREATED_AT]['from'])) . ' 00:00:00';
                }
                if (!empty($filters[ResultInterface::CREATED_AT]['to'])) {
                    $to = date('Y-m-d', strtotime($filters[ResultInterface::CREATED_AT]['to'])) . ' 23:59:59';
                }
                if ($from) {
                    $collection->addFieldToFilter(ResultInterface::CREATED_AT, ['gteq' => $from]);
                }
                if ($to) {
                    $collection->addFieldToFilter(ResultInterface::CREATED_AT, ['lteq' => $to]);
                }
            }
            if (isset($filters[ResultInterface::ID])) {
                $from = $to = false;
                if (!empty($filters[ResultInterface::ID]['from'])) {
                    $from = $filters[ResultInterface::ID]['from'];
                }
                if (!empty($filters[ResultInterface::ID]['to'])) {
                    $to = $filters[ResultInterface::ID]['to'];
                }
                if ($from) {
                    $collection->addFieldToFilter(ResultInterface::ID, ['gteq' => $from]);
                }
                if ($to) {
                    $collection->addFieldToFilter(ResultInterface::ID, ['lteq' => $to]);
                }
            }
            if (isset($filters[ResultInterface::APPROVED])) {
                $collection->addFilter(ResultInterface::APPROVED, $filters[ResultInterface::APPROVED]);
            }
            if (isset($filters['customer'])) {
                $collection->addFieldToFilter('customer', ['like' => '%' . $filters['customer'] . '%']);
            }
            foreach ($collection as $result) {
                $Ids[] = $result->getId();
            }
        }
        return $Ids;
    }
}