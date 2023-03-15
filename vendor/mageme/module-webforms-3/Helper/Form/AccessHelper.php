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

namespace MageMe\WebForms\Helper\Form;


use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory;
use Magento\Backend\Model\Authorization\RoleLocator;
use Magento\Framework\AuthorizationInterface;

class AccessHelper
{
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;
    /**
     * @var CollectionFactory
     */
    protected $rulesCollectionFactory;
    /**
     * @var RoleLocator
     */
    protected $roleLocator;

    /**
     * @param RoleLocator $roleLocator
     * @param CollectionFactory $rulesCollectionFactory
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        RoleLocator            $roleLocator,
        CollectionFactory      $rulesCollectionFactory,
        AuthorizationInterface $authorization
    )
    {
        $this->authorization          = $authorization;
        $this->rulesCollectionFactory = $rulesCollectionFactory;
        $this->roleLocator            = $roleLocator;
    }

    /**
     * @param int $formId
     * @return bool
     */
    public function isAllowed(int $formId): bool
    {
        if ($this->authorization->isAllowed('Magento_Backend::all')) {
            return true;
        }

        if (!$formId) {
            return false;
        }

        $collection = $this->rulesCollectionFactory->create()
            ->addFilter('role_id', $this->roleLocator->getAclRoleId())
            ->addFilter('resource_id', 'MageMe_WebForms::form' . $formId)
            ->addFilter('permission', 'allow');

        return (bool)$collection->count();
    }
}