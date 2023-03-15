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


use Exception;
use MageMe\WebForms\Api\RepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

abstract class AbstractInlineEdit extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * InlineEdit constructor.
     * @param RepositoryInterface $repository
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        RepositoryInterface $repository,
        JsonFactory         $jsonFactory,
        Context             $context
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->repository  = $repository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $error    = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $items = $this->getRequest()->getParam('items', []);
            if (!count($items)) {
                $messages[] = __('Please correct the data sent.');
                $error      = true;
            } else {
                foreach ($items as $id => $data) {
                    try {
                        $model = $this->repository->getById($id);
                        $model->setData(array_merge($model->getData(), $data));
                        $this->repository->save($model);
                    } catch (Exception $e) {
                        $messages[] = '[Row ID: ' . $id . '] ' . __($e->getMessage());
                        $error      = true;
                    }
                }
            }
        }

        return $this->jsonFactory->create()->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}