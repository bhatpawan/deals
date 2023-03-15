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


use Exception;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Config\Options\CustomerDataOptions;
use MageMe\WebForms\Config\Options\Field\CaseTransform;
use MageMe\WebForms\Model\Field\Type;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Ui\Component\Form;

class Password extends Text
{
    const IS_ENCRYPT = Type\Password::IS_ENCRYPT;
    const IS_COMPLEXITY_ENABLED = Type\Password::IS_COMPLEXITY_ENABLED;
    const MIN_PASSWORD_LENGTH = Type\Password::MIN_PASSWORD_LENGTH;
    const COMPLEXITY_SYMBOLS_COUNT = Type\Password::COMPLEXITY_SYMBOLS_COUNT;
    const MATCH_VALUE_FIELD_ID = Type\Password::MATCH_VALUE_FIELD_ID;

    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param Registry $registry
     * @param CaseTransform $caseTransform
     * @param CustomerDataOptions $customerDataOptions
     */
    public function __construct(
        RequestInterface    $request,
        Registry            $registry,
        CaseTransform $caseTransform,
        CustomerDataOptions $customerDataOptions)
    {
        parent::__construct($caseTransform, $customerDataOptions);
        $this->registry = $registry;
        $this->request  = $request;
    }

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::PLACEHOLDER => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::PLACEHOLDER,
                                    'visible' => 0,
                                    'sortOrder' => 45,
                                    'label' => __('Placeholder'),
                                    'additionalInfo' => __('Placeholder text will appear in the input and disappear on the focus'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_ENCRYPT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_ENCRYPT,
                                    'visible' => 0,
                                    'sortOrder' => 55,
                                    'label' => __('Encrypt Password'),
                                    'additionalInfo' => __('It is recommended to store passwords encrypted for better security'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_COMPLEXITY_ENABLED => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_COMPLEXITY_ENABLED,
                                    'visible' => 0,
                                    'sortOrder' => 56,
                                    'label' => __('Password Complexity Check'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MIN_PASSWORD_LENGTH => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::MIN_PASSWORD_LENGTH,
                                    'visible' => 0,
                                    'sortOrder' => 57,
                                    'label' => __('Minimum Password Length'),
                                    'additionalInfo' => __('Please enter a number 1 or greater in this field.'),
                                    'default' => 8,
                                    'validation' => [
                                        'required-entry' => true,
                                        'validate-number' => true,
                                        'validate-greater-than-zero' => true
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::COMPLEXITY_SYMBOLS_COUNT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::COMPLEXITY_SYMBOLS_COUNT,
                                    'visible' => 0,
                                    'sortOrder' => 58,
                                    'default' => 3,
                                    'label' => __('Number Of Required Character Classes'),
                                    'additionalInfo' => __('Number of different character classes required in password: Lowercase, Uppercase, Digits, Special Characters.'),
                                    'validation' => [
                                        'validate-number' => true,
                                        'validate-number-range' => '0-4'
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MATCH_VALUE_FIELD_ID => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::MATCH_VALUE_FIELD_ID,
                                    'visible' => 0,
                                    'sortOrder' => 59,
                                    'label' => __('Match Value With Another Field'),
                                    'options' => $this->getMatchFieldsAsOptions(),
                                    'caption' => __('-- Please Select --'),
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getMatchFieldsAsOptions(): array
    {
        $options = [];
        $id = $this->request->getParam(FieldInterface::ID);
        try {
            /** @var FormInterface $form */
            $form = $this->registry->registry('webforms_form');
            $options = $form->getFieldsAsOptions(Type\Password::class, ['ne_field_ids' => [$id]]);
        } catch (Exception $exception) {
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config = parent::getResultAdminFormConfig($result);
        $config['disabled'] = true;
        $config['type']  = \MageMe\WebForms\Block\Adminhtml\Result\Element\Password::TYPE;
        return $config;
    }
}
