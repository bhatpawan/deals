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

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Form extends AbstractResolver implements ResolverInterface
{
    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;
    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * @param FormRepositoryInterface $formRepository
     * @param AccessHelper $accessHelper
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        FormRepositoryInterface $formRepository,
        AccessHelper            $accessHelper,
        AuthorizationInterface  $authorization
    ) {
        parent::__construct($authorization);
        $this->accessHelper   = $accessHelper;
        $this->formRepository = $formRepository;
    }

    /**
     * @inerhitDoc
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlAuthorizationException
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->validateInput($args);
        $this->validateAccess($context, $args);
        try {
            $data = $this->formRepository->getDataById($args[FormInterface::ID]);
            return $data['form'];
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }

    /**
     * @inheirtDoc
     * @throws GraphQlInputException
     */
    protected function validateInput(array $args): void
    {
        if (!isset($args[FormInterface::ID])) {
            throw new GraphQlInputException(__('Form id should be specified.'));
        }
        if ((int)$args[FormInterface::ID] < 1) {
            throw new GraphQlInputException(__('Form id must be greater than 0.'));
        }
    }

    /**
     * @inheirtDoc
     * @throws GraphQlAuthorizationException
     */
    protected function validateAdminAccess(ContextInterface $context, array $args): void
    {
        parent::validateAdminAccess($context, $args);
        if (!$this->authorization->isAllowed('MageMe_WebForms::manage_forms')) {
            throw new GraphQlAuthorizationException(__('The consumer isn\'t authorized to access resources.'));
        }
        if (!$this->accessHelper->isAllowed($args[FormInterface::ID])) {
            throw new GraphQlAuthorizationException(__('The consumer isn\'t authorized to access resources.'));
        }
    }

    /**
     * @inheirtDoc
     * @throws GraphQlAuthorizationException
     * @throws NoSuchEntityException
     */
    protected function validateCustomerAccess(ContextInterface $context, array $args): void
    {
        $form = $this->formRepository->getById($args[FormInterface::ID]);
        if (!$form->getIsActive()) {
            throw new GraphQlAuthorizationException(__('Web-form is not active.'));
        }
        if ($form->getIsCustomerAccessLimited()) {
            if (false === $context->getExtensionAttributes()->getIsCustomer()) {
                throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
            }
            if (!in_array($context->getExtensionAttributes()->getCustomerGroupId(), $form->getAccessGroups())) {
                throw new GraphQlAuthorizationException(__('You don\'t have enough permissions to access this content.'));
            }
        }
    }
}