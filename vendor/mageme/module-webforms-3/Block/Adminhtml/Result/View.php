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

namespace MageMe\WebForms\Block\Adminhtml\Result;


use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Backend\Block\Widget\Container;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;

class View extends Container
{
    /** @var ResultInterface */
    protected $result;

    /**
     * @var array
     */
    protected $options = [];

    protected $_template = 'MageMe_WebForms::result/view.phtml';

    /**
     * @var string
     */
    protected $_nameInLayout = 'webforms_result_view';

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
}
