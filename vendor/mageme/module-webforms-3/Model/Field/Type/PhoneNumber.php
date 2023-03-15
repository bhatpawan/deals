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
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

class PhoneNumber extends Text
{
    const PREFERRED_COUNTRIES = 'preferred_countries';
    const ONLY_COUNTRIES = 'only_countries';
    const INITIAL_COUNTRY = 'initial_country';

    /**
     * @var CollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @param CollectionFactory $countryCollectionFactory
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(CollectionFactory   $countryCollectionFactory,
                                Context             $context,
                                FieldUiInterface    $fieldUi,
                                FieldBlockInterface $fieldBlock
    ) {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    #region type attributes

    /**
     * @return array
     */
    public function getPreferredCountries(): array
    {
        return is_array($this->getData(self::PREFERRED_COUNTRIES)) ? $this->getData(self::PREFERRED_COUNTRIES) : [];
    }

    /**
     * @param array $preferredCountries
     * @return PhoneNumber
     */
    public function setPreferredCountries(array $preferredCountries): PhoneNumber
    {
        return $this->setData(self::PREFERRED_COUNTRIES, $preferredCountries);
    }

    /**
     * @return array
     */
    public function getOnlyCountries(): array
    {
        return is_array($this->getData(self::ONLY_COUNTRIES)) ? $this->getData(self::ONLY_COUNTRIES) : [];
    }

    /**
     * @param array $onlyCountries
     * @return PhoneNumber
     */
    public function setOnlyCountries(array $onlyCountries): PhoneNumber
    {
        return $this->setData(self::ONLY_COUNTRIES, $onlyCountries);
    }

    /**
     * @return string
     */
    public function getInitialCountry(): string
    {
        return (string)$this->getData(self::INITIAL_COUNTRY);
    }

    /**
     * @param string $initialCountry
     * @return PhoneNumber
     */
    public function setInitialCountry(string $initialCountry): PhoneNumber
    {
        return $this->setData(self::INITIAL_COUNTRY, $initialCountry);
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation                            = parent::getValidation();
        $validation['rules']['validate-intl-phone-number'] = "'validate-intl-phone-number':true";
        return $validation;
    }

    /**
     * @inheritDoc
     */
    public function getPostErrors(array $postData, bool $logicVisibility, array $config = []): array
    {
        $errors = parent::getPostErrors($postData, $logicVisibility);
        if (!$this->validatePhoneNumber($postData)) {
            $errors[] = __('Invalid number.');
        }
        return $errors;
    }

    /**
     * @param $postData
     * @return bool
     */
    protected function validatePhoneNumber($postData): bool
    {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $value = trim((string)$fields[$this->getId()]);
        return (bool)preg_match('/^\(?\+?[\d\-\s()]*\d$/', $value);
    }

    /**
     * @return array
     */
    public function getCountriesAsOptions()
    {
        return $this->countryCollectionFactory->create()->loadByStore(
            $this->getStoreId())->toOptionArray(__('Auto'));
    }
}