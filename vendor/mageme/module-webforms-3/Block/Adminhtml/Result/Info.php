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
use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

class Info extends Container
{
    /**
     * @var ResultInterface
     */
    protected $result;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var string
     */
    protected $_template = 'result/reply/info.phtml';

    /**
     * Info constructor.
     * @param GroupRepositoryInterface $groupRepository
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        Context                  $context,
        array                    $data = [])
    {
        parent::__construct($context, $data);
        $this->groupRepository = $groupRepository;
    }

    /**
     * @return string
     */
    public function getCustomerViewUrl(): string
    {
        $result = $this->getResult();
        if (!$result->getCustomerId()) {
            return '';
        }

        return $this->getUrl('customer/index/edit', ['id' => $result->getCustomerId()]);
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
    public function setResult(ResultInterface $result): Info
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Check if is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode(): bool
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * @return Phrase|string|null
     * @throws NoSuchEntityException
     */
    public function getOrderStoreName()
    {
        $result = $this->getResult();
        if ($result) {
            $storeId = $result->getStoreId();

            $store = $this->_storeManager->getStore($storeId);
            if ($store->getId() === null) {
                return __('[deleted]');
            }
            $name = [$store->getWebsite()->getName(), $store->getGroup()->getName(), $store->getName()];
            return implode('<br>', $name);
        }

        return null;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getCustomerGroupName(): string
    {
        if ($this->getResult()) {
            $customer = $this->getResult()->getCustomer();

            $customerGroupId = null;
            if ($customer)
                $customerGroupId = $customer->getGroupId();
            try {
                if ($customerGroupId !== null) {
                    return $this->groupRepository->getById($customerGroupId)->getCode();
                }
            } catch (NoSuchEntityException $e) {
                return '';
            }
        }

        return '';
    }

    /**
     * @return bool
     */
    public function shouldDisplayCustomerIp(): bool
    {
        return (bool)$this->_scopeConfig->getValue('webforms/general/collect_customer_ip');
    }

    /**
     * Get extended result data array [['label' => string , 'value' => string], ...]
     * @return array
     */
    public function getExtendedData(): array
    {
        return [];
    }
}
