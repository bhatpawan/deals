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

namespace MageMe\WebForms\Model\Field\Type;


use finfo;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\TmpFileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Api\Utility\Field\UploadFieldInterface;
use MageMe\WebForms\Controller\Adminhtml\Result\Save;
use MageMe\WebForms\File\DropzoneUploader;
use MageMe\WebForms\Helper\FileHelper;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\Field\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Validate_Exception;
use Zend_Validate_File_Upload;

class File extends AbstractField implements UploadFieldInterface
{

    /**
     * Attributes
     */
    const ALLOWED_EXTENSIONS = 'allowed_extensions';
    const ALLOWED_SIZE = 'allowed_size';
    const IS_DROPZONE = 'is_dropzone';
    const DROPZONE_TEXT = 'dropzone_text';
    const DROPZONE_MAX_FILES = 'dropzone_max_files';


    /**
     * @var FileDropzoneRepositoryInterface
     */
    protected $fileDropzoneRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TmpFileDropzoneRepositoryInterface
     */
    protected $tmpFileDropzoneRepository;

    /**
     * @var DropzoneUploader
     */
    protected $uploader;

    /**
     * @var FileHelper
     */
    protected $fileHelper;

    /**
     * File constructor.
     * @param FileHelper $fileHelper
     * @param DropzoneUploader $uploader
     * @param TmpFileDropzoneRepositoryInterface $tmpFileDropzoneRepository
     * @param StoreManagerInterface $storeManager
     * @param FileDropzoneRepositoryInterface $fileDropzoneRepository
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        FileHelper                         $fileHelper,
        DropzoneUploader                   $uploader,
        TmpFileDropzoneRepositoryInterface $tmpFileDropzoneRepository,
        StoreManagerInterface              $storeManager,
        FileDropzoneRepositoryInterface    $fileDropzoneRepository,
        Context                            $context,
        FieldUiInterface                   $fieldUi,
        FieldBlockInterface                $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->fileDropzoneRepository    = $fileDropzoneRepository;
        $this->storeManager              = $storeManager;
        $this->tmpFileDropzoneRepository = $tmpFileDropzoneRepository;
        $this->uploader                  = $uploader;
        $this->fileHelper                = $fileHelper;
    }

    #region type attributes
    /**
     * Get allowed extensions
     *
     * @return array
     */
    public function getAllowedExtensions(): array
    {
        $allowed_extensions = explode("\n", trim((string)$this->getData(self::ALLOWED_EXTENSIONS)));
        $allowed_extensions = array_map('trim', $allowed_extensions);
        $allowed_extensions = array_map('strtolower', $allowed_extensions);
        $filtered_result    = [];
        foreach ($allowed_extensions as $ext) {
            if (strlen($ext) > 0) {
                $filtered_result[] = $ext;
            }
        }
        return $filtered_result;
    }

    /**
     * Set allowed extensions
     *
     * @param string $allowedExtensions
     * @return $this
     */
    public function setAllowedExtensions(string $allowedExtensions): File
    {
        return $this->setData(self::ALLOWED_EXTENSIONS, $allowedExtensions);
    }

    /**
     * Get files allowed size
     *
     * @return int
     * @throws LocalizedException
     */
    public function getAllowedSize(): int
    {
        $limit = (int)$this->getData(self::ALLOWED_SIZE);
        $limit = $limit ?: (int)$this->scopeConfig->getValue('webforms/files/upload_limit',
            ScopeInterface::SCOPE_STORE);
        return $limit ?: (int)$this->getForm()->getFilesUploadLimit();
    }

    /**
     * Set files allowed size
     *
     * @param int $allowedSize
     * @return $this
     */
    public function setAllowedSize(int $allowedSize): File
    {
        return $this->setData(self::ALLOWED_SIZE, $allowedSize);
    }

    /**
     * Get dropzone flag
     *
     * @return bool
     */
    public function getIsDropzone(): bool
    {
        return (bool)$this->getData(self::IS_DROPZONE);
    }

