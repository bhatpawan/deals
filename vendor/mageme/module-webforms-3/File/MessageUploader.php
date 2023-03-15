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


use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\TmpFileMessageInterface;
use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Api\TmpFileMessageRepositoryInterface;
use MageMe\WebForms\Model\FileMessage;
use MageMe\WebForms\Model\FileMessageFactory;
use MageMe\WebForms\Model\Result;
use MageMe\WebForms\Model\TmpFileMessage;
use MageMe\WebForms\Model\TmpFileMessageFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManager;

class MessageUploader extends AbstractUploader
{
    const UPLOAD_DIR = parent::UPLOAD_DIR . '/message';
    const TMP_DIR = parent::TMP_DIR . '/message';

    /**
     * @var TmpFileMessageFactory
     */
    protected $tmpFileMessageFactory;

    /**
     * @var FileMessageFactory
     */
    protected $fileMessageFactory;

    /**
     * @var TmpFileMessageRepositoryInterface
     */
    protected $tmpFileMessageRepository;

    /**
     * @var FileMessageRepositoryInterface
     */
    protected $fileMessageRepository;

    public function __construct(
        FileMessageRepositoryInterface    $fileMessageRepository,
        TmpFileMessageRepositoryInterface $tmpFileMessageRepository,
        FileMessageFactory                $fileMessageFactory,
        TmpFileMessageFactory             $tmpFileMessageFactory,
        Filesystem                        $fileSystem,
        UploaderFactory                   $uploaderFactory,
        StoreManager                      $storeManager,
        Random                            $random
    )
    {
        parent::__construct($fileSystem, $uploaderFactory, $storeManager, $random);
        $this->tmpFileMessageFactory    = $tmpFileMessageFactory;
        $this->fileMessageFactory       = $fileMessageFactory;
        $this->tmpFileMessageRepository = $tmpFileMessageRepository;
        $this->fileMessageRepository    = $fileMessageRepository;
    }

    public function saveFileToTmpDir($fileId): TmpFileMessage
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

        $tmpFile = $this->tmpFileMessageFactory->create();
        $tmpFile->setData(
            [
                TmpFileMessage::NAME => $result['name'],
                TmpFileMessage::SIZE => $result['size'],
                TmpFileMessage::MIME_TYPE => $result['type'],
                TmpFileMessage::PATH => $this->getTmpFilePath($result['file']),
                TmpFileMessage::HASH => $hash,
            ]
        );
        $this->tmpFileMessageRepository->save($tmpFile);
        return $tmpFile;
    }

    /**
     * @param TmpFileMessageInterface|TmpFileMessage $tmpFile
     * @param MessageInterface|Result $message
     * @return FileMessage
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function copyFileFromTmpDir(TmpFileMessageInterface $tmpFile, MessageInterface $message): FileMessage
    {
        $tmp_name  = $this->random->getRandomString(20);
        $link_hash = $this->random->getRandomString(40);
        $file_path = $this->getUploadDir() . '/' . $tmp_name;

        $file = $this->fileMessageFactory->create();
        $file->setData(
            [
                FileMessage::MESSAGE_ID => $message->getId(),
                FileMessage::NAME => $tmpFile->getName(),
                FileMessage::SIZE => $tmpFile->getSize(),
                FileMessage::MIME_TYPE => $tmpFile->getMimeType(),
                FileMessage::PATH => $this->getPath() . '/' . $tmp_name,
                FileMessage::LINK_HASH => $link_hash
            ]
        );
        $this->fileMessageRepository->save($file);

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
        $this->tmpFileMessageRepository->cleanup();
    }
}
