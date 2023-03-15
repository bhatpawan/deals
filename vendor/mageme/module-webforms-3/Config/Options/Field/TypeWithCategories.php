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

namespace MageMe\WebForms\Config\Options\Field;


use MageMe\WebForms\Config\Config;
use Magento\Framework\Data\OptionSourceInterface;

class TypeWithCategories implements OptionSourceInterface
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * Type constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    )
    {
        $this->_config = $config;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options    = [];
        $types      = $this->_config->getFieldTypes();
        $categories = $this->getCategories($types);
        foreach ($categories as $category) {
            $value = [];
            foreach ($this->_config->getFieldTypes() as $key => $data) {
                if ($data['category'] == $category) {
                    $value[] = [
                        'label' => $data['label'],
                        'value' => $key,
                    ];
                }
            }
            if ($value) {
                $options[] = [
                    'label' => $category,
                    'value' => $value,
                ];
            }
        }
        return $options;
    }

    /**
     * Get categories from types
     *
     * @param array $types
     * @return array
     */
    protected function getCategories(array $types): array
    {
        $categories = [];
        foreach ($types as $type) {
            if (!in_array($type['category'], $categories)) {
                $categories[] = $type['category'];
            }
        }
        return $categories;
    }

}
