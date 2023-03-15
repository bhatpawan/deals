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


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\FileGalleryRepositoryInterface;
use MageMe\WebForms\Api\TmpFileGalleryRepositoryInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\File\GalleryUploader;
use MageMe\WebForms\Model\Field\Context;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Gallery extends Select
{
    const IMAGES = 'images';
    const IMAGE_WIDTH = 'image_width';
    const IMAGE_HEIGHT = 'image_height';
    const IS_LABELED = 'is_labeled';

    /**
     * @var FileGalleryRepositoryInterface
     */
    protected $fileGalleryRepository;
    /**
     * @var TmpFileGalleryRepositoryInterface
     */
    protected $tmpFileGalleryRepository;
    /**
     * @var GalleryUploader
     */
    protected $uploader;

    /**
     * Gallery constructor.
     * @param GalleryUploader $uploader
     * @param TmpFileGalleryRepositoryInterface $tmpFileGalleryRepository
     * @param FileGalleryRepositoryInterface $fileGalleryRepository
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        GalleryUploader                   $uploader,
        TmpFileGalleryRepositoryInterface $tmpFileGalleryRepository,
        FileGalleryRepositoryInterface    $fileGalleryRepository,
        Context                           $context,
        FieldUiInterface                  $fieldUi,
        FieldBlockInterface               $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);

        $this->fileGalleryRepository    = $fileGalleryRepository;
        $this->tmpFileGalleryRepository = $tmpFileGalleryRepository;
        $this->uploader                 = $uploader;
    }

    /**
     * @inheritDoc
     */
    public function getFilteredFieldValue()
    {
        $images         = $this->getImages();
        $customer_value = $this->getCustomerValue();
        if ($customer_value) {
            if (!is_array($customer_value)) {
                $customer_value = explode("\n", (string)$customer_value);
            }
            foreach ($images as &$image) {
                $image['selected'] = in_array($image['value_id'], $customer_value);
            }
        }
        return $images;
    }

    /**
     * Get images
     *
     * @param bool $sort
     * @return array
     */
    public function getImages(bool $sort = false): array
    {
        $result = $this->getData(self::IMAGES);
        if (empty($result) || !is_array($result)) {
            return [];
        }
        if ($sort) {
            $result = $this->sortImagesByPosition($result);
        }
        return $result;
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     * @return array
     */
    private function sortImagesByPosition(array $images): array
    {
        usort(
            $images,
            function ($imageA, $imageB) {
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            }
        );
        return $images;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->getImages(true) as $image) {
            if ($image['disabled']) continue;
            $options[] = [
                'label' => $image['label'],
                'value' => $image['value_id'],
                'url' => $image['url'],
            ];
        }
        return $options;
    }

    /**
     * Get images width
     *
     * @return int
     */
    public function getImagesWidth()
    {
        return (int)$this->getData(self::IMAGE_WIDTH) ?: '';
    }

    /**
     * Get images height
     *
     * @return int
     */
    public function getImagesHeight()
    {
        return (int)$this->getData(self::IMAGE_HEIGHT) ?: '';
    }

    /**
     * Get show label flag
     *
     * @return bool
     */
    public function getIsLabeled(): bool
    {
        return (bool)$this->getData(self::IS_LABELED);
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): string
    {
        $options = '';
        foreach ($this->getImages(true) as $image) {
            if ($image['disabled']) continue;
            $options = empty($options) ? $options . $image['value_id'] :
                $options . "\n" . $image['value_id'];
        }
        return $options;
    }

    /**
     * Set images
     *
     * @param array $images
     * @return $this
     */
    public function setImages(array $images): Gallery
    {
        return $this->setData(self::IMAGES, $images);
    }

    /**
     * Set images width
     *
     * @param int $imagesWidth
     * @return $this
     */
    public function setImagesWidth(int $imagesWidth): Gallery
    {
        return $this->setData(self::IMAGE_WIDTH, $imagesWidth);
    }

    /**
     * Set images height
     *
     * @param int $imagesHeight
     * @return $this
     */
    public function setImagesHeight(int $imagesHeight): Gallery
    {
        return $this->setData(self::IMAGE_HEIGHT, $imagesHeight);
    }

    #endregion

    /**
     * Set show label flag
     *
     * @param bool $isLabeled
     * @return $this
     */
    public function setIsLabeled(bool $isLabeled): Gallery
    {
        return $this->setData(self::IS_LABELED, $isLabeled);
    }

    /**
     * Get image html
     *
     * @param $image
     * @return string
     */
    public function getImageHtml($image): string
    {
        $html = '<div>';
        $html .= '<img class="product-image"
                data-role="image-element"
                src="' . $image['url'] . '"
                alt="' . $image['label'] . '"
                width="' . $this->getImagesWidth() . '"
                height="' . $this->getImagesHeight() . '">';
        if ($this->getIsLabeled()) {
            $html .= '<p>' . $image['label'] . '</p>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function processTypeAttributesOnSave(array &$data, int $storeId): FieldInterface
    {
        if (empty($data[self::IMAGES])) return $this;
        $result = [];
        $images = $this->getImages();
        foreach ($data[self::IMAGES] as $item) {

            // skip old images
            if (!isset($item['removed'])) {
                continue;
            }
            if ($item['removed']) {
                continue;
            }
            if ($item['value_id']) {
                foreach ($images as $image) {
                    if ($image['value_id'] == $item['value_id']) {
                        $result[] = [
                            'value_id' => $image['value_id'],
                            'file' => $image['file'],
                            'media_type' => $image['media_type'],
                            'label' => $item['label'],
                            'position' => $item['position'],
                            'disabled' => $item['disabled'],
                            'url' => $image['url'],
                            'size' => $image['size'],
                        ];
                        break;
                    }
                }
                continue;
            }
            if ($item['hash']) {
                $tmpFile = $this->tmpFileGalleryRepository->getByHash($item['hash']);
                $file    = $this->uploader->copyFileFromTmpDir($tmpFile, $this);
                $this->tmpFileGalleryRepository->delete($tmpFile);

                $result[] = [
                    'value_id' => $file->getId(),
                    'file' => $file->getName(),
                    'media_type' => 'image',
                    'label' => $item['label'],
                    'position' => $item['position'],
                    'disabled' => $item['disabled'],
                    'url' => $file->getImageUrl($storeId),
                    'size' => $file->getSize(),
                ];
            }
        }
        $this->uploader->cleanupTmp();
        $data[self::IMAGES] = $result;
        return $this;
    }

    /**
     * @param mixed $value
     * @param array $options
     * @return string
     */
    public function getValueForResultHtml($value, array $options = []): string
    {
        $width  = $this->scopeConfig->getValue('webforms/images/grid_thumbnail_width');
        $height = $this->scopeConfig->getValue('webforms/images/grid_thumbnail_height');

        return $this->getValueHtml($value, $width, $height);
    }

    public function getValueHtml($value, $width, $height): string
    {
        $images = $this->getImages();
        $values = $this->parseValue($value);
        $html   = '';
        foreach ($values as $value) {
            foreach ($images as $image) {
                if ($image['value_id'] == $value) {
                    try {
                        $file = $this->fileGalleryRepository->getById($value);
                        if (file_exists($file->getFullPath())) {
                            $html .= '<div class="webforms-image-box"><figure><p>' .
                                '<img src="' . $file->getThumbnail($width, $height) . '" width="' . $width . '" alt="gallery"/></p>';
                            if (!empty($image['label'])) {
                                $html .= '<figcaption>' . $image['label'] . '</figcaption>';
                            }
                            $html .= '</figure></div>';
                        } else {
                            $html .= '<div class="webforms-file-link-name">' .
                                '<p>' . __('File not found.') . '</p>';
                            if (!empty($image['label'])) {
                                $html .= '<p>' . $image['label'] . '</p>';
                            }
                            $html .= '</div>';
                        }
                    } catch (NoSuchEntityException $e) {
                        $html .= '<div class="error">' . $e->getMessage() . '</div>';
                    }
                    break;
                }
            }
        }
        return $html;
    }

    /**
     * @param $data
     * @return array
     */
    public function parseValue($data): array
    {
        if (empty($data)) {
            return [];
        }
        return is_string($data) ? explode("\n", $data) : $data;
    }

    /**
     * @inheritDoc
     */
    public function processColumnDataSource(array &$dataSource): FieldInterface
    {
        $fieldName = 'field_' . $this->getId();
        $width     = $this->scopeConfig->getValue('webforms/images/grid_thumbnail_width');
        $height    = $this->scopeConfig->getValue('webforms/images/grid_thumbnail_height');
        foreach ($dataSource['data']['items'] as & $item) {
            if (!isset($item[$fieldName])) {
                return $this;
            }

            $item[$fieldName] = $this->getValueHtml($item[$fieldName], $width, $height);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValueForExport($value, ?int $resultId = null)
    {
        $valueArray = [];
        $values     = $this->parseValue($value);
        foreach ($values as $id) {
            $label = $this->getImageLabel($id);
            if ($label) $valueArray[$id] = $label;
            else {
                try {
                    $file = $this->fileGalleryRepository->getById($id);
                    $name = file_exists($file->getFullPath()) ? $file->getName() : __('File not found.');
                } catch (NoSuchEntityException $e) {
                    $name = $e->getMessage();
                }
                $valueArray[$id] = $name;
            }
        }
        return implode(";", $valueArray);
    }

    /**
     * @param $value
     * @return string
     */
    public function getImageLabel($value): string
    {
        $images = $this->getImages();
        foreach ($images as $image) {
            if ($image['value_id'] == $value) {
                return $image['label'] ?? '';
            }
        }
        return '';
    }

    /**
     * @param string $value
     * @param array $options
     * @return string
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string
    {
        return $this->getValueForResultHtml($value, $options);
    }

    /**
     * @param array $options
     * @inheritDoc
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = []) {
        return $this->getValueForResultAdminhtml($value);
    }
}
