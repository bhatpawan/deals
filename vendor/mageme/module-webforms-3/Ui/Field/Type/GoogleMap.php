<?php

namespace MageMe\WebForms\Ui\Field\Type;

use MageMe\WebForms\Api\Ui\FieldResultFormInterface;
use MageMe\WebForms\Api\Ui\FieldResultListingColumnInterface;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Ui\Component\Form;
use MageMe\WebForms\Model\Field\Type;

class GoogleMap extends AbstractField implements FieldResultListingColumnInterface, FieldResultFormInterface
{
    const ADDRESS = Type\GoogleMap::ADDRESS;
    const LAT = Type\GoogleMap::LAT;
    const LNG = Type\GoogleMap::LNG;
    const ZOOM = Type\GoogleMap::ZOOM;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::ADDRESS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::ADDRESS,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Default Address'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::LAT => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::LAT,
                                    'visible' => 0,
                                    'sortOrder' => 66,
                                    'label' => __('Default Latitude'),
                                    'validation' => [
                                        'validate-number' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::LNG => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::LNG,
                                    'visible' => 0,
                                    'sortOrder' => 67,
                                    'label' => __('Default Longitude'),
                                    'validation' => [
                                        'validate-number' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::ZOOM => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::ZOOM,
                                    'visible' => 0,
                                    'sortOrder' => 68,
                                    'label' => __('Default Zoom'),
                                    'validation' => [
                                        'validate-number' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }

}