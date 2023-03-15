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

namespace MageMe\WebForms\Ui\Field\Type;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Data\TmpFileGalleryInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\BodyTmpl;
use Magento\Backend\Model\Image\UploadResizeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Size;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Ui\Component\Form;

class Gallery extends Select
{
    const ID = FieldInterface::ID;
    const IMAGES = Type\Gallery::IMAGES;
    const IMAGE_WIDTH = Type\Gallery::IMAGE_WIDTH;
    const IMAGE_HEIGHT = Type\Gallery::IMAGE_HEIGHT;
    const IS_LABELED = Type\Gallery::IS_LABELED;
    /**
     * @var UploadResizeConfigInterface
     */
    protected $imageUploadConfig;
    /**
     * @var Size
     */
    protected $fileSizeService;
    /**
     * @var StoreManager
     */
    protected $storeManager;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Gallery constructor.
     * @param RequestInterface $request
     * @param StoreManager $storeManager
     * @param Size $fileSizeService
     * @param UploadResizeConfigInterface $imageUploadConfig
     * @param UrlInterface $urlBuilder
     * @param FieldRepositoryInterface $fieldRepository
     */
    public function __construct(
        RequestInterface            $request,
        StoreManager                $storeManager,
        Size                        $fileSizeService,
        UploadResizeConfigInterface $imageUploadConfig,
        UrlInterface                $urlBuilder,
        FieldRepositoryInterface    $fieldRepository
    )
    {
        $this->imageUploadConfig = $imageUploadConfig;
        $this->fileSizeService   = $fileSizeService;
        $this->storeManager      = $storeManager;
        $this->request           = $request;
        $this->fieldRepository   = $fieldRepository;
        $this->urlBuilder        = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        try {
            $id = $this->request->getParam(self::ID);
            $store = $this->request->getParam('store', Store::DEFAULT_STORE_ID);
            if ($id) {
                $gallery = $this->fieldRepository->getById($id, $store);
                $images = $gallery->getImages(true);
            } else {
                $images = [];
            }
            $uploaderUrl = $this->urlBuilder->getUrl('webforms/file/galleryUpload',
                [TmpFileGalleryInterface::FIELD_ID => $id]);
            $scopeName = $store ? $this->storeManager->getStore($store)->getName() : __('All Store Views');
            $deleteConfirmMessage = __('The image will be deleted from scope: %1. Do you want to proceed?', $scopeName);
        } catch (NoSuchEntityException $e) {
            return [];
        }
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::IMAGES => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataScope' => static::IMAGES,
                                    'component' => 'MageMe_WebForms/js/form/element/images',
                                    'elementTmpl' => 'MageMe_WebForms/form/element/images',
                                    'visible' => 0,
                                    'sortOrder' => 64,
                                    'label' => __('Images'),
                                    'images' => $images,
                                    'deleteConfirmMessage' => $deleteConfirmMessage,
                                    'uploader' => [
                                        'url' => $uploaderUrl,
                                        'config' => [
                                            'maxFileSize' => $this->fileSizeService->getMaxFileSize(),
                                            'maxWidth' => $this->imageUploadConfig->getMaxWidth(),
                                            'maxHeight' => $this->imageUploadConfig->getMaxHeight(),
                                            'isResizeEnabled' => $this->imageUploadConfig->isResizeEnabled(),
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_LABELED => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_LABELED,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Show Label Under The Image'),
                                    'additionalInfo' => __('The label of each image will be added below. Click on the image to set the label.'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IMAGE_WIDTH => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::IMAGE_WIDTH,
                                    'visible' => 0,
                                    'sortOrder' => 66,
                                    'label' => __('Maximum Image Width'),
                                    'default' => 150,
                                    'validation' => [
                                        'validate-digits' => true,
                                        'required-entry' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IMAGE_HEIGHT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::IMAGE_HEIGHT,
                                    'visible' => 0,
                                    'sortOrder' => 67,
                                    'label' => __('Maximum Image Height'),
                                    'default' => 150,
                                    'validation' => [
                                        'validate-digits' => true,
                                        'required-entry' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_MULTISELECT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_MULTISELECT,
                                    'visible' => 0,
                                    'sortOrder' => 68,
                                    'label' => __('Multiple Selection'),
                                    'additionalInfo' => __('Select multiple images.'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getLogicValueMeta(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\MultiSelect::NAME,
                        'component' => 'MageMe_WebForms/js/form/element/gallery',
                        'elementTmpl' => 'MageMe_WebForms/form/element/gallery',
                        'visible' => 1,
                        'sortOrder' => 40,
                        'label' => __('Trigger value(s)'),
                        'additionalInfo' => __('Select one or multiple trigger values.<br>Please, configure for each locale <b>Store View</b>.'),
                        'options' => $this->getField()->toOptionArray() ?? [],
                        'validation' => [
                            'required-entry' => true,
                        ],
                        'imagesWidth' => $this->getField()->getImagesWidth(),
                        'imagesHeight' => $this->getField()->getImagesHeight(),
                        'showLabel' => $this->getField()->getIsLabeled(),
                        'multiple' => true,
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getResultListingColumnConfig(int $sortOrder): array
    {
        $config             = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['class']    = \MageMe\WebForms\Ui\Component\Result\Listing\Column\Field\Gallery::class;
        $config['bodyTmpl'] = BodyTmpl::HTML;
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config                       = $this->getDefaultResultAdminFormConfig();
        $config['type']               = \MageMe\WebForms\Block\Adminhtml\Result\Element\Gallery::TYPE;
        $config[self::IMAGES]         = $this->getField()->getImages(true);
        $config[self::IMAGE_WIDTH]    = $this->getField()->getImagesWidth();
        $config[self::IMAGE_HEIGHT]   = $this->getField()->getImagesHeight();
        $config[self::IS_MULTISELECT] = $this->getField()->getIsMultiselect();
        $config[self::IS_LABELED]     = $this->getField()->getIsLabeled();
        return $config;
    }
}
