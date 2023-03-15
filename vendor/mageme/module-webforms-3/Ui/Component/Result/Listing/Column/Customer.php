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

namespace MageMe\WebForms\Ui\Component\Result\Listing\Column;

use Exception;
use MageMe\WebForms\Api\Utility\ExportValueConverterInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Customer extends Column implements ExportValueConverterInterface
{
    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Customer constructor.
     * @param CustomerRegistry $customerRegistry
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        CustomerRegistry   $customerRegistry,
        UrlInterface       $urlBuilder,
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        array              $components = [],
        array              $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder       = $urlBuilder;
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $value = $item['customer_id'];
                try {
                    $customer = $this->customerRegistry->retrieve($value);
                    $html     = '<a href="javascript:void(0)" onclick="window.open(\'' . $this->getCustomerUrl((int)$customer->getId()) . '\',\'_blank\')">' . htmlspecialchars((string)$customer->getName()) . '</a>';
                } catch (Exception $exception) {
                    $html = __('Guest');
                }
                $item[$fieldName] = $html;
            }
        }

        return $dataSource;
    }

    /**
     * @param int $customer_id
     * @return string
     */
    public function getCustomerUrl(int $customer_id): string
    {

        return $this->getUrl('customer/index/edit', ['id' => $customer_id, '_current' => false]);
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function convertExportValue($data)
    {
        if(is_array($data) && !empty($data['customer_id']))
        {
            $customer = $this->customerRegistry->retrieve($data['customer_id']);
            return $customer->getName().' <'.$customer->getEmail().'>';
        }
        return '';
    }
}
