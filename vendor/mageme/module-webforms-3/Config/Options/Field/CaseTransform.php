<?php

namespace MageMe\WebForms\Config\Options\Field;

use Magento\Framework\Data\OptionSourceInterface;

class CaseTransform implements OptionSourceInterface
{
    const NO = 'no';
    const UPPER = 'upper';
    const LOWER = 'lower';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('No'),
                'value' => self::NO,
            ],
            [
                'label' => __('Uppercase'),
                'value' => self::UPPER,
            ],
            [
                'label' => __('Lowercase'),
                'value' => self::LOWER,
            ],
        ];
    }
}