    /**
     * Set dropzone flag
     *
     * @param bool $isDropzone
     * @return $this
     */
    public function setIsDropzone(bool $isDropzone): File
    {
        return $this->setData(self::IS_DROPZONE, $isDropzone);
    }

    /**
     * Get dropzone text
     *
     * @return Phrase|string
     */
    public function getDropzoneText()
    {
        $text = (string)$this->getData(self::DROPZONE_TEXT);
        return $text ? str_replace("'", "\'", $text) : __('Add files or drop here');
    }

    /**
     * Set dropzone text
     *
     * @param string $dropzoneText
     * @return $this
     */
    public function setDropzoneText(string $dropzoneText): File
    {
        return $this->setData(self::DROPZONE_TEXT, $dropzoneText);
    }

    /**
     * Get dropzone max files
     *
     * @return int
     */
    public function getDropzoneMaxFiles(): int
    {
        $limit = (int)$this->getData(self::DROPZONE_MAX_FILES);
        return $limit ?: 5;
    }

    /**
     * Set dropzone max files
     *
     * @param int $dropzoneMaxFiles
     * @return $this
     */
    public function setDropzoneMaxFiles(int $dropzoneMaxFiles): File
    {
        return $this->setData(self::DROPZONE_MAX_FILES, $dropzoneMaxFiles);
    }
    #endregion

