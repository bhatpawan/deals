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

namespace MageMe\WebForms\CustomerData;


use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\CustomerRegistry;

class CustomerData implements SectionSourceInterface
{
    const CUSTOMER_NAME = 'customer_name';
    const CUSTOMER_EMAIL = 'customer_email';
    const CUSTOMER_FIRSTNAME = 'customer_firstname';
    const CUSTOMER_LASTNAME = 'customer_lastname';
    const BILLING_FIRSTNAME = 'billing_firstname';
    const BILLING_LASTNAME = 'billing_lastname';
    const BILLING_COMPANY = 'billing_company';
    const BILLING_CITY = 'billing_city';
    const BILLING_STREET = 'billing_street';
    const BILLING_COUNTRY_ID = 'billing_country_id';
    const BILLING_REGION = 'billing_region';
    const BILLING_POSTCODE = 'billing_postcode';
    const BILLING_TELEPHONE = 'billing_telephone';
    const BILLING_FAX = 'billing_fax';

    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * Data constructor.
     * @param CustomerRegistry $customerRegistry
     * @param CurrentCustomer $currentCustomer
     */
    public function __construct(
        CustomerRegistry $customerRegistry,
        CurrentCustomer  $currentCustomer
    )
    {
        $this->currentCustomer  = $currentCustomer;
        $this->customerRegistry = $customerRegistry;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData(): array
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return [];
        }

        $customer = $this->currentCustomer->getCustomer();
        $data     = [
            self::CUSTOMER_NAME => $customer->getFirstname().' '.$customer->getLastname(),
            self::CUSTOMER_EMAIL => $customer->getEmail(),
            self::CUSTOMER_FIRSTNAME => $customer->getFirstname(),
            self::CUSTOMER_LASTNAME => $customer->getLastname(),
        ];
        $billing  = $this->customerRegistry->retrieve($customer->getId())->getDefaultBillingAddress();
        if ($billing) {
            $data = array_merge($data, [
                self::BILLING_FIRSTNAME => $billing->getFirstname(),
                self::BILLING_LASTNAME => $billing->getLastname(),
                self::BILLING_COMPANY => $billing->getCompany(),
                self::BILLING_CITY => $billing->getCity(),
                self::BILLING_STREET => $billing->getStreet(),
                self::BILLING_COUNTRY_ID => $billing->getCountryId(),
                self::BILLING_REGION => $billing->getRegion(),
                self::BILLING_POSTCODE => $billing->getPostcode(),
                self::BILLING_TELEPHONE => $billing->getTelephone(),
                self::BILLING_FAX => $billing->getFax(),
            ]);
        }
        return $data;
    }

}