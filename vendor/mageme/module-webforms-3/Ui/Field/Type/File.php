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
use MageMe\WebForms\Api\FileDropzoneRepositoryInterface;
use MageMe\WebForms\Api\Ui\FieldResultFormInterface;
use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\BodyTmpl;
use MageMe\WebForms\Ui\Component\Result\Listing\Column\Field;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form;

class File extends AbstractField implements FieldResultListingColumnInterface, FieldResultFormInterface
{
    const ALLOWED_EXTENSIONS = Type\File::ALLOWED_EXTENSIONS;
    const ALLOWED_SIZE = Type\File::ALLOWED_SIZE;
    const IS_DROPZONE = Type\File::IS_DROPZONE;
    const DROPZONE_TEXT = Type\File::DROPZONE_TEXT;
    const DROPZONE_MAX_FILES = Type\File::DROPZONE_MAX_FILES;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var FileDropzoneRepositoryInterface
     */
    protected $fileDropzoneRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        UrlInterface                    $urlBuilder,
        FileDropzoneRepositoryInterface $fileDropzoneRepository,
        StoreManagerInterface           $storeManager
    )
    {
        $this->urlBuilder             = $urlBuilder;
        $this->fileDropzoneRepository = $fileDropzoneRepository;
        $this->storeManager           = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::ALLOWED_EXTENSIONS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Textarea::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::ALLOWED_EXTENSIONS,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Allowed File Extensions'),
                                    'additionalInfo' => __('Specify allowed file extensions separated by newline. Example:<br><i>doc<br>txt<br>pdf</i>'),
                                    'rows' => 5,
                                ]
                            ]
                        ]
                    ],
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
                                    'sortOrder' => 66,
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
                                    'sortOrder' => 67,
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
                                    'sortOrder' => 68,
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
                                    'sortOrder' => 69,
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
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config                          = $this->getDefaultResultAdminFormConfig();
        $config['type']                  = 'file';
        $config['dropzone']              = $this->getField()->getIsDropzone();
        $config['dropzone_name']         = $config['name'];
        $config['dropzone_text']         = $this->getField()->getDropzoneText();
        $config['dropzone_maxfiles']     = $this->getField()->getDropzoneMaxFiles();
        $config['allowed_size']          = $this->getField()->getAllowedSize();
        $config['allowed_extensions']    = $this->getField()->getAllowedExtensions();
        $config['restricted_extensions'] = $this->getField()->getRestrictedExtensions();
        $config['field_id']              = $this->getField()->getId();
        $config['result_id']             = $result->getId();
        $config['name']                  = 'file_' . $this->getField()->getId();
        $config['required']              = false;

        $config['dropzone_url'] = $this->urlBuilder->getUrl('webforms/file/dropzoneUpload', []);
        return $config;
    }

}
