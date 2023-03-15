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

namespace MageMe\WebForms\File;


use Exception;
use MageMe\WebForms\Api\Data\FileCustomerNotificationInterface;
use MageMe\WebForms\Api\Data\TmpFileCustomerNotificationInterface;
use MageMe\WebForms\Api\FileCustomerNotificationRepositoryInterface;
use MageMe\WebForms\Api\TmpFileCustomerNotificationRepositoryInterface;
use MageMe\WebForms\Model\FileCustomerNotificationFactory;
use MageMe\WebForms\Model\TmpFileCustomerNotification;
use MageMe\WebForms\Model\TmpFileCustomerNotificationFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManager;

class CustomerNotificationUploader extends AbstractUploader
{
    const UPLOAD_DIR = parent::UPLOAD_DIR . '/сustomer_notification';
    const TMP_DIR = parent::TMP_DIR . '/сustomer_notification';

    /**
     * @var TmpFileCustomerNotificationFactory
     */
    private $tmpFileCustomerNotificationFactory;
    /**
     * @var TmpFileCustomerNotificationRepositoryInterface
     */
    private $tmpFileCustomerNotificationRepository;
    /**
     * @var FileCustomerNotificationFactory
     */
    private $fileCustomerNotificationFactory;
    /**
     * @var FileCustomerNotificationRepositoryInterface
     */
    private $fileCustomerNotificationRepository;

    /**
     * CustomerNotificationUploader constructor.
     * @param FileCustomerNotificationRepositoryInterface $fileCustomerNotificationRepository
     * @param FileCustomerNotificationFactory $fileCustomerNotificationFactory
     * @param TmpFileCustomerNotificationRepositoryInterface $tmpFileCustomerNotificationRepository
     * @param TmpFileCustomerNotificationFactory $tmpFileCustomerNotificationFactory
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param StoreManager $storeManager
     * @param Random $random
     */
    public function __construct(
        FileCustomerNotificationRepositoryInterface    $fileCustomerNotificationRepository,
        FileCustomerNotificationFactory                $fileCustomerNotificationFactory,
        TmpFileCustomerNotificationRepositoryInterface $tmpFileCustomerNotificationRepository,
        TmpFileCustomerNotificationFactory             $tmpFileCustomerNotificationFactory,
        Filesystem                                     $fileSystem,
        UploaderFactory                                $uploaderFactory,
        StoreManager                                   $storeManager,
        Random                                         $random
    )
    {
        parent::__construct($fileSystem, $uploaderFactory, $storeManager, $random);
        $this->tmpFileCustomerNotificationFactory    = $tmpFileCustomerNotificationFactory;
        $this->tmpFileCustomerNotificationRepository = $tmpFileCustomerNotificationRepository;
        $this->fileCustomerNotificationFactory       = $fileCustomerNotificationFactory;
        $this->fileCustomerNotificationRepository    = $fileCustomerNotificationRepository;
    }

    /**
     * @param mixed $fileId
     * @param int $formId
     * @return TmpFileCustomerNotificationInterface
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws Exception
     */
    public function saveFileToTmpDir($fileId, int $formId): TmpFileCustomerNotificationInterface
    {
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);

        $tmp_name = $this->random->getRandomString(20);
        $hash     = $this->random->getRandomString(40);

        $result = $uploader->save($this->getTmpDir(), $tmp_name);

        if (!$result) {
            throw new LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }

        $tmpFile = $this->tmpFileCustomerNotificationFactory->create();
        $tmpFile->setData(
            [
                TmpFileCustomerNotificationInterface::FORM_ID => $formId,
                TmpFileCustomerNotificationInterface::NAME => $result['name'],
                TmpFileCustomerNotificationInterface::SIZE => $result['size'],
                TmpFileCustomerNotificationInterface::MIME_TYPE => $result['type'],
                TmpFileCustomerNotificationInterface::PATH => $this->getTmpFilePath($result['file']),
                TmpFileCustomerNotificationInterface::HASH => $hash,
            ]
        );
        $this->tmpFileCustomerNotificationRepository->save($tmpFile);
        return $tmpFile;
    }

    /**
     * @param TmpFileCustomerNotificationInterface|TmpFileCustomerNotification $tmpFile
     * @param int $formId
     * @return FileCustomerNotificationInterface
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function copyFileFromTmpDir(TmpFileCustomerNotificationInterface $tmpFile, int $formId): FileCustomerNotificationInterface
    {
        $tmp_name  = $this->random->getRandomString(20);
        $link_hash = $this->random->getRandomString(40);
        $file_path = $this->getUploadDir() . '/' . $tmp_name;

        $file = $this->fileCustomerNotificationFactory->create();
        $file->setData(
            [
                FileCustomerNotificationInterface::FORM_ID => $formId,
                FileCustomerNotificationInterface::NAME => $tmpFile->getName(),
                FileCustomerNotificationInterface::SIZE => $tmpFile->getSize(),
                FileCustomerNotificationInterface::MIME_TYPE => $tmpFile->getMimeType(),
                FileCustomerNotificationInterface::PATH => $this->getPath() . '/' . $tmp_name,
                FileCustomerNotificationInterface::LINK_HASH => $link_hash
            ]
        );
        $this->fileCustomerNotificationRepository->save($file);

        if (!is_dir(dirname($file_path))) {
            mkdir(dirname($file_path), 0755, true);
        }

        copy($tmpFile->getFullPath(), $file_path);

        return $file;
    }

    /**
     * Cleanup temp files
     */
    public function cleanupTmp()
    {
        $this->tmpFileCustomerNotificationRepository->cleanup();
    }
}