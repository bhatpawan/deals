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


use MageMe\WebForms\Api\Data\FileDropzoneInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\TmpFileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\File\DropzoneUploader;
use MageMe\WebForms\Helper\FileHelper;
use MageMe\WebForms\Model\Field\Context;
use MageMe\WebForms\Model\FileDropzone;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Image extends File
{
    /**
     * Attributes
     */
    const IS_RESIZED = 'is_resized';
    const RESIZE_WIDTH = 'resize_width';
    const RESIZE_HEIGHT = 'resize_height';

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * Image constructor.
     * @param ImageFactory $imageFactory
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
        ImageFactory                       $imageFactory,
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
        parent::__construct(
            $fileHelper,
            $uploader,
            $tmpFileDropzoneRepository,
            $storeManager,
            $fileDropzoneRepository,
            $context,
            $fieldUi,
            $fieldBlock
        );
        $this->imageFactory = $imageFactory;
    }

    /**
     * @inheritDoc
     */
    public function validateFile(array $file): array
    {
        $errors = parent::validateFile($file);
        return $this->validateCompression($file, $errors);
    }

    /**
     * Check image compression
     *
     * @param array $value
     * @param array $errors
     * @return array
     */
    protected function validateCompression(array $value, array $errors = []): array
    {
        if (!@getimagesize($value['tmp_name'])) {
            $errors[] = __('Unsupported image compression: %1', $value['name']);
        }
        return $errors;
    }

    #region type attributes
    /**
     * @inheritDoc
     */
    public function getAllowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }

    /**
     * @inheritDoc
     */
    public function getAllowedSize(): int
    {
        $limit = (int)$this->getData(self::ALLOWED_SIZE);
        $limit = $limit ?: (int)$this->scopeConfig->getValue('webforms/images/upload_limit',
            ScopeInterface::SCOPE_STORE);
        return $limit ?: (int)$this->getForm()->getImagesUploadLimit();
    }

    /**
     * Get resize flag
     *
     * @return bool
     */
    public function getIsResized(): bool
    {
        return (bool)$this->getData(self::IS_RESIZED);
    }

    /**
     * Set resize flag
     *
     * @param bool $isResized
     * @return $this
     */
    public function setIsResized(bool $isResized): Image
    {
        return $this->setData(self::IS_RESIZED, $isResized);
    }

    /**
     * Get maximum width on resize
     *
     * @return int
     */
    public function getResizeWidth(): int
    {
        return (int)$this->getData(self::RESIZE_WIDTH);
    }

    /**
     * Set maximum width on resize
     *
     * @param int $resizeWidth
     * @return $this
     */
    public function setResizeWidth(int $resizeWidth): Image
    {
        return $this->setData(self::RESIZE_WIDTH, $resizeWidth);
    }

    /**
     * Get maximum height on resize
     *
     * @return int
     */
    public function getResizeHeight(): int
    {
        return (int)$this->getData(self::RESIZE_HEIGHT);
    }

    /**
     * Set maximum height on resize
     *
     * @param int $resizeHeight
     * @return $this
     */
    public function setResizeHeight(int $resizeHeight): Image
    {
        return $this->setData(self::RESIZE_HEIGHT, $resizeHeight);
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        $resultId = $options['result_id'] ?? false;
        return $this->getValueForResultTemplate($value, $resultId);
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        if (!$resultId) {
            return '';
        }
        $fileList = [];
        $files    = $this->fileDropzoneRepository->getListByResultAndFieldId($resultId, $this->getId())->getItems();

        /** @var FileDropzone $file */
        foreach ($files as $file) {
            $htmlContent = '';
            $img         = '<figure><img src="' . $file->getThumbnail(
                    $this->scopeConfig->getValue('webforms/images/email_thumbnail_width'),
                    $this->scopeConfig->getValue('webforms/images/email_thumbnail_height')
                ) . '" alt=""/>';
            $htmlContent .= $img;
            $htmlContent .= '<figcaption>' . $this->fileHelper->getShortFilename($file->getName());
            $htmlContent .= ' <small>[' . $file->getSizeText() . ']</small></figcaption>';
            $htmlContent .= '</figure>';
            $fileList[]  = $htmlContent;
        }
        return implode('<br>', $fileList);
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string
    {
        if (empty($options['result_id'])) {
            return '';
        }
        $files     = $this->fileDropzoneRepository->getListByResultAndFieldId($options['result_id'], $this->getId())->getItems();
        $html      = '';
        $width     = empty($options['image_width']) ? 200 : $options['image_width'];
        $height    = empty($options['image_height']) ? 200 : $options['image_height'];
        $imageLink = !empty($options['image_link']) && (bool)$options['image_link'];

        /** @var FileDropzone $file */
        foreach ($files as $file) {
            if (file_exists($file->getFullPath())) {
                $thumbnail = $file->getThumbnail($width, $height);
                $img       = '<img src="' . $thumbnail . '"/>';
                if ($imageLink) {
                    $img = "<a href='" . $file->getDownloadLink() . "'>" . $img . "</a>";
                }
                if ($thumbnail) {
                    $html .= '<div class="webforms-image">' . $img . '</div>';
                }
            }
        }
        return $html;
    }

    #endregion

    /**
     * @inheritDoc
     */
    public function getValueForResultAdminhtml($value, array $options = []): string
    {
        if (empty($options['result_id'])) {
            return '';
        }
        /** @var FileDropzoneInterface $files */
        $files = $this->fileDropzoneRepository->getListByResultAndFieldId(
            $options['result_id'],
            $this->getId()
        )->getItems();
        $html  = '';
        foreach ($files as $file) {
            if (file_exists($file->getFullPath())) {
                $width  = $this->scopeConfig->getValue('webforms/images/grid_thumbnail_width');
                $height = $this->scopeConfig->getValue('webforms/images/grid_thumbnail_height');
                if ($file->getThumbnail($width, $height)) {

                    $html .= '<a class="webforms-image-box webforms-file-link" href="javascript:void(0)" onclick="setLocation(\'' . $file->getDownloadLink() . '\')">
                            <figure>
                                <p><img src="' . $file->getThumbnail($width, $height) . '" alt="image"/></p>
                                <figcaption>' . $this->fileHelper->getShortFilename($file->getName()) . ' [' . $file->getSizeText() . ']</figcaption>
                            </figure>
                        </a>';
                } else {
                    $html .= '<nobr><a class="webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $this->fileHelper->getShortFilename($file->getName()) . ' [' . $file->getSizeText() . ']</a></nobr>';
                }
            } else {
                $html .= '<nobr><a class="webforms-file-link" href="javascript:alert(\'' . __('File not found.') . '\')">' . $this->fileHelper->getShortFilename($file->getName()) . ' [' . $file->getSizeText() . ']</a></nobr>';
            }
        }
        return $html;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getValueForResultAfterSave($value, ResultInterface $result)
    {
        $value = parent::getValueForResultAfterSave($value, $result);
        $this->resizeImages($result);
        return $value;
    }

    /**
     *
     * @throws LocalizedException
     */
    public function resizeImages(ResultInterface $result)
    {
        if (!$this->getIsResized()) return;

        $width  = $this->getResizeWidth();
        $height = $this->getResizeHeight();
        if (!$width && !$height) return;

        $files = $this->fileDropzoneRepository->getListByResultAndFieldId($result->getId(),
            $this->getId())->getItems();

        /** @var FileDropzone $file */
        foreach ($files as $file) {
            $imageUrl  = $file->getFullPath();
            $file_info = @getimagesize($imageUrl);
            if (!$file_info) continue;

            // skip bmp files
            if (strstr((string)$file_info["mime"], "bmp")) continue;
            if (!file_exists($imageUrl)) continue;
            $file->setMemoryForImage();
            $imageObj = @$this->imageFactory->create($imageUrl);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepTransparency(true);
            if (!$width) {
                $width = $imageObj->getOriginalWidth();
            }
            $imageObj->resize($width, $height);
            $imageObj->save($imageUrl);
            unset($imageObj);
            clearstatcache();
            $size = filesize($imageUrl);
            if ($size === false) $size = null;
            $file->setSize($size);
            $this->fileDropzoneRepository->save($file);
        }
    }
}