    /**
     * @inheritdoc
     */
    public function getFilteredFieldValue()
    {
        $result = $this->getData('result');
        if (($result instanceof DataObject)) {
            $resultId = $result->getData('result_id');
            if ($this->getId() && $resultId) {
                return $this->fileDropzoneRepository->getListByResultAndFieldId($resultId, $this->getId())->getItems();
            }
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation = parent::getValidation();
        if ($this->getIsRequired()) {
            unset($validation['rules']['required-entry']);
            $validation['rules']['required-dropzone-file'] = "'required-dropzone-file':true";
            if ($this->getValidationRequiredMessage()) {
                unset($validation['descriptions']['data-msg-required-entry']);
                $validation['descriptions']['data-msg-required-dropzone-file'] = $this->getValidationRequiredMessage();
            }
        }
        return $validation;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        $fileList = [];

        /** @var FileDropzoneInterface $files */
        $files    = $this->fileDropzoneRepository->getListByResultAndFieldId($resultId, $this->getId())->getItems();
        foreach ($files as $file) {
            $htmlContent = $file->getName();
            $htmlContent .= ' <small>[' . $file->getSizeText() . ']</small>';
            $fileList[]  = $htmlContent;
        }
        return implode('<br>', $fileList);
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        $resultId = $options['result_id'] ?? false;
        if (!$resultId) {
            return '';
        }
        $recipient       = $options['recipient'] ?? 'admin';
        $isAdminhtml     = $options['adminhtml_downloads'] ?? false;
        $isExplicitLinks = $options['explicit_links'] ?? false;

        $fileList = [];

        /** @var FileDropzoneInterface $files */
        $files    = $this->fileDropzoneRepository->getListByResultAndFieldId($resultId, $this->getId())->getItems();
        foreach ($files as $file) {
            $htmlContent = '';
            if ($recipient == 'admin' &&
                ($this->getForm()->getIsFrontendDownloadAllowed() || $isExplicitLinks)) {
                $htmlContent .= '<a href="' . $file->getDownloadLink($isAdminhtml) . '">' . $file->getName() . '</a>';
            } else {
                $htmlContent .= $file->getName();
            }
            $htmlContent .= ' [' . $file->getSizeText() . ']';
            $fileList[]  = $htmlContent;
        }
        return implode('<br>', $fileList);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAdminhtml($value, array $options = []): string
    {
        $resultId = $options['result_id'] ?? false;
        if (!$resultId) {
            return '';
        }

        /** @var FileDropzoneInterface $files */
        $files = $this->fileDropzoneRepository->getListByResultAndFieldId($resultId, $this->getId())->getItems();
        $html  = '';
        foreach ($files as $file) {
            $file->setName(htmlentities((string)$file->getName()));
            if (file_exists($file->getFullPath())) {
                $html .= '<nobr><a class="webforms-file-link" title="' . $file->getName() . '" href="javascript:void(0)" onclick="setLocation(\'' . $file->getDownloadLink(true) . '\')">' . $this->fileHelper->getShortFilename($file->getName()) . ' [' . $file->getSizeText() . ']</a></nobr>';
            } else {
                $html .= '<nobr><a class="webforms-file-link" title="' . $file->getName() . '" href="javascript:alert(\'' . __('File not found.') . '\')">' . $this->fileHelper->getShortFilename($file->getName()) . ' [' . $file->getSizeText() . ']</a></nobr>';
            }
        }
        return $html;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAdminGrid($value, array $options = [])
    {
        return $this->getValueForResultAdminhtml($value, $options);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string
    {
        if (empty($options['result_id'])) {
            return '';
        }

        /** @var FileDropzoneInterface $files */
        $files = $this->fileDropzoneRepository->getListByResultAndFieldId($options['result_id'], $this->getId())->getItems();
        $html  = '';
        foreach ($files as $file) {
            if (file_exists($file->getFullPath())) {
                $html .= '<div class="webforms-file"><a href="' . $file->getDownloadLink(false) . '">' . $file->getName() . '</a></div>';
            }
        }
        return $html;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getPostErrors(array $postData, bool $logicVisibility, array $config = []): array
    {
        if (isset($config[Save::ADMIN_SAVE]) && $config[Save::ADMIN_SAVE]) {
            $this->setIsRequired(false);
        }

        $errors = parent::getPostErrors($postData, $logicVisibility);
        if ($this->getIsDropzone()) {
            if ($this->getDropzoneMaxFiles()) {
                if (!$this->validateMaxFiles($postData)) {
                    $errors[] = __('Maximum %1 files in dropzone. Please remove upload or select some files to delete', $this->getDropzoneMaxFiles());
                }
            }
        } else {
            $file = $this->getSimpleUploadedFile();
            if ($file) {
                array_merge($errors, $this->validateFile($file));
            }
        }
        return $errors;
    }

    /**
     * @param array $postData
     * @param bool $logicVisibility
     * @return bool
     * @throws Zend_Validate_Exception
     */
    public function validatePostRequired(array $postData, bool $logicVisibility): bool
    {
        if (!$logicVisibility) {
            return true;
        }
        $deleted = 0;
        $existed = 0;
        if (!empty($postData['result_id'])) {
            $deleted = empty($postData['delete_file_' . $this->getId()]) ? 0 : count($postData['delete_file_' . $this->getId()]);
            $existed = $this->fileDropzoneRepository->getListByResultAndFieldId($postData['result_id'],
                $this->getId())->getTotalCount();
        }
        if ($this->getIsDropzone()) {
            $value = (string)$postData['field'][$this->getId()];
            $uploaded = $value ? count(explode(';', $value)) : 0;
            return $uploaded + $existed - $deleted > 0;
        } else {
            $file = $this->getSimpleUploadedFile();
            if ($file) {
                return true;
            }
            return $existed - $deleted > 0;
        }
    }

    /**
     * @param array $config
     * @inheritDoc
     * @throws Zend_Validate_Exception
     * @throws NoSuchEntityException|CouldNotDeleteException
     */
    public function preparePostData(
        array &$postData,
        array $config = [],
        ResultInterface $result = null,
        bool $isAdmin = false
    ): FieldInterface
    {
        // remove files if hidden by logic
        if ($result && $result->getId()) {
            if (isset($postData['logic_visibility'])
                && isset($postData['logic_visibility'][$this->getId()])
            ) {
                if (!$postData['logic_visibility'][$this->getId()]) {

                    /** @var FileDropzoneInterface[] $files */
                    $files = $this->fileDropzoneRepository->getListByResultAndFieldId($result->getId(), $this->getId())->getItems();
                    foreach ($files as $file) {
                        $this->fileDropzoneRepository->delete($file);
                    }
                    return $this;
                }
            }
        }

        // delete files
        if (!empty($postData['delete_file_' . $this->getId()])) {
            foreach ($postData['delete_file_' . $this->getId()] as $hash) {
                $resultFile = $this->fileDropzoneRepository->getByHash($hash);
                $this->fileDropzoneRepository->delete($resultFile);
            }
            $postData['delete_file_' . $this->getId()] = [];
        }

        if (!$this->getIsDropzone()) {
            $file = $this->getSimpleUploadedFile();
            if ($file) {
                $postData['field'][$this->getId()] = $file['name'];
            }
        }

        return parent::preparePostData($postData, $config, $result, $isAdmin);
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getUploadLimit(): int
    {
        return $this->getForm()->getUploadLimit();
    }

    /**
     * @return false|array
     * @throws Zend_Validate_Exception
     */
    public function getSimpleUploadedFile()
    {
        $fileId   = 'file_' . $this->getId();
        $uploader = new Zend_Validate_File_Upload;
        $valid    = $uploader->isValid($fileId);
        if ($valid) {
            $file = $uploader->getFiles($fileId);
            return $file[$fileId];
        }
        return false;
    }

    #region File validation
    /**
     * Validate uploaded file
     *
     * @param array $file
     * @return array
     * @throws LocalizedException
     */
    public function validateFile(array $file): array
    {
        $errors = [];
        if (!empty($file['error']) && $file['error'] == UPLOAD_ERR_INI_SIZE) {
            $errors[] = __('Uploaded file %1 exceeds allowed limit: %2', $file['name'],
                ini_get('upload_max_filesize'));
        }
        if (isset($file['name']) && file_exists($file['tmp_name'])) {
            $errors = $this->validateSize($file, $errors);
            $errors = $this->validateAllowedExtensions($file, $errors);
            $errors = $this->validateRestrictedExtensions($file, $errors);
            $errors = $this->validateFilename($file, $errors);
        }
        return $errors;
    }

    /**
     * Check file size
     *
     * @param array $value
     * @param array $errors
     * @return array
     * @throws LocalizedException
     */
    protected function validateSize(array $value, array $errors = []): array
    {
        $fileSize     = round($value['size'] / 1024);
        $upload_limit = $this->getAllowedSize();
        if ($upload_limit > 0 && $fileSize > $upload_limit) {
            $errors[] = __('Uploaded file %1 (%2 kB) exceeds allowed limit: %3 kB', $value['name'], $fileSize,
                $this->getAllowedSize());
        }
        return $errors;
    }



    /**
     * Check for allowed extensions
     *
     * @param array $value
     * @param array $errors
     * @return array
     */
    protected function validateAllowedExtensions(array $value, array $errors = []): array
    {
        $allowed_extensions = $this->getAllowedExtensions();
        if (count($allowed_extensions)) {
            preg_match('/\.([^.]+)$/', (string)$value['name'], $matches);
            $file_ext = strtolower((string)$matches[1]);

            // check file extension
            if (!in_array($file_ext, $allowed_extensions)) {
                $errors[] = __('Uploaded file %1 has none of allowed extensions: %2', $value['name'],
                    implode(', ', $allowed_extensions));
            }
        }
        return $errors;
    }

    /**
     * Check for restricted extensions
     *
     * @param array $value
     * @param array $errors
     * @return array
     */
    protected function validateRestrictedExtensions(array $value, array $errors = []): array
    {
        $restricted_extensions = $this->getRestrictedExtensions();
        if (count($restricted_extensions)) {
            preg_match('/\.([^.]+)$/', (string)$value['name'], $matches);
            $file_ext = strtolower((string)$matches[1]);
            if (in_array($file_ext, $restricted_extensions)) {
                $errors[] = __('Uploading of potentially dangerous files is not allowed.');
            }
            if (class_exists('\finfo')) {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $type  = (string)$finfo->file($value['tmp_name']);
                if (strstr($type, 'php') || strstr($type, 'python') || strstr($type, 'perl')) {
                    $errors[] = __('Uploading of potentially dangerous files is not allowed.');
                }
            }
        }
        return $errors;
    }

    /**
     * Check for valid filename
     *
     * @param array $value
     * @param array $errors
     * @return array
     */
    protected function validateFilename(array $value, array $errors = []): array
    {
        if ($this->scopeConfig->getValue('webforms/files/validate_filename') && !preg_match('/^[a-zA-Z0-9_\s-.]+$/',
                (string)$value['name'])) {
            $errors[] = __('Uploaded file %1 has non-latin characters in the name', $value['name']);
        }
        return $errors;
    }

    /**
     * Get potentially dangerous extensions
     *
     * @return array
     */
    public function getRestrictedExtensions(): array
    {
        return [
            'php',
            'pl',
            'py',
            'jsp',
            'asp',
            'htm',
            'html',
            'js',
            'sh',
            'shtml',
            'cgi',
            'com',
            'exe',
            'bat',
            'cmd',
            'vbs',
            'vbe',
            'jse',
            'wsf',
            'wsh',
            'psc1'
        ];
    }

    /**
     * Check files count
     *
     * @param array $postData
     * @return bool
     */
    protected function validateMaxFiles(array $postData): bool
    {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $uploaded = count(explode(';', (string)$fields[$this->getId()]));
        if (!empty($postData['result_id'])) {
            $deleted = empty($postData['delete_file_' . $this->getId()]) ? 0 : count($postData['delete_file_' . $this->getId()]);
            $existed = $this->fileDropzoneRepository->getListByResultAndFieldId($postData['result_id'], $this->getId())->getTotalCount();
            $uploaded = $uploaded + $existed - $deleted;
        }
        return $uploaded <= $this->getDropzoneMaxFiles();
    }
    #endregion

    /**
     * @inheritdoc
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function getValueForResultAfterSave($value, ResultInterface $result)
    {
        if (!$this->getIsDropzone()) {
            $file = $this->getSimpleUploadedFile();
            if ($file) {
                $files = $this->uploader->uploadResultFiles($result, [$this->getId() => $file]);
                foreach ($files as $item) {
                    return $item->getLinkHash();
                }
            }
            return '';
        }

        $counter    = 0;
        $maxFiles   = $this->getDropzoneMaxFiles();
        $hash_array = is_string($value) ? explode(';', $value) : $value;
        foreach ($hash_array as $hash) {
            try {
                $tmpFile = $this->tmpFileDropzoneRepository->getByHash($hash);
                $this->uploader->copyFileFromTmpDir($tmpFile, $result);
                $this->tmpFileDropzoneRepository->delete($tmpFile);
                $counter++;
            } catch (NoSuchEntityException $e) {
            }
            if ($counter >= $maxFiles) {
                break;
            }
        }
        $fileList = $this->fileDropzoneRepository->getListByResultAndFieldId($result->getId(), $this->getId());
        if ($fileList->getTotalCount() > $maxFiles) {

            /** @var FileDropzoneInterface[] $files */
            $files = $fileList->getItems();
            usort($files, function (FileDropzoneInterface $a, FileDropzoneInterface $b) {
                if ($a->getId() == $b->getId()) {
                    return 0;
                }
                return ($a->getId() < $b->getId()) ? -1 : 1;
            });
            for ($i = 0; $i < $fileList->getTotalCount() - $maxFiles; $i++) {
                $this->fileDropzoneRepository->delete($files[$i]);
            }
        }

        $hashes = [];

        /** @var FileDropzoneInterface[] $files */
        $files = $this->fileDropzoneRepository->getListByResultAndFieldId($result->getId(), $this->getId())->getItems();
        foreach ($files as $file) {
            $hashes[] = $file->getLinkHash();
        }
        return empty($hashes) ? '' : implode(';', $hashes);
    }

    /**
     * @inheritdoc
     */
    public function getValueForSubject($value)
    {
        $fileList = [];
        $hashes = is_array($value) ? $value : explode(';', (string)$value);
        foreach ($hashes as $hash) {
            try {
                $file = $this->fileDropzoneRepository->getByHash($hash);
                $fileList[] = $file->getName();
            } catch (NoSuchEntityException $e) {
                $fileList[] = $hash;
            }
        }
        return implode(', ', $fileList);
    }
}
