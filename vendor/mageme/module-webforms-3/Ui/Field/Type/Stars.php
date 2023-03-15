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
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Filter;
use MageMe\WebForms\Ui\Component\Result\Listing\Column\Field;
use Magento\Ui\Component\Form;

class Stars extends Select
{
    const INIT_STARS = Type\Stars::INIT_STARS;
    const MAX_STARS = Type\Stars::MAX_STARS;

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::INIT_STARS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::INIT_STARS,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Number Of Stars Selected By Default'),
                                    'additionalInfo' => __('3 stars are selected by default'),
                                    'validation' => [
                                        'validate-digits' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::MAX_STARS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::MAX_STARS,
                                    'visible' => 0,
                                    'sortOrder' => 66,
                                    'label' => __('Total Amount Of Stars'),
                                    'additionalInfo' => __('5 stars are available by default'),
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
        $config             = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['filter']   = Filter::TEXT_RANGE;
        $config['bodyTmpl'] = BodyTmpl::HTML;
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config            = parent::getResultAdminFormConfig($result);
        $config['type']    = 'select';
        $config['options'] = $this->getField()->getStarsOptions();
        return $config;
    }
}
