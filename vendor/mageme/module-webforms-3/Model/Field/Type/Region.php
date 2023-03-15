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


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\Field\Context;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class Region extends AbstractField
{
    /**
     * Attributes
     */
    const COUNTRY_FIELD_ID = 'country_field_id';

    /**
     * @var RegionCollectionFactory
     */
    protected $regCollectionFactory;

    /**
     * Region constructor.
     * @param RegionCollectionFactory $regCollectionFactory
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        RegionCollectionFactory $regCollectionFactory,
        Context                 $context,
        FieldUiInterface        $fieldUi,
        FieldBlockInterface     $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->regCollectionFactory = $regCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getFilteredFieldValue()
    {
        $value          = [
            'region' => '',
            'region_id' => ''
        ];
        $customer_value = $this->getCustomerValue();
        if ($customer_value) {
            $regionInfo = json_decode($customer_value, true);
            if (!empty($regionInfo['region']))
                $value['region'] = $regionInfo['region'];
            if (!empty($regionInfo['region_id']))
                $value['region_id'] = $regionInfo['region_id'];
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getValidation(): array
    {
        $validation = parent::getValidation();
        if ($this->getIsRequired()) {
            $validation['rules']['validate-select'] = "'validate-select':true";
            if ($this->getValidationRequiredMessage()) {
                $validation['descriptions']['data-msg-validate-select'] = $this->getValidationRequiredMessage();
            }
        }
        return $validation;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAfterSave($value, ResultInterface $result)
    {
        return is_array($value) ? json_encode([
            'region' => empty($value['region']) ? '' : $value['region'],
            'region_id' => empty($value['region_id']) ? '' : $value['region_id']
        ]) : $value;
    }

    /**
     * @return bool
     */
    public function isImportPostProcess(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     * @throws CouldNotSaveException
     */
    public function importPostProcess(array $logicMatrix): FieldInterface
    {
        $countryFieldId = isset($logicMatrix['field_' . $this->getCountryFieldId()]) ?
            $logicMatrix['field_' . $this->getCountryFieldId()] : null;
        $this->setCountryFieldId($countryFieldId);
        $this->fieldRepository->save($this);
        return $this;
    }

    /**
     * Get country field id
     *
     * @return int
     */
    public function getCountryFieldId(): int
    {
        return (int)$this->getData(self::COUNTRY_FIELD_ID);
    }

    /**
     * Set country field id
     *
     * @param int|null $countryFieldId
     * @return $this
     */
    public function setCountryFieldId(?int $countryFieldId): Region
    {
        return $this->setData(self::COUNTRY_FIELD_ID, $countryFieldId);
    }

    public function getValueForResultHtml($value, array $options = [])
    {
        $data = json_decode((string)$value, true);

        return $this->getRegionName($data);
    }

    public function getRegionName($data): string
    {
        if (!empty($data['region_id'])) {
            $region = $this->regCollectionFactory->create()->getItemById($data['region_id']);
            return $region->getName();
        }
        return $data['region'] ?? '';
    }

    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        $data = is_array($value) ? $value:json_decode((string)$value, true);

        return $this->getRegionName($data);
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

