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

namespace MageMe\WebForms\Config\Options;


use MageMe\WebForms\CustomerData\CustomerData;
use Magento\Framework\Data\OptionSourceInterface;

class CustomerDataOptions implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Customer'),
                'value' => [
                    [
                        'label' => __('Full name'),
                        'value' => CustomerData::CUSTOMER_NAME,
                    ],
                    [
                        'label' => __('First name'),
                        'value' => CustomerData::CUSTOMER_FIRSTNAME,
                    ],
                    [
                        'label' => __('Last name'),
                        'value' => CustomerData::CUSTOMER_LASTNAME,
                    ],
                    [
                        'label' => __('Email'),
                        'value' => CustomerData::CUSTOMER_EMAIL,
                    ],
                ]
            ],
            [
                'label' => __('Billing Address'),
                'value' => [
                    [
                        'label' => __('First name'),
                        'value' => CustomerData::BILLING_FIRSTNAME,
                    ],
                    [
                        'label' => __('Last name'),
                        'value' => CustomerData::BILLING_LASTNAME,
                    ],
                    [
                        'label' => __('Company'),
                        'value' => CustomerData::BILLING_COMPANY,
                    ],
                    [
                        'label' => __('City'),
                        'value' => CustomerData::BILLING_CITY,
                    ],
                    [
                        'label' => __('Street'),
                        'value' => CustomerData::BILLING_STREET,
                    ],
                    [
                        'label' => __('Country 2 symbol code'),
                        'value' => CustomerData::BILLING_COUNTRY_ID,
                    ],
                    [
                        'label' => __('Region'),
                        'value' => CustomerData::BILLING_REGION,
                    ],
                    [
                        'label' => __('Postcode'),
                        'value' => CustomerData::BILLING_POSTCODE,
                    ],
                    [
                        'label' => __('Telephone'),
                        'value' => CustomerData::BILLING_TELEPHONE,
                    ],
                    [
                        'label' => __('Fax'),
                        'value' => CustomerData::BILLING_FAX,
                    ],
                ]
            ],

        ];
    }
}