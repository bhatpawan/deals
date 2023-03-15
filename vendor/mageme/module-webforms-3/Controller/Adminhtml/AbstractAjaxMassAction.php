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
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;

abstract class AbstractAjaxMassAction extends Action
{
    /**
     * Action name for messages
     */
    const ACTION = '';

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var AbstractDb
     */
    protected $collection;

    /**
     * AbstractAjaxMassAction constructor.
     * @param Filter $filter
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        Filter      $filter,
        JsonFactory $jsonFactory,
        Context     $context
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->filter      = $filter;
    }

    /**
     * @inheritDoc
     */
    public function execute(): Json
    {
        $error = false;
        try {
            $collection = $this->filter->getCollection($this->getCollection());
            if ($collection->getSize() < 1) {
                throw new NoSuchEntityException();
            }
            $message = $this->action($collection);
        } catch (NoSuchEntityException $e) {
            $message = __('Please select item(s).');
            $error   = true;
        } catch (LocalizedException $e) {
            $message = __($e->getMessage());
            $error   = true;
        } catch (Exception $e) {
            $message = __('We can\'t mass ' . static::ACTION . ' the record(s) right now.');
            $errorMessage = $e->getMessage();
            $error   = true;
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
                'errorMessage' => $errorMessage ?? ''
            ]
        );

        return $resultJson;
    }

    /**
     * @return AbstractDb
     */
    protected function getCollection(): AbstractDb
    {
        return $this->collection;
    }

    /**
     * Action on collection items
     *
     * @param AbstractDb $collection
     * @return Phrase
     */
    abstract protected function action(AbstractDb $collection): Phrase;
}