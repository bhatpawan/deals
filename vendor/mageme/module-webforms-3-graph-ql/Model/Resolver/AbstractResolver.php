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

namespace MageMe\WebFormsGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

abstract class AbstractResolver
{
    const ADMIN_RESOURCE = 'MageMe_WebForms::api_info';

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param AuthorizationInterface $authorization
     */
    public function __construct(AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @param array $args
     * @return void
     * @throws GraphQlInputException
     */
    abstract protected function validateInput(array $args): void;

    /**
     * @param ContextInterface $context
     * @param array $args
     * @return void
     * @throws GraphQlAuthorizationException
     */
    protected function validateAccess(ContextInterface $context, array $args): void {
        if ($context->getUserType() === UserContextInterface::USER_TYPE_ADMIN
            || $context->getUserType() === UserContextInterface::USER_TYPE_INTEGRATION
        ) {
            $this->validateAdminAccess($context, $args);
            return;
        }
        $this->validateCustomerAccess($context, $args);
    }

    /**
     * @param ContextInterface $context
     * @param array $args
     * @return void
     */
    abstract protected function validateCustomerAccess(ContextInterface $context, array $args): void;

    /**
     * @param ContextInterface $context
     * @param array $args
     * @return void
     * @throws GraphQlAuthorizationException
     */
    protected function validateAdminAccess(ContextInterface $context, array $args): void {
        if ($context->getUserId() === 0) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (!$this->authorization->isAllowed(static::ADMIN_RESOURCE)) {
            throw new GraphQlAuthorizationException(__('The consumer isn\'t authorized to access resources.'));
        }
    }

}