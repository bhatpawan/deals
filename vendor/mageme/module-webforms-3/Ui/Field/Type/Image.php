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


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\BodyTmpl;
use MageMe\WebForms\Ui\Component\Result\Listing\Column\Field;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\Form;

class Image extends File
{
    const IS_RESIZED = Type\Image::IS_RESIZED;
    const RESIZE_WIDTH = Type\Image::RESIZE_WIDTH;
    const RESIZE_HEIGHT = Type\Image::RESIZE_HEIGHT;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::ALLOWED_SIZE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::ALLOWED_SIZE,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Allowed File Size'),
                                    'additionalInfo' => __('Specify maximum allowed file size in kilobytes'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_DROPZONE => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_DROPZONE,
                                    'visible' => 0,
                                    'sortOrder' => 66,
                                    'label' => __('Enable Dropzone'),
                                    'additionalInfo' => __('Dropzone allows you to upload multiple files at once and also adds drag and drop functionality'),
                                    'default' => '1',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::DROPZONE_TEXT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::DROPZONE_TEXT,
                                    'visible' => 0,
                                    'sortOrder' => 67,
                                    'label' => __('Dropzone Text'),
                                    'additionalInfo' => __('Set custom text in the dropzone'),
                                    'default' => __('Add files or drop here'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::DROPZONE_MAX_FILES => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::DROPZONE_MAX_FILES,
                                    'visible' => 0,
                                    'sortOrder' => 68,
                                    'label' => __('Maximum Files In Dropzone'),
                                    'additionalInfo' => __('Set maximum number of files to be uploaded through dropzone. Default value is 5'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                    'default' => 5,
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_RESIZED => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_RESIZED,
                                    'visible' => 0,
                                    'sortOrder' => 68,
                                    'label' => __('Resize Uploaded Image'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::RESIZE_WIDTH => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::RESIZE_WIDTH,
                                    'visible' => 0,
                                    'sortOrder' => 69,
                                    'label' => __('Maximum Width'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::RESIZE_HEIGHT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::RESIZE_HEIGHT,
                                    'visible' => 0,
                                    'sortOrder' => 70,
                                    'label' => __('Maximum Height'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                ]
                            ]
                        ]
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
        $config                  = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['bodyTmpl']      = BodyTmpl::HTML;
        $config['disableAction'] = true;
        return $config;
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config                 = parent::getResultAdminFormConfig($result);
        $config['type']         = 'image';
        $config['dropzone_url'] = $this->storeManager->getStore()->getUrl('webforms/file/dropzoneUpload');
        return $config;
    }
}
