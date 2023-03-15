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

namespace MageMe\WebForms\Helper;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MailHelper
{
    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $customerSessionFactory;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param SessionFactory $sessionFactory
     * @param FieldRepositoryInterface $fieldRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SessionFactory           $sessionFactory,
        FieldRepositoryInterface $fieldRepository,
        SearchCriteriaBuilder    $searchCriteriaBuilder,
        ScopeConfigInterface     $scopeConfig
    )
    {
        $this->customerSessionFactory = $sessionFactory;
        $this->scopeConfig            = $scopeConfig;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->fieldRepository        = $fieldRepository;
    }

    /**
     * @param ResultInterface $result
     * @return bool|mixed|string|null
     */
    public function getReplyToForAdmin(ResultInterface $result)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FieldInterface::FORM_ID, $result->getFormId())
            ->addFilter(FieldInterface::TYPE, 'email')
            ->create();
        $fields         = $this->fieldRepository->getList($searchCriteria, $result->getStoreId())->getItems();
        $emailField     = is_array($fields) ? reset($fields) : false;
        $replyTo        = false;
        if ($emailField) {
            foreach ($result->getData() as $key => $value) {
                if ($key == 'field_' . $emailField->getId()) {
                    $replyTo = $value;
                }
            }
        }
        if (!$replyTo) {
            if ($this->getCustomerSession()->isLoggedIn()) {
                $replyTo = $this->getCustomerSession()->getCustomer()->getEmail();
            } else {
                $replyTo = $this->scopeConfig->getValue('trans_email/ident_general/email', $result->getScope(),
                    $result->getStoreId());
            }
        }
        return $replyTo;
    }

    private function getCustomerSession(): Session
    {
        if ($this->customerSession === null) {
            $this->customerSession = $this->customerSessionFactory->create();
        }

        return $this->customerSession;
    }

    /**
     * @param ResultInterface $result
     * @return array|mixed|string|null
     */
    public function getReplyToForCustomer(ResultInterface $result)
    {
        $form = $result->getForm();
        if ($form->getCustomerNotificationReplyTo()) {
            $replyTo = $form->getCustomerNotificationReplyTo();
        } elseif ($this->scopeConfig->getValue('webforms/email/customer_notification_reply_to', $result->getScope(),
            $result->getStoreId())) {
            $replyTo = $this->scopeConfig->getValue('webforms/email/customer_notification_reply_to', $result->getScope(),
                $result->getStoreId());
        } else {
            $replyTo = $this->scopeConfig->getValue('trans_email/ident_general/email', $result->getScope(),
                $result->getStoreId());
        }
        return $replyTo;
    }
}
