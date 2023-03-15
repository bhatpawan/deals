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

namespace MageMe\WebForms\Model\Field\Type;


use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Model\Field\Context;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Locale\TranslatedLists;

class Country extends Select implements OptionSourceInterface
{

    /**
     * Attributes
     */
    const DEFAULT_COUNTRY = 'default_country';

    /**
     * @var CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var TranslatedLists
     */
    protected $translatedLists;

    /**
     * Country constructor.
     * @param TranslatedLists $translatedLists
     * @param CollectionFactory $countryCollectionFactory
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        TranslatedLists     $translatedLists,
        CollectionFactory   $countryCollectionFactory,
        Context             $context,
        FieldUiInterface    $fieldUi,
        FieldBlockInterface $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);

        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->translatedLists          = $translatedLists;
    }

    /**
     * @inheritDoc
     */
    public function getFilteredFieldValue()
    {
        $country = $this->getDefaultCountry() ?: '';
        $customer = $this->getCustomer();
        if ($customer) {
            $address = $customer->getDefaultBillingAddress();
            if ($address) {
                $country = $address->getCountryId();
            }
        }
        return $this->getCustomerValue() ?: $country;
    }

    /**
     * Get field text
     *
     * @return string
     */
    public function getDefaultCountry(): string
    {
        return (string)$this->getData(self::DEFAULT_COUNTRY);
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray($emptyLabel = '-- Please Select --'): array
    {
        /** @var Collection */
        return $this->countryCollectionFactory->create()->loadByStore(
            $this->getStoreId())->toOptionArray($emptyLabel);
    }

    #region type attributes

    /**
     * @inheritDoc
     */
    public function getOptionsArray($value = 'options'): array
    {
        /** @var Collection */
        return $this->countryCollectionFactory->create()->loadByStore(
            $this->getStoreId())->toOptionArray(__('-- Please Select --')
        );
    }

    /**
     * Set field text
     *
     * @param string $defaultCountry
     * @return $this
     */
    public function setDefaultCountry(string $defaultCountry): Country
    {
        return $this->setData(self::DEFAULT_COUNTRY, $defaultCountry);
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        $country_name = $this->translatedLists->getCountryTranslation($value);
        return empty($country_name) ? htmlentities((string)$value) : $country_name;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        return $this->getValueForResultHtml($value);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAdminGrid($value, array $options = [])
    {
        return htmlentities((string)$value);
    }

    /**
     * @param string $value
     * @param array $options
     * @return string
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string
    {
        return $this->getValueForResultHtml($value, $options);
    }

    /**
     * @inheritDoc
     */
    public function getValueForSubject($value)
    {
        $value = $this->getValueForResultHtml($value);
        return parent::getValueForSubject($value);
    }
}
