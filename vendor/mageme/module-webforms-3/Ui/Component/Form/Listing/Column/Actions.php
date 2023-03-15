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

namespace MageMe\WebForms\Ui\Component\Form\Listing\Column;


use MageMe\WebForms\Api\Data\FormInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManager;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
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
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Url
     */
    protected $frontendUrlBuilder;

    /**
     * Actions constructor.
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManager $storeManager
     * @param Url $frontendUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct
    (
        RequestInterface     $request,
        ScopeConfigInterface $scopeConfig,
        StoreManager         $storeManager,
        Url                  $frontendUrlBuilder,
        UrlInterface         $urlBuilder,
        ContextInterface     $context,
        UiComponentFactory   $uiComponentFactory,
        array                $components = [],
        array                $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder         = $urlBuilder;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->storeManager       = $storeManager;
        $this->scopeConfig        = $scopeConfig;
        $this->request            = $request;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {

                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->getEditURL($item[FormInterface::ID]),
                    'label' => __('Edit Form'),
                    'hidden' => false,
                ];

                $item[$this->getData('name')]['results'] = [
                    'href' => $this->getResultUrl($item[FormInterface::ID]),
                    'label' => __('Browse Results'),
                    'hidden' => false,
                ];

                $item[$this->getData('name')]['export'] = [
                    'href' => $this->getExportURL($item[FormInterface::ID]),
                    'label' => __('Export Form'),
                    'hidden' => false,
                ];

                if (!empty($item[FormInterface::URL_KEY])) {
                    $item[$this->getData('name')]['view'] = [
                        'href' => $this->getViewURL($item),
                        'label' => __('View'),
                        'target' => '_blank',
                        'hidden' => false,
                    ];
                } elseif ($this->storeManager->getStore()->getConfig('webforms/general/preview_enabled')) {
                    $item[$this->getData('name')]['preview'] = [
                        'href' => $this->getPreviewURL($item),
                        'label' => __('Preview'),
                        'target' => '_blank',
                        'hidden' => false,
                    ];
                }
            }
        }

        return $dataSource;
    }

    private function getEditURL($id): string
    {
        return $this->urlBuilder->getUrl(
            'webforms/form/edit',
            [
                'store' => $this->request->getParam('store'),
                FormInterface::ID => $id
            ]
        );
    }

    private function getResultUrl($id): string
    {
        return $this->urlBuilder->getUrl(
            'webforms/result',
            [
                'store' => $this->request->getParam('store'),
                FormInterface::ID => $id
            ]
        );
    }

    /**
     * Get export URL
     *
     * @param $id
     * @return string
     */
    private function getExportURL($id): string
    {
        return $this->urlBuilder->getUrl(
            'webforms/form/export',
            [
                '_current' => false,
                FormInterface::ID => $id
            ]
        );
    }

    /**
     * Get view URL
     *
     * @param $item
     * @return string
     * @throws NoSuchEntityException
     */
    private function getViewURL($item): string
    {
        $store = $this->storeManager->getStore($this->scopeConfig->getValue('webforms/general/preview_store'));
        $this->frontendUrlBuilder->setScope($store->getId());
        return $this->frontendUrlBuilder->getUrl(
            $item[FormInterface::URL_KEY],
            [
                '_current' => false,
                '_query' => '___store=' . $store->getCode(),
                '_nosid' => true
            ]
        );
    }

    /**
     * Get preview URL
     *
     * @param $item
     * @return string
     * @throws NoSuchEntityException
     */
    private function getPreviewURL($item): string
    {
        $store = $this->storeManager->getStore($this->scopeConfig->getValue('webforms/general/preview_store'));
        $this->frontendUrlBuilder->setScope($store->getId());
        return $this->frontendUrlBuilder->getUrl(
            'webforms/form/preview',
            [
                '_current' => false,
                FormInterface::ID => $item[FormInterface::ID],
                '_query' => '___store=' . $store->getCode(),
                '_nosid' => true
            ]
        );
    }
}
