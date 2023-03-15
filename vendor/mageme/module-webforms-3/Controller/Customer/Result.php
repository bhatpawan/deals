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

use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Config\Options\Result\Permission;
use MageMe\WebForms\Controller\AbstractAction;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Result extends AbstractAction
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * Result constructor.
     * @param Context $context
     * @param ResultRepositoryInterface $resultRepository
     * @param SessionFactory $sessionFactory
     * @param Registry $registry
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context                   $context,
        ResultRepositoryInterface $resultRepository,
        SessionFactory            $sessionFactory,
        Registry                  $registry,
        PageFactory               $pageFactory
    )
    {
        parent::__construct($context, $pageFactory);
        $this->registry         = $registry;
        $this->session          = $sessionFactory->create();
        $this->resultRepository = $resultRepository;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->session->authenticate($this);

        $resultId = (int)$this->request->getParam(ResultInterface::ID);
        $result   = $this->resultRepository->getById($resultId);
        if ($result->getCustomerId() != $this->session->getCustomerId()) {
            return $this->redirect('customer/account');
        }
        $groupId = $this->session->getCustomerGroupId();
        $form    = $result->getForm();
        if (!$form->getIsActive()
            || !$form->getIsCustomerDashboardEnabled()
            || !in_array($groupId, $form->getDashboardGroups())
            || !in_array(Permission::VIEW, $form->getCustomerResultPermissions())
        ) {
            return $this->redirect('customer/account');
        }

        $this->registry->register('webforms_result', $result);

        $resultPage = $this->pageFactory->create();

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $resultPage->getLayout()->getBlock('page.main.title')->setPageTitle($result->getSubject());
        $resultPage->getConfig()->getTitle()->set($result->getSubject());

        return $resultPage;
    }
}
