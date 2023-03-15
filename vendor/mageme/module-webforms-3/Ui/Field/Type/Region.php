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
use MageMe\WebForms\Model\Field\Type\Country;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\BodyTmpl;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Registry;
use Magento\Ui\Component\Form;

class Region extends AbstractField implements FieldResultListingColumnInterface, FieldResultFormInterface
{
    const COUNTRY_FIELD_ID = Type\Region::COUNTRY_FIELD_ID;

    protected $regCollectionFactory;

    protected $registry;

    public function __construct(
        Registry                $registry,
        RegionCollectionFactory $regCollectionFactory
    )
    {
        $this->registry             = $registry;
        $this->regCollectionFactory = $regCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        /** @var \MageMe\WebForms\Model\Form $form */
        $form           = $this->registry->registry('webforms_form');
        $options        = $form->getFieldsAsOptions(Country::class);
        $additionalInfo = __('Connect to country field to populate the list with region options.');
        if (!$options) {
            $additionalInfo = '<div style="color:red; font-weight:bold">' . __('Please create any country field on this form') . '</div>' . $additionalInfo;
        }
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::COUNTRY_FIELD_ID => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Select::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::COUNTRY_FIELD_ID,
                                    'visible' => 0,
                                    'sortOrder' => 65,
                                    'label' => __('Country Field'),
                                    'additionalInfo' => $additionalInfo,
                                    'options' => $options,
                                    'validation' => [
                                        'required-entry' => true,
                                    ]
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
        $config             = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['class']    = \MageMe\WebForms\Ui\Component\Result\Listing\Column\Field\Region::class;
        $config['bodyTmpl'] = BodyTmpl::HTML;
        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $value                      = json_decode($result->getData('field_' . $this->getField()->getId()), true);
        $config                     = $this->getDefaultResultAdminFormConfig();
        $config['type']             = 'region';
        $config['country_field_id'] = 'field_' . $this->getField()->getCountryFieldId();
        $config['region']           = !empty($value['region']) ? $value['region'] : '';
        $config['region_id']        = !empty($value['region_id']) ? $value['region_id'] : '';
        return $config;
    }
}