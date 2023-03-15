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

namespace MageMe\WebForms\Ui\Component\Result\Info\Form\Modifier;

use IntlDateFormatter;
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;

class Messages extends AbstractModifier
{
    protected $messageRepository;
    protected $fileMessageRepository;
    protected $urlBuilder;

    public function __construct(
        UrlInterface                   $urlBuilder,
        FileMessageRepositoryInterface $fileMessageRepository,
        MessageRepositoryInterface     $messageRepository,
        SearchCriteriaBuilder          $searchCriteriaBuilder,
        ScopeConfigInterface           $scopeConfig,
        FormRepositoryInterface        $formRepository,
        ResultRepositoryInterface      $resultRepository,
        TimezoneInterface              $timezone,
        RequestInterface               $request)
    {
        parent::__construct($searchCriteriaBuilder, $scopeConfig, $formRepository, $resultRepository, $timezone, $request);
        $this->messageRepository     = $messageRepository;
        $this->fileMessageRepository = $fileMessageRepository;
        $this->urlBuilder            = $urlBuilder;
    }

    public function modifyData(array $data): array
    {
        /** Messages */
        $messages = [];
        foreach ($this->messageRepository->getListByResultId($data['result_id'])->getItems() as $message) {
            $messageData                                        = $message->getData();
            $messageData[MessageInterface::CREATED_AT]          = $this->timezone->formatDate($message->getCreatedAt(),
                IntlDateFormatter::MEDIUM, true);
            $messageData[MessageInterface::IS_CUSTOMER_EMAILED] = (bool)$message->getIsCustomerEmailed();
            $author                                             = '<strong>' . $messageData[MessageInterface::AUTHOR] . '</strong>';
            $messageData['signature']                           = __('Sent by %1 on %2', $author, $messageData[MessageInterface::CREATED_AT]);
            $messageData['attachments']                         = '';
            foreach ($this->fileMessageRepository->getListByMessageId($message->getId())->getItems() as $file) {
                $messageData['attachments'] .= $file->getDownloadHtml();
            }
            $messages[] = $messageData;
        }
        $data['messages']           = $messages;
        $data['message_email_url']  = $this->getMessageEmailUrl();
        $data['message_delete_url'] = $this->getMessageDeleteUrl();
        return $data;
    }

    /**
     * Message email action URL
     *
     * @return string
     */
    public function getMessageEmailUrl(): string
    {
        return $this->getUrl('webforms/message/email');
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * Message delete action URL
     *
     * @return string
     */
    public function getMessageDeleteUrl(): string
    {
        return $this->getUrl('webforms/message/delete');
    }

    public function modifyMeta(array $meta): array
    {
        $result        = $this->getResult();
        $messagesCount = $this->messageRepository->getListByResultId($result->getId())->getTotalCount();
        if ($messagesCount) {
            $meta['messages'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Form\Fieldset::NAME,
                            'label' => __('Messages History (%1)', [$messagesCount]),
                            'collapsible' => true,
                            'opened' => false,
                        ]
                    ]
                ],
                'children' => [
                    'messages' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                                    'componentType' => DynamicRows::NAME,
                                    'placeholder' => __('No messages yet.'),
                                    'label' => null,
                                    'columnsHeaderAfterRender' => false,
                                    'template' => 'MageMe_WebForms/dynamic-rows/templates/default-with-placeholder',
                                    'identificationProperty' => 'message_id',
                                    'addButton' => false,
                                    'visible' => true,
                                    'columnsHeader' => false,
                                    'additionalClasses' => 'admin__field-wide  webforms-result-messages-block',
                                    'dndConfig' => ['enabled' => false],
                                ],
                            ]
                        ],
                        'children' => [
                            'record' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'componentType' => 'container',
                                            'isTemplate' => true,
                                            'is_collection' => true,
                                            'component' => 'Magento_Ui/js/dynamic-rows/record',
                                        ]
                                    ]
                                ],
                                'children' => [
                                    'message_id' => [
                                        'arguments' => [
                                            'data' => [
                                                'config' => [
                                                    'component' => 'MageMe_WebForms/js/form/element/message',
                                                    'componentType' => 'field',
                                                    'formElement' => 'input',
                                                    'fit' => false,
                                                    'elementTmpl' => 'MageMe_WebForms/form/element/message',
                                                    'label' => __('Message'),
                                                    'labelVisible' => false,
                                                    'additionalClasses' => 'admin__field-wide',
                                                    'imports' => [
                                                        'email' => 'webforms_result_reply_form.webforms_result_reply_form_data_source:data.email',
                                                        'bcc' => 'webforms_result_reply_form.webforms_result_reply_form_data_source:data.bcc',
                                                        'cc' => 'webforms_result_reply_form.webforms_result_reply_form_data_source:data.cc',
                                                        'email_url' => '${ $.provider }:data.message_email_url',
                                                        'message' => '${ $.provider }:${ $.parentScope }.message',
                                                        'author' => '${ $.provider }:${ $.parentScope }.author',
                                                        'signature' => '${ $.provider }:${ $.parentScope }.signature',
                                                        'is_customer_emailed' => '${ $.provider }:${ $.parentScope }.is_customer_emailed',
                                                        'created_at' => '${ $.provider }:${ $.parentScope }.created_at',
                                                        'attachments' => '${ $.provider }:${ $.parentScope }.attachments',
                                                        '__disableTmpl' => false,
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                    'action' => [
                                        'arguments' => [
                                            'data' => [
                                                'config' => [
                                                    'component' => 'MageMe_WebForms/js/form/element/action-delete-message',
                                                    'componentType' => 'actionDelete',
                                                    'fit' => false,
                                                    'dataType' => 'text',
                                                    'label' => __('Actions'),
                                                    'imports' => [
                                                        'url' => '${ $.provider }:data.message_delete_url',
                                                        'message_id' => '${ $.provider }:${ $.dataScope }.message_id',
                                                        '__disableTmpl' => false,
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ]
                ]
            ];
        }
        return $meta;
    }

}
