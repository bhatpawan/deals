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

namespace MageMe\WebForms\Model\ResourceModel;

use Exception;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\FileCustomerNotificationRepositoryInterface;
use MageMe\WebForms\Api\StoreRepositoryInterface;
use MageMe\WebForms\Config\Exception\UrlRewriteAlreadyExistsException;
use MageMe\WebForms\Setup\Table\FormTable;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManager;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResource;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Form resource model
 *
 */
class Form extends AbstractResource
{
    const ENTITY_TYPE = 'form';
    const DB_TABLE = FormTable::TABLE_NAME;
    const ID_FIELD = FormInterface::ID;

    /**
     * @inheritdoc
     */
    protected $serializableFields = [
        FormInterface::ACCESS_GROUPS => [
            self::SERIALIZE_OPTION_SERIALIZED => FormInterface::ACCESS_GROUPS_SERIALIZED,
            self::SERIALIZE_OPTION_DEFAULT_DESERIALIZED => []
        ],
        FormInterface::DASHBOARD_GROUPS => [
            self::SERIALIZE_OPTION_SERIALIZED => FormInterface::DASHBOARD_GROUPS_SERIALIZED,
            self::SERIALIZE_OPTION_DEFAULT_DESERIALIZED => []
        ],
        FormInterface::CUSTOMER_RESULT_PERMISSIONS => [
            self::SERIALIZE_OPTION_SERIALIZED => FormInterface::CUSTOMER_RESULT_PERMISSIONS_SERIALIZED,
            self::SERIALIZE_OPTION_DEFAULT_DESERIALIZED => []
        ],
        FormInterface::CUSTOMER_NOTIFICATION_ATTACHMENTS => [
            self::SERIALIZE_OPTION_SERIALIZED => FormInterface::CUSTOMER_NOTIFICATION_ATTACHMENTS_SERIALIZED,
            self::SERIALIZE_OPTION_DEFAULT_DESERIALIZED => []
        ],
    ];

    /**
     * @var UrlRewriteCollectionFactory
     */
    protected $urlRewriteCollectionFactory;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var UrlRewriteResource
     */
    protected $urlRewriteResource;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var FileCustomerNotificationRepositoryInterface
     */
    protected $fileCustomerNotificationRepository;

    /**
     * Form constructor.
     * @param FileCustomerNotificationRepositoryInterface $fileCustomerNotificationRepository
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param StoreManager $storeManager
     * @param UrlRewriteResource $urlRewriteResource
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlRewriteCollectionFactory $urlRewriteCollectionFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param Context $context
     * @param string|null $connectionName
     */
    public function __construct(
        FileCustomerNotificationRepositoryInterface $fileCustomerNotificationRepository,
        FieldRepositoryInterface                    $fieldRepository,
        FieldsetRepositoryInterface                 $fieldsetRepository,
        StoreManager                                $storeManager,
        UrlRewriteResource                          $urlRewriteResource,
        UrlRewriteFactory                           $urlRewriteFactory,
        UrlRewriteCollectionFactory                 $urlRewriteCollectionFactory,
        StoreRepositoryInterface                    $storeRepository,
        Context                                     $context,
        ?string                                     $connectionName = null
    )
    {
        parent::__construct($storeRepository, $context, $connectionName);
        $this->urlRewriteCollectionFactory        = $urlRewriteCollectionFactory;
        $this->urlRewriteFactory                  = $urlRewriteFactory;
        $this->urlRewriteResource                 = $urlRewriteResource;
        $this->storeManager                       = $storeManager;
        $this->fieldsetRepository                 = $fieldsetRepository;
        $this->fieldRepository                    = $fieldRepository;
        $this->fileCustomerNotificationRepository = $fileCustomerNotificationRepository;
    }

