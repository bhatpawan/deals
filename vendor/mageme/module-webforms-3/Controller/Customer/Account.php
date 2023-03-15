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

namespace MageMe\WebForms\Controller\Customer;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Controller\AbstractAction;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManager;

class Account extends AbstractAction
{
    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * Account constructor.
     * @param Context $context
     * @param FormRepositoryInterface $formRepository
     * @param SessionFactory $sessionFactory
     * @param Registry $registry
     * @param StoreManager $storeManager
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context                 $context,
        FormRepositoryInterface $formRepository,
        SessionFactory          $sessionFactory,
        Registry                $registry,
        StoreManager            $storeManager,
        PageFactory             $pageFactory
    )
    {
        parent::__construct($context, $pageFactory);
        $this->storeManager    = $storeManager;
        $this->registry        = $registry;
        $this->customerSession = $sessionFactory->create();
        $this->formRepository  = $formRepository;
    }

    /**
     * @inheritDoc
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $formId  = (int)$this->request->getParam(FormInterface::ID);
        $groupId = $this->customerSession->getCustomerGroupId();
        $form    = $this->formRepository->getById($formId, $this->storeManager->getStore()->getId());

        $dashboardGroups = [];
        if (is_array($form->getDashboardGroups())) {
            $dashboardGroups = $form->getDashboardGroups();
        }

        if (!$form->getIsActive() || !$form->getIsCustomerDashboardEnabled() || !in_array($groupId, $dashboardGroups)) {
            return $this->redirect('customer/account');
        }

        $this->registry->register('webforms_form', $form);
        $this->registry->register('customer_id', $this->customerSession->getCustomerId());

        $resultPage = $this->pageFactory->create();

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $resultPage->getLayout()->getBlock('page.main.title')->setPageTitle($form->getName());
        $resultPage->getConfig()->getTitle()->set($form->getName());
        return $resultPage;
    }
}
