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
use MageMe\WebForms\Api\Utility\Field\SubscriptionInterface;
use MageMe\WebForms\Model\Field\Context;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Newsletter\Model\SubscriberFactory;

class Subscribe extends AbstractOption implements OptionSourceInterface, SubscriptionInterface
{

    const TYPE_NAME = 'subscribe';

    /**
     * Attributes
     */
    const TEXT = 'text';

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * Subscribe constructor.
     * @param SubscriberFactory $subscriberFactory
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        SubscriberFactory   $subscriberFactory,
        Context             $context,
        FieldUiInterface    $fieldUi,
        FieldBlockInterface $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Always return true cause this type has no label
     *
     * @return bool
     */
    public function getIsLabelHidden(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->getText();
    }

    /**
     * Get field text
     *
     * @return string
     */
    public function getText(): string
    {
        return (string)$this->getData(self::TEXT);
    }

    /**
     * Set field text
     *
     * @param string $text
     * @return $this
     */
    public function setText(string $text): Subscribe
    {
        return $this->setData(self::TEXT, $text);
    }

    #region type attributes

    /**
     * @inheritDoc
     */
    public function getTypeCssForContainer(): string
    {
        return parent::getTypeCssForContainer() . ' choice';
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getOptionsArray($value = 'options'): array
    {
        return [
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 0, 'label' => __('No')]
        ];
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return $this->getOptionsArray();
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        return $value ? __('Yes') : __('No');
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAdminGrid($value, array $options = [])
    {
        return htmlentities((string)$value);
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function processNewResult(ResultInterface $result): FieldInterface
    {
        foreach ($result->getData() as $key => $value) {
            if ($key == 'field_' . $this->getId() && $value) {

                // subscribe to newsletter
                $customer_email = $result->getCustomerEmail();
                foreach ($customer_email as $email) {

                    // deprecated but not exists < 2.4
                    $this->subscriberFactory->create()->subscribe($email);
                }
            }
        }
        return $this;
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

}
