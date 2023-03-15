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

namespace MageMe\WebForms\Cron;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\StoreInterface;
use MageMe\WebForms\Api\FileCustomerNotificationRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\FileCustomerNotification as FileCustomerNotificationResource;
use MageMe\WebForms\Model\ResourceModel\Form;
use MageMe\WebForms\Setup\Table\FormTable;
use MageMe\WebForms\Setup\Table\StoreTable;
use Magento\Framework\Exception\CouldNotDeleteException;
use Psr\Log\LoggerInterface;

class CustomerNotificationFilesPurge
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var FileCustomerNotificationRepositoryInterface
     */
    private $fileCustomerNotificationRepository;
    /**
     * @var FileCustomerNotificationResource
     */
    private $resource;

    /**
     * CustomerNotificationFilesPurge constructor.
     * @param FileCustomerNotificationResource $resource
     * @param FileCustomerNotificationRepositoryInterface $fileCustomerNotificationRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        FileCustomerNotificationResource            $resource,
        FileCustomerNotificationRepositoryInterface $fileCustomerNotificationRepository,
        LoggerInterface                             $logger
    )
    {
        $this->logger                             = $logger;
        $this->fileCustomerNotificationRepository = $fileCustomerNotificationRepository;
        $this->resource                           = $resource;
    }

    /**
     * Purge customer notification files
     */
    public function execute()
    {
        $files      = $this->fileCustomerNotificationRepository->getList()->getItems();
        $connection = $this->resource->getConnection();
        foreach ($files as $file) {
            $select = $connection->select()
                ->from(['mwf' => FormTable::TABLE_NAME], FormInterface::ID)
                ->joinLeft(['mws' => StoreTable::TABLE_NAME],
                    'mwf.' . FormInterface::ID . ' = mws.' . StoreInterface::ENTITY_ID .
                    ' AND mws.' . StoreInterface::ENTITY_TYPE . ' = \'' . Form::ENTITY_TYPE . '\'',
                    [])
                ->where('mwf.' . FormInterface::CUSTOMER_NOTIFICATION_ATTACHMENTS_SERIALIZED . ' LIKE \'%"id":"' . $file->getId() . '"%\'' .
                    ' OR mws.' . StoreInterface::STORE_DATA_SERIALIZED . ' LIKE \'%"id":"' . $file->getId() . '"%\'');
            $result = $connection->fetchCol($select);
            if (!empty($result)) continue;
            try {
                $this->fileCustomerNotificationRepository->delete($file);
            } catch (CouldNotDeleteException $e) {
                $this->logger->error($e);
            }
        }
    }
}