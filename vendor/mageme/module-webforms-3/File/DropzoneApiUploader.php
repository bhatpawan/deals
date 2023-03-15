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
use InvalidArgumentException;
use LogicException;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\TmpFileDropzoneInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\TmpFileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\Utility\DropzoneApiUploaderInterface;
use MageMe\WebForms\Model\Field\Type\File;
use MageMe\WebForms\Model\TmpFileDropzoneFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;

class DropzoneApiUploader implements DropzoneApiUploaderInterface
{
    const FILENAME = 'filename';
    const CONTENT = 'content';
    const MIME_TYPE = 'mime_type';

    /**
     * @var FieldRepositoryInterface
     */
    private $fieldRepository;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var DropzoneUploader
     */
    private $uploader;
    /**
     * @var Random
     */
    private $random;
    /**
     * @var TmpFileDropzoneFactory
     */
    private $tmpFileDropzoneFactory;
    /**
     * @var TmpFileDropzoneRepositoryInterface
     */
    private $tmpFileDropzoneRepository;

    /**
     * @param TmpFileDropzoneRepositoryInterface $tmpFileDropzoneRepository
     * @param TmpFileDropzoneFactory $tmpFileDropzoneFactory
     * @param Random $random
     * @param DropzoneUploader $dropzoneUploader
     * @param FieldRepositoryInterface $fieldRepository
     * @param RequestInterface $request
     */
    public function __construct(
        TmpFileDropzoneRepositoryInterface $tmpFileDropzoneRepository,
        TmpFileDropzoneFactory             $tmpFileDropzoneFactory,
        Random                             $random,
        DropzoneUploader                   $dropzoneUploader,
        FieldRepositoryInterface           $fieldRepository,
        RequestInterface                   $request
    ) {
        $this->request                   = $request;
        $this->fieldRepository           = $fieldRepository;
        $this->uploader                  = $dropzoneUploader;
        $this->random                    = $random;
        $this->tmpFileDropzoneFactory    = $tmpFileDropzoneFactory;
        $this->tmpFileDropzoneRepository = $tmpFileDropzoneRepository;
    }


    /**
     * @inheritDoc
     */
    public function upload(int $id)
    {
        $file = $this->request->getFiles($id);
        if ($file) {
            return $this->restUploadPost($id);
        }
        return $this->restUploadBase64($id);
    }

    /**
     * @param int $fieldId
     * @return array
     */
    protected function restUploadPost(int $fieldId): array
    {
        $errors = [];
        $hash   = '';

        try {
            $field = $this->getField($fieldId);
            $file  = $this->request->getFiles($fieldId);
            array_merge($errors, $field->validateFile($file));
            if (empty($errors)) {
                $file = $this->uploader->saveFileToTmpDir((string)$fieldId);
                $hash = $file->getHash();
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
        return [
            'success' => (bool)$hash,
            'errors' => $errors,
            'hash' => $hash
        ];
    }

    /**
     * @param int $fieldId
     * @return array
     */
    protected function restUploadBase64(int $fieldId): array
    {
        $filename = $this->request->getParam(self::FILENAME);
        $content  = $this->request->getParam(self::CONTENT);
        $mimeType = $this->request->getParam(self::MIME_TYPE);
        try {
            if ($this->isUri($content)) {
                $uriData  = $this->getUriData($content);
                $content  = $uriData[self::CONTENT];
                $mimeType = $mimeType ?: $uriData[self::MIME_TYPE];
            }
            if (empty($filename)) {
                throw new InvalidArgumentException(__('Filename is required.'));
            }
            if (empty($content)) {
                throw new InvalidArgumentException(__('Content is required.'));
            }
            if (empty($mimeType)) {
                throw new InvalidArgumentException(__('MIME type not found.'));
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'hash' => '',
            ];
        }
        return $this->uploadBase64($fieldId, $filename, $content, $mimeType);
    }

    /**
     * @param int $fieldId
     * @param string $filename
     * @param string $content
     * @param string $mimeType
     * @return array
     */
    public function uploadBase64(int $fieldId, string $filename, string $content, string $mimeType): array
    {
        $errors     = [];
        $resultHash = '';

        try {
            $field    = $this->getField($fieldId);
            $tmpName  = $this->random->getRandomString(20);
            $hash     = $this->random->getRandomString(40);
            $filePath = $this->uploader->getTmpDir() . '/' . $tmpName;

            if (!is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            $size = file_put_contents($filePath, $content);
            if ($size === false) {
                throw new LocalizedException(
                    __('File can not be saved to the destination folder.')
                );
            }

            $file = [
                'name' => $filename,
                'type' => $mimeType,
                'tmp_name' => $filePath,
                'error' => 0,
                'size' => $size
            ];

            array_merge($errors, $field->validateFile($file));

            if (empty($errors)) {
                $tmpFile = $this->tmpFileDropzoneFactory->create();
                $tmpFile->setData(
                    [
                        TmpFileDropzoneInterface::FIELD_ID => $field->getId(),
                        TmpFileDropzoneInterface::NAME => $filename,
                        TmpFileDropzoneInterface::SIZE => $size,
                        TmpFileDropzoneInterface::MIME_TYPE => $mimeType,
                        TmpFileDropzoneInterface::PATH => $this->uploader->getTmpFilePath($tmpName),
                        TmpFileDropzoneInterface::HASH => $hash,
                    ]
                );
                $this->tmpFileDropzoneRepository->save($tmpFile);
                $resultHash = $hash;
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        return [
            'success' => (bool)$resultHash,
            'errors' => $errors,
            'hash' => $resultHash
        ];
    }

    /**
     * @param int $fieldId
     * @return FieldInterface|File
     * @throws NoSuchEntityException
     * @throws InvalidArgumentException
     */
    protected function getField(int $fieldId)
    {
        $field = $this->fieldRepository->getById($fieldId);
        if (!($field instanceof File)) {
            throw new InvalidArgumentException(__('Field is not file field.'));
        }
        if (!$field->getIsDropzone()) {
            throw new InvalidArgumentException(__('Dropzone disabled on this field.'));
        }
        return $field;
    }

    /**
     * @param string $data
     * @return bool
     */
    public function isUri(string $data): bool
    {
        return (bool)preg_match('/^data:\W*(\w+\/\w+);base64,/', $data);
    }

    /**
     * @param string $data
     * @return array
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function getUriData(string $data): array
    {
        if (preg_match('/^data:\W*(\w+\/\w+);base64,/', $data, $mimeType)) {
            $data     = substr($data, strpos($data, ',') + 1);
            $mimeType = strtolower((string)$mimeType[1]);

            $data = str_replace(' ', '+', $data);
            $data = base64_decode((string)$data);

            if ($data === false) {
                throw new InvalidArgumentException(__('Uri decode failed'));
            }

            return [
                self::CONTENT => $data,
                self::MIME_TYPE => $mimeType
            ];

        }
        throw new LogicException(__('Did not find URI data type'));
    }
}