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
use MageMe\WebForms\Helper\Result\PostHelper;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Zend_Validate_Exception;

class SubmitForm extends Form implements ResolverInterface
{
    const ADMIN_RESOURCE = 'MageMe_WebForms::api_submission';

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @param PostHelper $postHelper
     * @param FormRepositoryInterface $formRepository
     * @param AccessHelper $accessHelper
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        PostHelper              $postHelper,
        FormRepositoryInterface $formRepository,
        AccessHelper            $accessHelper,
        AuthorizationInterface  $authorization
    ) {
        parent::__construct($formRepository, $accessHelper, $authorization);
        $this->postHelper = $postHelper;
    }

    /**
     * @inerhitDoc
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws Zend_Validate_Exception
     * @throws GraphQlAuthorizationException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $this->validateInput($args);
        $this->validateAccess($context, $args);
        try {
            $form = $this->formRepository->getById($args[FormInterface::ID]);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        $data = json_decode($args['formData'], true);
        if (!$data) {
            throw new GraphQlInputException(__('Invalid data.'));
        }
        $this->postHelper->getRequest()->setParams($data);
        $result = $this->postHelper->postResult($form);
        return [
            'success' => $result['success'] ?: false,
            'errors' => isset($result['errors']) ? json_encode($result['errors']) : '',
            'result' => $result['model'] !== false ? $result['model']->getId() : ''
        ];
    }

    /**
     * @inheirtDoc
     * @throws GraphQlInputException
     */
    protected function validateInput(array $args): void
    {
        parent::validateInput($args);
        if (!isset($args['formData'])) {
            throw new GraphQlInputException(__('Form data should be specified.'));
        }
    }

    /**
     * @inheirtDoc
     * @throws GraphQlAuthorizationException
     * @throws NoSuchEntityException
     */
    protected function validateCustomerAccess(ContextInterface $context, array $args): void
    {
        parent::validateCustomerAccess($context, $args);
    }
}