    /**
     * @param int $roleId
     * @return array
     */
    public function getRoleFormsIds(int $roleId): array
    {
        try {
            $select = $this->getConnection()->select()
                ->from($this->getMainTable(), [FormInterface::ID])
                ->join(['admin_rule' => $this->getTable('authorization_rule')],
                    "admin_rule.resource_id = concat('MageMe_WebForms::form', " . $this->getMainTable() . "." . FormInterface::ID . ")",
                    [])
                ->where("admin_rule.role_id = $roleId")
                ->where("admin_rule.permission = 'allow'");
        } catch (LocalizedException $e) {
            return [];
        }
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param AbstractModel|FormInterface $object
     * @return AbstractResource
     * @throws Exception
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    protected function _beforeDelete(AbstractModel $object): AbstractResource
    {
        // delete customer notification files
        $files = $this->fileCustomerNotificationRepository->getListByFormId($object->getId())->getItems();
        foreach ($files as $file) {
            $this->fileCustomerNotificationRepository->delete($file);
        }

        // delete fields
        $fields = $this->fieldRepository->getListByWebformId($object->getId())->getItems();
        foreach ($fields as $field) {
            $this->fieldRepository->delete($field);
        }

        // delete fieldsets
        $fieldsets = $this->fieldsetRepository->getListByWebformId($object->getId())->getItems();
        foreach ($fieldsets as $fieldset) {
            $this->fieldsetRepository->delete($fieldset);
        }

        foreach ($this->getStores($object->getStoreId()) as $store) {
            $this->removeFormUrlRewrite($object->getId(), $store->getId());
        }

        return parent::_beforeDelete($object);
    }

    /**
     * Get stores for URL rewrites
     *
     * @param int|null $storeId
     * @return \Magento\Store\Model\Store[]
     */
    protected function getStores(?int $storeId = null): array
    {
        $stores = [];
        if ($storeId) {
            try {
                $stores[] = $this->storeManager->getStore($storeId);
            } catch (Exception $exception) {
            }
        }
        if (empty($stores)) {
            $stores = $this->storeManager->getStores();
        }
        return $stores;
    }

    /**
     * Remove form view rewrite
     *
     * @param int $id
     * @param int $storeId
     * @throws Exception
     */
    protected function removeFormUrlRewrite(int $id, int $storeId)
    {
        $urlRewriteCollection = $this->urlRewriteCollectionFactory->create();
        $urlRewriteCollection->addStoreFilter($storeId);
        $urlRewriteCollection->addFieldToFilter(UrlRewrite::ENTITY_TYPE,
            $this->getEntityType());
        $urlRewriteCollection->addFieldToFilter(UrlRewrite::ENTITY_ID, $id);

        foreach ($urlRewriteCollection as $urlRewrite) {
            $this->urlRewriteResource->delete($urlRewrite);
        }
    }

    /**
     * @param AbstractModel $object
     * @return AbstractDb|Form
     * @throws LocalizedException
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->dataHasChangedFor(FormInterface::URL_KEY) && $object->getId()) {
            $this->manageUrlRewrites($object->getId(), $object->getStoreId(), $object->getUrlKey());
        }

        return parent::_afterSave($object);
    }

    /**
     * Add or delete rewrites with URL key
     *
     * @param int $id
     * @param int $storeId
     * @param string|null $urlKey
     * @throws LocalizedException
     * @throws Exception
     */
    public function manageUrlRewrites(int $id, int $storeId, ?string $urlKey)
    {
        if ($urlKey) {
            if (!$this->isValidUrlKey($urlKey)) {
                throw new LocalizedException(
                    __(
                        "The form URL key can't use capital letters or disallowed symbols. "
                        . "Remove the letters and symbols and try again."
                    )
                );
            }
            if ($this->isNumericUrlKey($urlKey)) {
                throw new LocalizedException(
                    __("The form URL key can't use only numbers. Add letters or words and try again.")
                );
            }
            foreach ($this->getStores($storeId) as $store) {
                $this->addFormUrlRewrite($id, $store->getId(), $urlKey);
            }
        } else {
            foreach ($this->getStores($storeId) as $store) {
                $this->removeFormUrlRewrite($id, $store->getId());
            }
        }
    }

    /**
     *  Check whether form URL key is valid
     *
     * @param string $urlKey
     * @return bool
     */
    protected function isValidUrlKey(string $urlKey): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $urlKey);
    }

    /**
     *  Check whether form URL key is numeric
     *
     * @param string $urlKey
     * @return bool
     */
    protected function isNumericUrlKey(string $urlKey): bool
    {
        return preg_match('/^[0-9]+$/', $urlKey);
    }

    /**
     * Add form view rewrite
     *
     * @param int $id
     * @param int $storeId
     * @param string $urlKey
     * @return void
     * @throws UrlRewriteAlreadyExistsException
     * @throws LocalizedException
     * @throws Exception
     */
    protected function addFormUrlRewrite(int $id, int $storeId, string $urlKey)
    {
        $urlRewrite = null;
        $collection = $this->urlRewriteCollectionFactory->create();
        $collection->addStoreFilter($storeId);
        $collection->addFieldToFilter(UrlRewrite::ENTITY_TYPE,
            $this->getEntityType());
        $collection->addFieldToFilter(UrlRewrite::ENTITY_ID, $id);
        if ($collection->getSize() > 1) {
            $this->removeFormUrlRewrite($id, $storeId);
        } else {
            foreach ($collection as $item) {
                $urlRewrite = $item;
            }
        }
        if (!$urlRewrite) {
            $urlRewrite = $this->urlRewriteFactory->create();
        }
        $targetPath = 'webforms/form/view/' . FormInterface::ID . '/' . $id;
        $urlRewrite->setEntityType($this->getEntityType())
            ->setEntityId($id)
            ->setRequestPath($urlKey)
            ->setTargetPath($targetPath)
            ->setStoreId($storeId);
        try {
            $this->urlRewriteResource->save($urlRewrite);
        } catch (AlreadyExistsException $e) {
            throw new UrlRewriteAlreadyExistsException(__('URL Key already exists'), $e);
        }
    }
}
