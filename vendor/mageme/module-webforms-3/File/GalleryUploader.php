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


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\TmpFileGalleryInterface;
use MageMe\WebForms\Api\FileGalleryRepositoryInterface;
use MageMe\WebForms\Api\TmpFileGalleryRepositoryInterface;
use MageMe\WebForms\Model\FileGallery;
use MageMe\WebForms\Model\FileGalleryFactory;
use MageMe\WebForms\Model\TmpFileGallery;
use MageMe\WebForms\Model\TmpFileGalleryFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManager;

/**
 * Class GalleryUploader
 * @package MageMe\WebForms\Config\Uploader
 */
class GalleryUploader extends AbstractUploader
{
    const UPLOAD_DIR = parent::UPLOAD_DIR . '/gallery';
    const TMP_DIR = parent::TMP_DIR . '/gallery';

    /**
     * @var TmpFileGalleryFactory
     */
    protected $tmpFileGalleryFactory;

    /**
     * @var FileGalleryFactory
     */
    protected $fileGalleryFactory;

    /**
     * @var TmpFileGalleryRepositoryInterface
     */
    protected $tmpFileGalleryRepository;

    /**
     * @var FileGalleryRepositoryInterface
     */
    protected $fileGalleryRepository;
    /**
     * Allowed file types
     *
     * @var array
     */
    private $allowedMimeTypes = [
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/png',
        'png' => 'image/gif'
    ];

    /**
     * GalleryUploader constructor.
     * @param FileGalleryRepositoryInterface $fileGalleryRepository
     * @param TmpFileGalleryRepositoryInterface $tmpFileGalleryRepository
     * @param FileGalleryFactory $fileGalleryFactory
     * @param TmpFileGalleryFactory $tmpFileGalleryFactory
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param StoreManager $storeManager
     * @param Random $random
     */
    public function __construct(
        FileGalleryRepositoryInterface    $fileGalleryRepository,
        TmpFileGalleryRepositoryInterface $tmpFileGalleryRepository,
        FileGalleryFactory                $fileGalleryFactory,
        TmpFileGalleryFactory             $tmpFileGalleryFactory,
        Filesystem                        $fileSystem,
        UploaderFactory                   $uploaderFactory,
        StoreManager                      $storeManager,
        Random                            $random
    )
    {
        parent::__construct($fileSystem, $uploaderFactory, $storeManager, $random);
        $this->tmpFileGalleryFactory    = $tmpFileGalleryFactory;
        $this->fileGalleryFactory       = $fileGalleryFactory;
        $this->tmpFileGalleryRepository = $tmpFileGalleryRepository;
        $this->fileGalleryRepository    = $fileGalleryRepository;
    }

    /**
     * @param null $fieldId
     * @param string $fileId
     * @return TmpFileGallery
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    public function saveFileToTmpDir($fieldId = null, string $fileId = 'image'): TmpFileGallery
    {
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions($this->getAllowedExtensions());
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

        $tmpFile = $this->tmpFileGalleryFactory->create();
        $tmpFile->setData(
            [
                TmpFileGallery::FIELD_ID => $fieldId,
                TmpFileGallery::NAME => $result['name'],
                TmpFileGallery::SIZE => $result['size'],
                TmpFileGallery::MIME_TYPE => $result['type'],
                TmpFileGallery::PATH => $this->getTmpFilePath($result['file']),
                TmpFileGallery::HASH => $hash,
            ]
        );
        $this->tmpFileGalleryRepository->save($tmpFile);
        return $tmpFile;
    }

    /**
     * Get the set of allowed file extensions.
     *
     * @return array
     */
    private function getAllowedExtensions(): array
    {
        return array_keys($this->allowedMimeTypes);
    }

    /**
     * @param TmpFileGalleryInterface|TmpFileGallery $tmpFile
     * @param FieldInterface $field
     * @return FileGallery
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function copyFileFromTmpDir(TmpFileGalleryInterface $tmpFile, FieldInterface $field): FileGallery
    {
        $tmp_name  = $this->random->getRandomString(20);
        $link_hash = $this->random->getRandomString(40);
        $file_path = $this->getUploadDir() . '/' . $tmp_name;

        $file = $this->fileGalleryFactory->create();
        $file->setData(
            [
                FileGallery::FIELD_ID => $field->getId(),
                FileGallery::NAME => $tmpFile->getName(),
                FileGallery::SIZE => $tmpFile->getSize(),
                FileGallery::MIME_TYPE => $tmpFile->getMimeType(),
                FileGallery::PATH => $this->getPath() . '/' . $tmp_name,
                FileGallery::LINK_HASH => $link_hash
            ]
        );
        $this->fileGalleryRepository->save($file);

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
        $this->tmpFileGalleryRepository->cleanup();
    }
}
