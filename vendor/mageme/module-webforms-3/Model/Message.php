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

namespace MageMe\WebForms\Model;

use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManager;

class Message extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, MessageInterface
{
    /**
     * Message cache tag
     */
    const CACHE_TAG = 'webforms_message';

    /**
     * @var string
     */
    protected $_cacheTag = 'webforms_message';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webforms_message';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var FileMessageRepositoryInterface
     */
    protected $fileMessageRepository;

    /**
     * Message constructor.
     * @param FileMessageRepositoryInterface $fileMessageRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param FormRepositoryInterface $formRepository
     * @param StoreManager $storeManager
     * @param TimezoneInterface $localeDate
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        FileMessageRepositoryInterface $fileMessageRepository,
        ResultRepositoryInterface      $resultRepository,
        FormRepositoryInterface        $formRepository,
        StoreManager                   $storeManager,
        TimezoneInterface              $localeDate,
        ScopeConfigInterface           $scopeConfig,
        Context                        $context,
        Registry                       $registry,
        AbstractResource               $resource = null,
        AbstractDb                     $resourceCollection = null,
        array                          $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->scopeConfig           = $scopeConfig;
        $this->localeDate            = $localeDate;
        $this->storeManager          = $storeManager;
        $this->formRepository        = $formRepository;
        $this->resultRepository      = $resultRepository;
        $this->fileMessageRepository = $fileMessageRepository;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getId();
    }

#region DB getters and setters

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function setResultId(int $resultId): MessageInterface
    {
        return $this->setData(self::RESULT_ID, $resultId);
    }

    /**
     * @inheritDoc
     */
    public function getUserId(): ?int
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setUserId(?int $userId): MessageInterface
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * @inheritDoc
     */
    public function setMessage(?string $message): MessageInterface
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @inheritDoc
     */
    public function setAuthor(?string $author): MessageInterface
    {
        return $this->setData(self::AUTHOR, $author);
    }

    /**
     * @inheritDoc
     */
    public function getIsCustomerEmailed(): bool
    {
        return (bool)$this->getData(self::IS_CUSTOMER_EMAILED);
    }

    /**
     * @inheritDoc
     */
    public function setIsCustomerEmailed(bool $isCustomerEmailed): MessageInterface
    {
        return $this->setData(self::IS_CUSTOMER_EMAILED, $isCustomerEmailed);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): MessageInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException|LocalizedException
     */
    public function getTemplateVars(): array
    {
        $result = $this->getResult();
        $name   = $result->getCustomerName();

        $webform     = $result->getForm();
        $store_group = $this->storeManager->getStore($result->getStoreId())->getFrontendName();
        $store_name  = $this->storeManager->getStore($result->getStoreId())->getName();

        $varCustomer = new DataObject([
            'name' => $name,
        ]);

        $varResult = $result->getTemplateResultVar();

        $varResult->addData([
            'id' => $result->getId(),
            'subject' => $result->getSubject(),
            'date' => $this->localeDate->formatDate($result->getCreatedAt()),
            'html' => $result->toHtml(),
        ]);

        $varReply = new DataObject([
            'date' => $this->localeDate->formatDate($this->getCreatedAt()),
            'message' => $this->getMessage(),
            'author' => $this->getAuthor(),
        ]);

        $vars = [
            'webform_subject' => $result->getSubject(),
            'webform_name' => $webform->getName(),
            'customer_name' => $result->getCustomerName(),
            'customer_email' => $result->getCustomerEmail(),
            'ip' => $result->getCustomerIp(),
            'store_group' => $store_group,
            'store_name' => $store_name,
            'customer' => $varCustomer,
            'result' => $varResult,
            'reply' => $varReply,
            'webform' => $webform,
        ];

        $customer = $result->getCustomer();

        if ($customer) {
            $vars['customer'] = $customer;
            $billing_address  = $customer->getDefaultBilling();
            if ($billing_address) {
                $vars['billing_address'] = $billing_address;
            }
            $shipping_address = $customer->getDefaultShipping();
            if ($shipping_address) {
                $vars['shipping_address'] = $shipping_address;
            }
        }

        return $vars;
    }

    /**
     * @inheritDoc
     */
    public function getResult(): ResultInterface
    {
        return $this->resultRepository->getById($this->getResultId());
    }

    /**
     * @inheritDoc
     */
    public function getResultId(): ?int
    {
        return $this->getData(self::RESULT_ID);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): ?string
    {
        return $this->getData(self::MESSAGE);
    }

#endregion

    /**
     * @inheritDoc
     */
    public function getAuthor(): ?string
    {
        return $this->getData(self::AUTHOR);
    }

    /**
     * @inheritDoc
     */
    public function getFiles(): array
    {
        return $this->fileMessageRepository->getListByMessageId($this->getId())->getItems();
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Message::class);
    }
}
