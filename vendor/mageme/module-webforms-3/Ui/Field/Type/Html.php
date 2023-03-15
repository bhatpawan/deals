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
use MageMe\WebForms\Api\Ui\FieldResultFormInterface;
use MageMe\WebForms\Helper\HtmlHelper;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Ui\Component\Form;

class Html extends AbstractField implements FieldResultFormInterface
{
    const HTML = Type\Html::HTML;

    protected $htmlHelper;

    public function __construct(
        HtmlHelper $htmlHelper
    )
    {
        $this->htmlHelper = $htmlHelper;
    }

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::HTML => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Wysiwyg::NAME,
                                    'dataScope' => static::HTML,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('HTML Content'),
                                    'template' => 'ui/form/field',
                                    'wysiwyg' => true,
                                    'rows' => 14,
                                    'wysiwygConfigData' => [
                                        'add_variables' => true,
                                        'add_widgets' => true,
                                        'add_images' => true,
                                        'add_directives' => true,
                                        'height' => '20em',
                                        'is_pagebuilder_enabled' => false
                                    ],
                                    'validation' => [
                                        'required-entry' => true,
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
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config                       = $this->getDefaultResultAdminFormConfig();
        $config['type']               = 'label';
        $config['label']              = false;
        $config['after_element_html'] = $this->getField()->getHtml();
        return $config;
    }
}