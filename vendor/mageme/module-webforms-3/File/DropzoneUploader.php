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


use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\TmpFileDropzoneInterface;
use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\TmpFileDropzoneRepositoryInterface;
use MageMe\WebForms\Model\FileDropzone;
use MageMe\WebForms\Model\FileDropzoneFactory;
use MageMe\WebForms\Model\Result;
use MageMe\WebForms\Model\TmpFileDropzone;
use MageMe\WebForms\Model\TmpFileDropzoneFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManager;

class DropzoneUploader extends AbstractUploader
{
    const UPLOAD_DIR = parent::UPLOAD_DIR . '/dropzone';
    const TMP_DIR = parent::TMP_DIR . '/dropzone';

    /**
     * @var TmpFileDropzoneFactory
     */
    protected $tmpFileDropzoneFactory;

    /**
     * @var FileDropzoneFactory
     */
    protected $fileDropzoneFactory;

    /**
     * @var TmpFileDropzoneRepositoryInterface
     */
    protected $tmpFileDropzoneRepository;

    /**
     * @var FileDropzoneRepositoryInterface
     */
    protected $fileDropzoneRepository;

    /**
     * DropzoneUploader constructor.
     * @param FileDropzoneRepositoryInterface $fileDropzoneRepository
     * @param TmpFileDropzoneRepositoryInterface $tmpFileDropzoneRepository
     * @param FileDropzoneFactory $fileDropzoneFactory
     * @param TmpFileDropzoneFactory $tmpFileDropzoneFactory
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param StoreManager $storeManager
     * @param Random $random
     */
    public function __construct(
        FileDropzoneRepositoryInterface    $fileDropzoneRepository,
        TmpFileDropzoneRepositoryInterface $tmpFileDropzoneRepository,
        FileDropzoneFactory                $fileDropzoneFactory,
        TmpFileDropzoneFactory             $tmpFileDropzoneFactory,
        Filesystem                         $fileSystem,
        UploaderFactory                    $uploaderFactory,
        StoreManager                       $storeManager,
        Random                             $random
    )
    {
        parent::__construct($fileSystem, $uploaderFactory, $storeManager, $random);
        $this->tmpFileDropzoneFactory    = $tmpFileDropzoneFactory;
        $this->fileDropzoneFactory       = $fileDropzoneFactory;
        $this->tmpFileDropzoneRepository = $tmpFileDropzoneRepository;
        $this->fileDropzoneRepository    = $fileDropzoneRepository;
    }

    /**
     * @param string $fileId
     * @return TmpFileDropzone
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function saveFileToTmpDir(string $fileId): TmpFileDropzone
    {
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);

        $fieldId  = str_replace('file_', '', $fileId);
        $tmp_name = $this->random->getRandomString(20);
        $hash     = $this->random->getRandomString(40);

        $result = $uploader->save($this->getTmpDir(), $tmp_name);

        if (!$result) {
            throw new LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }

        $tmpFile = $this->tmpFileDropzoneFactory->create();
        $tmpFile->setData(
            [
                TmpFileDropzoneInterface::FIELD_ID => $fieldId,
                TmpFileDropzoneInterface::NAME => $result['name'],
                TmpFileDropzoneInterface::SIZE => $result['size'],
                TmpFileDropzoneInterface::MIME_TYPE => $result['type'],
                TmpFileDropzoneInterface::PATH => $this->getTmpFilePath($result['file']),
                TmpFileDropzoneInterface::HASH => $hash,
            ]
        );
        $this->tmpFileDropzoneRepository->save($tmpFile);
        return $tmpFile;
    }

    /**
     * @param TmpFileDropzoneInterface|TmpFileDropzone $tmpFile
     * @param ResultInterface|Result $result
     * @return FileDropzone
     * @throws LocalizedException
     */
    public function copyFileFromTmpDir(TmpFileDropzoneInterface $tmpFile, ResultInterface $result): FileDropzone
    {
        $tmp_name  = $this->random->getRandomString(20);
        $link_hash = $this->random->getRandomString(40);
        $file_path = $this->getUploadDir() . '/' . $tmp_name;

        $file = $this->fileDropzoneFactory->create();
        $file->setData(
            [
                FileDropzoneInterface::RESULT_ID => $result->getId(),
                FileDropzoneInterface::FIELD_ID => $tmpFile->getFieldId(),
                FileDropzoneInterface::NAME => $tmpFile->getName(),
                FileDropzoneInterface::SIZE => $tmpFile->getSize(),
                FileDropzoneInterface::MIME_TYPE => $tmpFile->getMimeType(),
                FileDropzoneInterface::PATH => $this->getPath() . '/' . $tmp_name,
                FileDropzoneInterface::LINK_HASH => $link_hash
            ]
        );
        $this->fileDropzoneRepository->save($file);

        if (!is_dir(dirname($file_path))) {
            mkdir(dirname($file_path), 0755, true);
        }

        copy($tmpFile->getFullPath(), $file_path);

        return $file;
    }

    /**
     * @param ResultInterface $result
     * @param array $uploadedFiles
     * @return FileDropzoneInterface[]
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function uploadResultFiles(ResultInterface $result, array $uploadedFiles): array
    {
        $files = [];
        if (!$result->getId()) {
            return $files;
        }
        foreach ($uploadedFiles as $fieldId => $uploadedFile) {
            $fileId   = 'file_' . $fieldId;
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            $tmp_name  = $this->random->getRandomString(20);
            $link_hash = $this->random->getRandomString(40);
            $size      = filesize($uploadedFile['tmp_name']);
            $mime      = $this->getMimeType($uploadedFile['tmp_name']);

            $success = $uploader->save($this->getUploadDir(), $tmp_name);

            if (!$success) {
                continue;
            }

            /** @var FileDropzoneInterface[] $oldFiles */
            $oldFiles = $this->fileDropzoneRepository->getListByResultAndFieldId($result->getId(),
                $fieldId)->getItems();
            foreach ($oldFiles as $oldFile) {
                $this->fileDropzoneRepository->delete($oldFile);
            }

            $file = $this->fileDropzoneFactory->create();
            $file->setData(
                [
                    FileDropzoneInterface::RESULT_ID => $result->getId(),
                    FileDropzoneInterface::FIELD_ID => $fieldId,
                    FileDropzoneInterface::NAME => $uploadedFile['name'],
                    FileDropzoneInterface::SIZE => $size,
                    FileDropzoneInterface::MIME_TYPE => $mime,
                    FileDropzoneInterface::PATH => $this->getPath() . '/' . $tmp_name,
                    FileDropzoneInterface::LINK_HASH => $link_hash
                ]
            );
            $files[] = $this->fileDropzoneRepository->save($file);
        }
        return $files;
    }

    /**
     * Cleanup temp files
     */
    public function cleanupTmp()
    {
        $this->tmpFileDropzoneRepository->cleanup();
    }
}
