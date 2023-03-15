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
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Config\Options\Result\Permission;
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

class Results extends AbstractResolver implements ResolverInterface
{
    /**
     * @var AccessHelper
     */
    private $accessHelper;
    /**
     * @var FormRepositoryInterface
     */
    private $formRepository;
    /**
     * @var ResultRepositoryInterface
     */
    private $resultRepository;

    /**
     * @param ResultRepositoryInterface $resultRepository
     * @param FormRepositoryInterface $formRepository
     * @param AccessHelper $accessHelper
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        FormRepositoryInterface   $formRepository,
        AccessHelper              $accessHelper,
        AuthorizationInterface    $authorization
    ) {
        parent::__construct($authorization);
        $this->accessHelper     = $accessHelper;
        $this->formRepository   = $formRepository;
        $this->resultRepository = $resultRepository;
    }

    /**
     * @inerhitDoc
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlAuthorizationException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $this->validateInput($args);
        if (isset($args['filter'][ResultInterface::ID])) {
            try {
                $result                  = $this->resultRepository->getById($args['filter'][ResultInterface::ID]);
                $args[FormInterface::ID] = $result->getFormId();
                $this->validateAccess($context, $args);
                if ($context->getExtensionAttributes()->getIsCustomer()) {
                    if ($result->getCustomerId() != $context->getUserId()) {
                        throw new GraphQlAuthorizationException(__('The consumer isn\'t authorized to access resources.'));
                    }
                }
                $data = $this->resultRepository->getDataById($args['filter'][ResultInterface::ID]);
                return [
                    'items' => [
                        $data['result']
                    ]
                ];
            } catch (NoSuchEntityException $e) {
                throw new GraphQlNoSuchEntityException(__($e->getMessage()),
                    $e);
            }
        } else {
            try {
                $args[FormInterface::ID] = $args['filter'][ResultInterface::FORM_ID];
                $this->validateAccess($context, $args);
                $items = $context->getExtensionAttributes()->getIsCustomer() ? $this->formRepository->getResultsById($args['filter'][ResultInterface::FORM_ID],
                    $context->getUserId()) :
                    $this->formRepository->getResultsById($args['filter'][ResultInterface::FORM_ID]);
                return [
                    'items' => $items
                ];
            } catch (NoSuchEntityException $e) {
                throw new GraphQlNoSuchEntityException(__($e->getMessage()),
                    $e);
            }
        }
    }

    /**
     * @inheirtDoc
     * @throws GraphQlInputException
     */
    protected function validateInput(array $args): void
    {
        if (!isset($args['filter'])) {
            throw new GraphQlInputException(
                __('filter input argument is required.')
            );
        }
        if (!isset($args['filter'][ResultInterface::ID]) && !isset($args['filter'][ResultInterface::FORM_ID])) {
            throw new GraphQlInputException(__('filter %1 or %2 should be specified', ResultInterface::ID,
                ResultInterface::FORM_ID));
        }
        if (isset($args['filter'][ResultInterface::ID]) && isset($args['filter'][ResultInterface::FORM_ID])) {
            throw new GraphQlInputException(__('%1 and %2 can\'t be used at the same time.', ResultInterface::ID,
                ResultInterface::FORM_ID));
        }
        if (isset($args['filter'][ResultInterface::ID]) && (int)$args['filter'][ResultInterface::ID] < 1) {
            throw new GraphQlInputException(__('Result id must be greater than 0.'));
        }
        if (isset($args['filter'][ResultInterface::FORM_ID]) && (int)$args['filter'][ResultInterface::FORM_ID] < 1) {
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
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $form = $this->formRepository->getById($args[FormInterface::ID]);
        if (!$form->getIsActive()) {
            throw new GraphQlAuthorizationException(__('Web-form is not active.'));
        }
        if (!$form->getIsCustomerDashboardEnabled()) {
            throw new GraphQlAuthorizationException(__('You don\'t have enough permissions to access this content.'));
        }
        if (!in_array($context->getExtensionAttributes()->getCustomerGroupId(), $form->getDashboardGroups())) {
            throw new GraphQlAuthorizationException(__('You don\'t have enough permissions to access this content.'));
        }
        if (!in_array(Permission::VIEW, $form->getCustomerResultPermissions())) {
            throw new GraphQlAuthorizationException(__('You don\'t have enough permissions to access this content.'));
        }
    }
}