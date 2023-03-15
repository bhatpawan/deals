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

namespace MageMe\WebForms\Controller\Form;


use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Controller\AbstractAction;
use MageMe\WebForms\Model\Repository\FormRepository;
use MageMe\WebForms\Model\ResourceModel\Form;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcherInterface;

class View extends AbstractAction
{
    const COOKIE_NAME = 'section_data_clean';

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;
    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;
    /**
     * @var StoreSwitcherInterface
     */
    protected $storeSwitcher;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var Form
     */
    protected $formResource;

    /**
     * @var FormRepository
     */
    protected $formRepository;

    /**
     * @var Config
     */
    protected $pageConfig;

    /**
     * View constructor.
     * @param Context $context
     * @param Form $formResource
     * @param FormRepository $formRepository
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreManagerInterface $storeManager
     * @param StoreSwitcherInterface $storeSwitcher
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     * @param PageFactory $pageFactory
     * @param Config $pageConfig
     */
    public function __construct(
        Context                  $context,
        Form                     $formResource,
        FormRepositoryInterface  $formRepository,
        UrlInterface             $urlBuilder,
        ScopeConfigInterface     $scopeConfig,
        Registry                 $registry,
        StoreRepositoryInterface $storeRepository,
        StoreManagerInterface    $storeManager,
        StoreSwitcherInterface   $storeSwitcher,
        CookieMetadataFactory    $cookieMetadataFactory,
        CookieManagerInterface   $cookieManager,
        PageFactory              $pageFactory,
        Config                   $pageConfig
    )
    {
        parent::__construct($context, $pageFactory);
        $this->cookieManager         = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->storeSwitcher         = $storeSwitcher;
        $this->storeManager          = $storeManager;
        $this->storeRepository       = $storeRepository;
        $this->registry              = $registry;
        $this->scopeConfig           = $scopeConfig;
        $this->urlBuilder            = $urlBuilder;
        $this->formResource          = $formResource;
        $this->formRepository        = $formRepository;
        $this->pageConfig            = $pageConfig;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function execute()
    {

        if (strpos((string)$this->request->getOriginalPathInfo(), 'webforms/form/view')) {
            return $this->response->setRedirect($this->getNoRouteUrl());
        }

        $targetStoreCode = $this->request->getParam(StoreResolverInterface::PARAM_NAME);
        if ($targetStoreCode) {
            $targetStore = $this->storeRepository->get($targetStoreCode);
            if ($targetStore->getId()) {
                $this->storeManager->setCurrentStore($targetStore);
                $this->storeSwitcher->switch($targetStore, $targetStore, $this->redirect->getRedirectUrl());
                $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                    ->setHttpOnly(false)
                    ->setDuration(15)
                    ->setPath($targetStore->getStorePath());
                $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $targetStore->getCode(), $cookieMetadata);
            }
        }

        $formId = (int)$this->request->getParam(FormInterface::ID);
        if (!$formId || !$this->formResource->entityExists($formId)) {
            return $this->response->setRedirect($this->getNoRouteUrl());
        }
        $form = $this->formRepository->getById($formId, $this->storeManager->getStore()->getId());

        $resultPage = $this->pageFactory->create();
        $resultPage->getLayout()->getBlock('webforms.form')->setForm($form);

        // Set meta
        if ($form->getMetaTitle()) {
            $this->pageConfig->setMetaTitle($form->getMetaTitle());
            $this->pageConfig->getTitle()->set($form->getMetaTitle());
        } else {
            $this->pageConfig->getTitle()->set($form->getTitle());
        }

        if ($form->getMetaKeywords()) {
            $this->pageConfig->setKeywords($form->getMetaKeywords());
        }
        if ($form->getMetaDescription()) {
            $this->pageConfig->setDescription($form->getMetaDescription());
        }

        return $resultPage;
    }

    /**
     * No Route url
     *
     * @return string
     */
    public function getNoRouteUrl(): string
    {
        $defaultNoRouteUrl = $this->scopeConfig->getValue(
            'web/default/no_route',
            ScopeInterface::SCOPE_STORE
        );
        return $this->urlBuilder->getUrl($defaultNoRouteUrl);
    }
}