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

namespace MageMe\WebForms\Block\Result;

use MageMe\Core\Helper\DateHelper;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\Form;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Store\Api\Data\StoreInterface;

class View extends Template
{
    /**
     * @var string
     */
    protected $recipient = 'admin';

    /** @var ResultInterface */
    protected $result;

    /**
     * @var DateHelper
     */
    protected $dateHelper;


    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $_template = 'MageMe_WebForms::result/html.phtml';

    /**
     * @param Template\Context $context
     * @param DateHelper $dateHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DateHelper       $dateHelper,
        array            $data = [])
    {
        parent::__construct($context, $data);
        $this->dateHelper = $dateHelper;
    }

    /**
     * @return false|FormInterface|Form
     */
    public function getForm()
    {
        if ($this->result) {
            return $this->result->getForm();
        }
        return false;
    }

    /**
     * @param string $recipient
     * @return View
     */
    public function setRecipient(string $recipient): View
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return View
     */
    public function setOptions(array $options): View
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array|int|mixed|null
     */
    public function getImageWidth()
    {
        return $this->getData('image_width') ?: 200;
    }

    /**
     * @return array|int|mixed|null
     */
    public function getImageHeight()
    {
        return $this->getData('image_height') ?: 200;
    }

    /**
     * @return array|mixed|null
     */
    public function getImageLink()
    {
        return $this->getData('image_link');
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        $result = $this->getResult();
        return $this->_storeManager->getStore($result->getStoreId());
    }

    /**
     * @return ResultInterface
     */
    public function getResult(): ResultInterface
    {
        return $this->result;
    }

    /**
     * @param ResultInterface $result
     * @return $this
     */
    public function setResult(ResultInterface $result): View
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Is single store mode enabled.
     *
     * @return bool
     */
    public function isSingleStoreMode(): bool
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
