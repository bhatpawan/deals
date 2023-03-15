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
use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\BodyTmpl;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Component;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Filter;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Ui\Component\Form;

class Select extends AbstractField implements FieldResultListingColumnInterface, FieldResultFormInterface
{
    const OPTIONS = Type\AbstractOption::OPTIONS;
    const IS_MULTISELECT = Type\Select::IS_MULTISELECT;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ){
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::OPTIONS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Textarea::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::OPTIONS,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Options'),
                                    'additionalInfo' => __('Select values should be separated with new line<br>Use <i>^Option Text</i> to check default<br>Use <i>Option Text {{null}}</i> to create option without value</i><br>Use <i>Option Text {{val VALUE}}</i> to set different option value<br>Use <i>Option Text {{disabled}}</i> to create disabled option<br>Use <i>{{optgroup label="Option group"}}...{{/optgroup}}</i> to create group of options<br>Example:<br><i>{{optgroup label="Fruits"}}<br>Apple<br>Banana<br>Mango<br>{{/optgroup}}</i>'),
                                    'rows' => 5,
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
                                    'sortOrder' => 66,
                                    'label' => __('Multiple Selection'),
                                    'additionalInfo' => __('Select multiple values'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
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
        $config             = $this->getDefaultUIResultColumnConfig($sortOrder);
        if(!$this->scopeConfig->getValue('webforms/general/show_select_db_value') && !$this->getField()->getIsMultiselect()) {
            $config['filter']    = Filter::SELECT;
            $config['options']   = $this->getField()->toOptionArray();
            $config['bodyTmpl']  = BodyTmpl::HTML;
            $config['component'] = Component::SELECT;
        }
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config           = $this->getDefaultResultAdminFormConfig();
        $config['type']   = $this->getField()->getIsMultiselect() ? 'multiselect' : 'select';
        $config['values'] = $this->getField()->toOptionArray();
        return $config;
    }
}
