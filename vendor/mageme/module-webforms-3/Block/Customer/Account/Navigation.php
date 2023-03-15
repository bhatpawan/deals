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

namespace MageMe\WebForms\Block\Customer\Account;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use Magento\Customer\Model\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;


class Navigation extends Template
{
    /**
     * @var array
     */
    protected $links = [];

    /**
     * @var string
     */
    protected $route = 'webforms/customer/account';

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * Navigation constructor.
     * @param FormRepositoryInterface $formRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        FormRepositoryInterface             $formRepository,
        SearchCriteriaBuilder               $searchCriteriaBuilder,
        \Magento\Framework\App\Http\Context $httpContext,
        Template\Context                    $context,
        array                               $data = []
    )
    {
        $this->httpContext           = $httpContext;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->formRepository        = $formRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getBlockTitle(): ?string
    {
        return $this->_storeManager->getStore()->getConfig('webforms/general/customer_navigation_block_title');
    }

    /**
     * @inheritdoc
     * @throws NoSuchEntityException
     */
    protected function _construct()
    {
        parent::_construct();
        if (!$this->isLoggedIn()) {
            return;
        }

        $groupId = $this->getGroupId();
        $storeId = $this->_storeManager->getStore()->getId();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(FormInterface::IS_CUSTOMER_DASHBOARD_ENABLED, 1)
            ->create();
        $forms          = $this->formRepository->getList($searchCriteria, $storeId)->getItems();
        $links          = [];

        foreach ($forms as $form) {
            $accessGroups    = $form->getAccessGroups();
            $dashboardGroups = $form->getDashboardGroups();
            if (
                (($form->getIsCustomerAccessLimited() && in_array($groupId, $accessGroups) || !$form->getIsCustomerAccessLimited())
                    && $form->getIsActive()
                    && $form->getIsCustomerDashboardEnabled()
                    && in_array($groupId, $dashboardGroups))
            ) {
                $active  = $this->getRequest()->getParam(FormInterface::ID) == $form->getId();
                $links[] = new DataObject([
                    'label' => $form->getName(),
                    'url' => $this->getFormUrl($form),
                    'active' => $active
                ]);
            }
        }
        $this->links = $links;
    }

    /**
     * @return mixed|null
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * @return mixed|null
     */
    public function getGroupId()
    {
        return $this->httpContext->getvalue(Context::CONTEXT_GROUP);
    }

    /**
     * @param $form
     * @return string
     */
    public function getFormUrl($form): string
    {
        return $this->getUrl($this->route, [FormInterface::ID => $form->getId()]);
    }
}
