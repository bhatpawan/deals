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

use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\File\DropzoneApiUploader;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Upload extends Form implements ResolverInterface
{
    const ADMIN_RESOURCE = 'MageMe_WebForms::api_submission';

    /**
     * @var FieldRepositoryInterface
     */
    private $fieldRepository;
    /**
     * @var DropzoneApiUploader
     */
    private $apiUploader;

    /**
     * @param DropzoneApiUploader $apiUploader
     * @param FieldRepositoryInterface $fieldRepository
     * @param FormRepositoryInterface $formRepository
     * @param AccessHelper $accessHelper
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        DropzoneApiUploader      $apiUploader,
        FieldRepositoryInterface $fieldRepository,
        FormRepositoryInterface  $formRepository,
        AccessHelper             $accessHelper,
        AuthorizationInterface   $authorization
    ) {
        parent::__construct($formRepository, $accessHelper, $authorization);
        $this->fieldRepository = $fieldRepository;
        $this->apiUploader     = $apiUploader;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->validateInput($args);
        $fieldId  = $args[FieldInterface::ID];
        $filename = $args['filename'];
        $content  = $args['content'];
        $mimeType = $args['mimeType'] ?? '';
        try {
            $field                   = $this->fieldRepository->getById($fieldId);
            $args[FormInterface::ID] = $field->getFormId();
            $this->validateAccess($context, $args);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(__('Field not found.'), $e);
        }
        if ($this->apiUploader->isUri($content)) {
            try {
                $uriData  = $this->apiUploader->getUriData($content);
                $content  = $uriData[DropzoneApiUploader::CONTENT];
                $mimeType = $mimeType ?: $uriData[DropzoneApiUploader::MIME_TYPE];
                if (empty($content)) {
                    throw new Exception(__('Content is required.'));
                }
                if (empty($mimeType)) {
                    throw new Exception(__('MimeType is required.'));
                }
            } catch (Exception $e) {
                throw new GraphQlInputException(__('Invalid URI data.'), $e);
            }
        }
        $uploadResult = $this->apiUploader->uploadBase64($fieldId, $filename, $content, $mimeType);
        try {
            if (!empty($uploadResult['errors'])) {
                throw new Exception(implode("\n", $uploadResult['errors']));
            }
            if (empty($uploadResult['hash'])) {
                throw new Exception(__('Unexpected error.'));
            }
        } catch (Exception $e) {
            throw new GraphQlInputException(__('Could not upload file.'), $e);
        }
        return $uploadResult['hash'];
    }

    /**
     * @param array $args
     * @return void
     * @throws GraphQlInputException
     */
    protected function validateInput(array $args): void
    {
        if (empty($args[FieldInterface::ID])) {
            throw new GraphQlInputException(__('Field id is required.'));
        }
        if (empty($args['filename'])) {
            throw new GraphQlInputException(__('Filename is required.'));
        }
        if (empty($args['content'])) {
            throw new GraphQlInputException(__('Content is required.'));
        }
        if (!$this->apiUploader->isUri($args['content']) && empty($args['mimeType'])) {
            throw new GraphQlInputException(__('MimeType is required.'));
        }
    }

    /**
     * @inheirtDoc
     * @throws GraphQlAuthorizationException
     * @throws NoSuchEntityException
     */
    protected function validateCustomerAccess(ContextInterface $context, array $args): void
    {
        if (!$context->getExtensionAttributes()->getIsCaptchaVerified()) {
            throw new GraphQlAuthorizationException(__('Please validate the reCAPTCHA first.'));
        }
        parent::validateCustomerAccess($context, $args);
    }
}