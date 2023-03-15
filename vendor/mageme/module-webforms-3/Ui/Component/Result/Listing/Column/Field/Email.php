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

namespace MageMe\WebForms\Ui\Component\Result\Listing\Column\Field;

use Exception;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManager;
use Magento\Ui\Component\Listing\Columns\Column;


class Email extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Email constructor.
     * @param CustomerRegistry $customerRegistry
     * @param StoreManager $storeManager
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        CustomerRegistry   $customerRegistry,
        StoreManager       $storeManager,
        UrlInterface       $urlBuilder,
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        array              $components = [],
        array              $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder       = $urlBuilder;
        $this->storeManager     = $storeManager;
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $value = empty($item[$fieldName]) ? false : $item[$fieldName];
                if ($value && isset($item['result_store_id'])) {
                    try {
                        $website   = $this->storeManager->getStore($item['result_store_id'])->getWebsite();
                        $websiteId = $website ? $website->getId() : false;
                    } catch (LocalizedException $e) {
                        $websiteId = false;
                    }
                    $item[$fieldName] = $this->prepareItem($value, $websiteId);
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $value
     * @param $websiteId
     * @return string
     */
    protected function prepareItem($value, $websiteId): string
    {
        try {
            $customer = $this->customerRegistry->retrieveByEmail($value, $websiteId);
            return htmlentities((string)$value) . ' [<a href="javascript:void(0)" onclick="window.open(\'' . $this->getCustomerUrl($customer->getId()) . '\',\'_blank\')">' . htmlentities($customer->getName()) . '</a>]';
        } catch (Exception $e) {
            return htmlentities((string)$value);
        }
    }

    public function getCustomerUrl($customerId): string
    {
        return $this->urlBuilder->getUrl('customer/index/edit', ['id' => $customerId, '_current' => false]);
    }
}
