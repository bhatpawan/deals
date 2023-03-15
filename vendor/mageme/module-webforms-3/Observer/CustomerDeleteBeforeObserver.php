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

namespace MageMe\WebForms\Observer;

use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class CustomerDeleteBeforeObserver implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @param ResultRepositoryInterface $resultRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        ScopeConfigInterface      $scopeConfig
    )
    {
        $this->scopeConfig      = $scopeConfig;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $customer = $observer->getCustomer();

        /** @var ResultInterface[] $results */
        $results  = $this->resultRepository->getListByCustomerId($customer->getId())->getItems();
        $isPurged = (bool)$this->scopeConfig->getValue('webforms/data_cleanup/purge_data_on_account_delete', ScopeInterface::SCOPE_WEBSITE);
        if ($isPurged) {
            foreach ($results as $result) {
                $this->resultRepository->delete($result);
            }
        } else {
            foreach ($results as $result) {
                $result->setCustomerId(null);
                $this->resultRepository->save($result);
            }
        }
    }
}