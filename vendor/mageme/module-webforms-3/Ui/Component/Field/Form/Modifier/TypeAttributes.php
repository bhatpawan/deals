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

namespace MageMe\WebForms\Ui\Component\Field\Form\Modifier;


use MageMe\WebForms\Config\Config;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Ui\Component\Field\Form\DataProvider;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\ArrayManager;

class TypeAttributes extends AbstractModifier
{
    /**
     * Meta path to type select field
     */
    const TYPE_PATH = 'information/children/type';
    const TARGET_NAME = 'webforms_field_form.webforms_field_form';

    /**
     * @var Config
     */
    protected $config;
    /**
     * @var ArrayManager
     */
    protected $arrayManager;
    /**
     * @var FieldFactory
     */
    protected $fieldFactory;

    /**
     * TypeAttributes constructor.
     * @param FieldFactory $fieldFactory
     * @param Config $config
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        FieldFactory $fieldFactory,
        Config       $config,
        ArrayManager $arrayManager
    )
    {
        $this->arrayManager = $arrayManager;
        $this->config       = $config;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function modifyMeta(array $meta): array
    {
        $rules = [];

        // Add types field to meta and get rules
        foreach ($this->getUiMetaFromTypes() as $key => $type) {
            foreach (DataProvider::FIELDSETS as $fieldset) {
                if (isset($type[$fieldset])) {
                    $meta = $this->arrayManager->merge(
                        $fieldset,
                        $meta,
                        $type[$fieldset]
                    );
                    if ($fieldset === DataProvider::INFORMATION_FIELDSET) {
                        $rules = $this->getTypeRules($key, $type, $rules);
                    }
                }
            }
        }

        // Add rules to type select field
        if ($rules) {
            $meta = $this->arrayManager->merge(
                self::TYPE_PATH,
                $meta,
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'switcherConfig' => [
                                    'component' => 'MageMe_WebForms/js/form/type-switcher',
                                    'enabled' => true,
                                    'rules' => $rules,
                                ],
                            ]
                        ]
                    ]
                ]
            );
        }

        return $meta;
    }

    /**
     * Get meta from each type in config
     *
     * @return array
     * @throws LocalizedException
     */
    public function getUiMetaFromTypes(): array
    {
        $types  = $this->config->getFieldTypes();
        $fields = [];
        foreach ($types as $key => $type) {
            $model = $type['model'];
            if (class_exists($model)) {
                $fields[$key] = $this->fieldFactory->create($key)->getFieldUi()->getUiMeta($key);
            }
        }
        return $fields;
    }

    /**
     * Get field display rules
     *
     * @param string $type
     * @param array $meta
     * @param array $rules
     * @return array
     */
    public function getTypeRules(string $type, array $meta, array $rules = []): array
    {
        if (isset($meta[DataProvider::INFORMATION_FIELDSET])) {
            $actions_show = [];
            $actions_hide = [];
            foreach ($meta[DataProvider::INFORMATION_FIELDSET]['children'] as $key => $data) {
                $actions_show[] = [
                    'target' => self::TARGET_NAME . '.information.' . $key,
                    'callback' => 'show'
                ];
                $actions_hide[] = [
                    'target' => self::TARGET_NAME . '.information.' . $key,
                    'callback' => 'hide'
                ];
            }
            $rules[] = [
                'value' => $type,
                'actions' => $actions_show
            ];
            $rules[] = [
                'value' => '!' . $type,
                'actions' => $actions_hide
            ];
        }
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data): array
    {
        return $data;
    }
}

