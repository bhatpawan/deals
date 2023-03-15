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

namespace MageMe\WebForms\Controller;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Model\Result;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Result
 * @package MageMe\WebForms\Controller
 */
abstract class ResultAction extends AbstractAction
{
    const ALLOWED = 'allowed';

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

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
     * ResultAction constructor.
     * @param Context $context
     * @param ResultRepositoryInterface $resultRepository
     * @param SessionFactory $sessionFactory
     * @param Registry $registry
     * @param EventManagerInterface $eventManager
     * @param MessageManagerInterface $messageManager
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context                   $context,
        ResultRepositoryInterface $resultRepository,
        SessionFactory            $sessionFactory,
        Registry                  $registry,
        EventManagerInterface     $eventManager,
        MessageManagerInterface   $messageManager,
        PageFactory               $pageFactory
    )
    {
        parent::__construct($context, $pageFactory);
        $this->messageManager   = $messageManager;
        $this->eventManager     = $eventManager;
        $this->registry         = $registry;
        $this->session          = $sessionFactory->create();
        $this->resultRepository = $resultRepository;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _init()
    {
        if (!$this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login to view the form.'));
            $this->session->authenticate();
        }

        $resultId = (int)$this->request->getParam(ResultInterface::ID);
        $result   = $this->resultRepository->getById($resultId);
        $result->addFieldArray();

        $access = new DataObject();
        $access->setData(self::ALLOWED, false);
        if ($result->getCustomerId() == $this->session->getId()) {
            $access->setData(self::ALLOWED, true);
        }

        $this->eventManager->dispatch('webforms_controller_result_access', ['access' => $access, 'result' => $result]);

        if (!$access->getData(self::ALLOWED)) {
            $this->messageManager->addErrorMessage(__('Access denied.'));
            $this->redirect('customer/account');
        }

        $groupId = $this->session->getCustomerGroupId();
        $webform = $result->getForm();
        if (!$webform->getIsActive() || !$webform->getIsCustomerDashboardEnabled() || !in_array($groupId,
                $webform->getDashboardGroups())) {
            $this->redirect('customer/account');
        }
        $this->registry->register('result', $result);
        $this->result = $result;
    }
}
