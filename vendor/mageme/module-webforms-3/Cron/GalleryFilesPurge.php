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


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\StoreInterface;
use MageMe\WebForms\Api\FileGalleryRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\Field;
use MageMe\WebForms\Model\ResourceModel\FileGallery;
use MageMe\WebForms\Setup\Table\FieldTable;
use MageMe\WebForms\Setup\Table\StoreTable;
use Magento\Framework\Exception\CouldNotDeleteException;
use Psr\Log\LoggerInterface;

class GalleryFilesPurge
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var FileGalleryRepositoryInterface
     */
    protected $fileGalleryRepository;
    /**
     * @var FileGallery
     */
    private $fileGalleryResource;

    /**
     * GalleryFilesPurge constructor.
     * @param FileGallery $fileGalleryResource
     * @param FileGalleryRepositoryInterface $fileGalleryRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        FileGallery                    $fileGalleryResource,
        FileGalleryRepositoryInterface $fileGalleryRepository,
        LoggerInterface                $logger
    )
    {
        $this->logger                = $logger;
        $this->fileGalleryRepository = $fileGalleryRepository;
        $this->fileGalleryResource   = $fileGalleryResource;
    }

    /**
     * Purge gallery files
     */
    public function execute()
    {
        $files      = $this->fileGalleryRepository->getList()->getItems();
        $connection = $this->fileGalleryResource->getConnection();
        foreach ($files as $file) {
            $select = $connection->select()
                ->from(['mwf' => FieldTable::TABLE_NAME], FieldInterface::ID)
                ->joinLeft(['mws' => StoreTable::TABLE_NAME],
                    'mwf.' . FieldInterface::ID . ' = mws.' . StoreInterface::ENTITY_ID .
                    ' AND mws.' . StoreInterface::ENTITY_TYPE . ' = \'' . Field::ENTITY_TYPE . '\'',
                    [])
                ->where('mwf.' . FieldInterface::TYPE . ' = (?)', 'gallery')
                ->where('mwf.' . FieldInterface::TYPE_ATTRIBUTES_SERIALIZED . ' LIKE \'%"value_id":"' . $file->getId() . '"%\'' .
                    ' OR mws.' . StoreInterface::STORE_DATA_SERIALIZED . ' LIKE \'%"value_id":"' . $file->getId() . '"%\'');
            $result = $connection->fetchCol($select);
            if (!empty($result)) continue;
            try {
                $this->fileGalleryRepository->delete($file);
            } catch (CouldNotDeleteException $e) {
                $this->logger->error($e);
            }
        }
    }
}