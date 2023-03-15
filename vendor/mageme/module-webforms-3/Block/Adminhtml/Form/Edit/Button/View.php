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

namespace MageMe\WebForms\Block\Adminhtml\Form\Edit\Button;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Block\Adminhtml\Common\Button\Generic;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Url;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Store\Model\StoreManager;

class View extends Generic
{
    const CONFIG_PREVIEW = 'webforms/general/preview_store';

    /**
     * @var Url
     */
    private $frontendUrlBuilder;
    /**
     * @var StoreManager
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var FormRepositoryInterface
     */
    private $formRepository;

    /**
     * View constructor.
     * @param FormRepositoryInterface $formRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManager $storeManager
     * @param Url $frontendUrlBuilder
     * @param RequestInterface $request
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        ScopeConfigInterface    $scopeConfig,
        StoreManager            $storeManager,
        Url                     $frontendUrlBuilder,
        RequestInterface        $request,
        Registry                $registry,
        Context                 $context)
    {
        parent::__construct($request, $registry, $context);
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->storeManager       = $storeManager;
        $this->scopeConfig        = $scopeConfig;
        $this->formRepository     = $formRepository;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getButtonData(): array
    {
        $formId = (int)$this->request->getParam(FormInterface::ID);
        if (!$formId) {
            return [];
        }
        $form = $this->formRepository->getById($formId);
        if(!$form->getUrlKey() && !$this->scopeConfig->getValue('webforms/general/preview_enabled'))
            return [];

        return [
            'label' => $form->getUrlKey() ? __('View') : __('Preview'),
            'on_click' => sprintf("window.open('%s');", $form->getUrlKey() ? $this->getViewURL($form) : $this->getPreviewURL($form)),
            'class' => 'view action-secondary',
            'sort_order' => 30
        ];
    }

    /**
     * Get view URL
     *
     * @param FormInterface $form
     * @return string
     * @throws NoSuchEntityException
     */
    private function getViewURL(FormInterface $form): string
    {
        $store = $this->storeManager->getStore($this->scopeConfig->getValue(self::CONFIG_PREVIEW));
        $this->frontendUrlBuilder->setScope($store->getId());
        return $this->frontendUrlBuilder->getUrl(
            $form->getUrlKey(),
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
     * @param FormInterface $form
     * @return string
     * @throws NoSuchEntityException
     */
    private function getPreviewURL(FormInterface $form): string
    {
        $store = $this->storeManager->getStore($this->scopeConfig->getValue(self::CONFIG_PREVIEW));
        $this->frontendUrlBuilder->setScope($store->getId());
        return $this->frontendUrlBuilder->getUrl(
            'webforms/form/preview',
            [
                '_current' => false,
                FormInterface::ID => $form->getId(),
                '_query' => '___store=' . $store->getCode(),
                '_nosid' => true
            ]
        );
    }
}