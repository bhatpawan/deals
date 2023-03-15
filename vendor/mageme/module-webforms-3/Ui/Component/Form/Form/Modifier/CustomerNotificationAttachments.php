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

namespace MageMe\WebForms\Ui\Component\Form\Form\Modifier;


use MageMe\WebForms\Api\Data\FormInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class CustomerNotificationAttachments implements ModifierInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * CustomerNotificationUpload constructor.
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        UrlInterface     $urlBuilder,
        RequestInterface $request
    )
    {
        $this->request    = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        $formId = (int)$this->request->getParam(FormInterface::ID);
        if (!$formId) {
            return $meta;
        }
        $meta['email_settings']['children']['customer_notification']['children'][FormInterface::CUSTOMER_NOTIFICATION_ATTACHMENTS] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'source' => 'field',
                        'componentType' => Form\Field::NAME,
                        'formElement' => 'fileUploader',
                        'isMultipleFiles' => 1,
                        'sortOrder' => 70,
                        'label' => __('Custom attachments for customer notification'),
                        'uploaderConfig' => [
                            'url' => $this->getCustomerAttachmentsUrl($formId)
                        ]
                    ]
                ]
            ]
        ];
        return $meta;
    }

    /**
     * @param int $formId
     * @return string
     */
    protected function getCustomerAttachmentsUrl(int $formId): string
    {
        return $this->urlBuilder->getUrl('webforms/file/customernotificationupload', [
            FormInterface::ID => $formId
        ]);
    }
}