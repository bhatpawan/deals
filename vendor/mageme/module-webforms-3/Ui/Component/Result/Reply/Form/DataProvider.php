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

namespace MageMe\WebForms\Ui\Component\Result\Reply\Form;


use IntlDateFormatter;
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Config\Options\Message\AdminCopy;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    const DROPZONE_MAX_FILES = 5;
    const DROPZONE_FILES_UPLOAD_LIMIT = 10000;
    const DROPZONE_RESTRICTED_EXTENSIONS = [];

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var FileMessageRepositoryInterface
     */
    protected $fileMessageRepository;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * DataProvider constructor.
     * @param TimezoneInterface $timezone
     * @param FileMessageRepositoryInterface $fileMessageRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        TimezoneInterface              $timezone,
        FileMessageRepositoryInterface $fileMessageRepository,
        CustomerRepositoryInterface    $customerRepository,
        MessageRepositoryInterface     $messageRepository,
        ResultRepositoryInterface      $resultRepository,
        UrlInterface                   $urlBuilder,
        RequestInterface               $request,
        CollectionFactory              $collectionFactory,
        ScopeConfigInterface           $scopeConfig,
        string                         $name,
        string                         $primaryFieldName,
        string                         $requestFieldName,
        array                          $meta = [],
        array                          $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection            = $collectionFactory->create();
        $this->request               = $request;
        $this->urlBuilder            = $urlBuilder;
        $this->resultRepository      = $resultRepository;
        $this->messageRepository     = $messageRepository;
        $this->customerRepository    = $customerRepository;
        $this->fileMessageRepository = $fileMessageRepository;
        $this->timezone              = $timezone;
        $this->scopeConfig           = $scopeConfig;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $result_id = $this->request->getParam(ResultInterface::ID);
        if (!$result_id) {
            return $this->loadedData;
        }
        $result = $this->resultRepository->getById($result_id)->addFieldArray();

        $data                              = [];
        $data[MessageInterface::RESULT_ID] = $result->getId();
        $customerEmail                     = $result->getCustomerEmail();
        $data['email']                     = is_array($customerEmail) && isset($customerEmail[0]) ? $customerEmail[0] : $customerEmail;
        $adminCopy  = $this->scopeConfig->getValue('webforms/message/admin_copy');
        if ($adminCopy != AdminCopy::NO){
            $form = $result->getForm();
            $data[$adminCopy] = $form->getAdminNotificationEmail();
        }

        /** Dropzone */
        $data['dropzone_url']                   = $this->getDropzoneUrl();
        $data['dropzone_max_files']             = self::DROPZONE_MAX_FILES;
        $data['dropzone_files_upload_limit']    = self::DROPZONE_FILES_UPLOAD_LIMIT;
        $data['dropzone_restricted_extensions'] = self::DROPZONE_RESTRICTED_EXTENSIONS;

        /** Action links */
        $data['quick_response_url'] = $this->getQuickresponseUrl();
        $data['message_email_url']  = $this->getMessageEmailUrl();
        $data['message_delete_url'] = $this->getMessageDeleteUrl();

        /** Messages */
        $messages = [];
        foreach ($this->messageRepository->getListByResultId($result->getId())->getItems() as $message) {
            $messageData                                        = $message->getData();
            $messageData[MessageInterface::CREATED_AT]          = $this->timezone->formatDate($message->getCreatedAt(),
                IntlDateFormatter::MEDIUM, true);
            $messageData[MessageInterface::IS_CUSTOMER_EMAILED] = (bool)$message->getIsCustomerEmailed();
            $messageData['attachments']                         = '';
            foreach ($this->fileMessageRepository->getListByMessageId($message->getId())->getItems() as $file) {
                $messageData['attachments'] .= $file->getDownloadHtml();
            }
            $messages[] = $messageData;
        }
        $data['messages']                   = $messages;
        $this->loadedData[$result->getId()] = $data;

        return $this->loadedData;
    }

    /**
     * Dropzone URL
     *
     * @return string
     */
    public function getDropzoneUrl(): string
    {
        return $this->getUrl('webforms/file/messageUpload');
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
    public function getQuickresponseUrl(): string
    {
        return $this->getUrl('webforms/quickresponse/get');
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
     * Message delete action URL
     *
     * @return string
     */
    public function getMessageDeleteUrl(): string
    {
        return $this->getUrl('webforms/message/delete');
    }
}
