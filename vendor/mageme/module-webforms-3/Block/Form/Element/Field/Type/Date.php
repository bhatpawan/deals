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

namespace MageMe\WebForms\Block\Form\Element\Field\Type;


use MageMe\WebForms\Block\Form\Element\Field\AbstractField;
use MageMe\WebForms\Block\Form\Element\Field\Tooltip;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

class Date extends AbstractField
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'date.phtml';
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param TranslationHelper $translationHelper
     * @param Tooltip $tooltipBlock
     * @param ResolverInterface $localeResolver
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        TranslationHelper $translationHelper,
        Tooltip           $tooltipBlock,
        ResolverInterface $localeResolver,
        Registry          $registry, Context $context, array $data = [])
    {
        parent::__construct($translationHelper, $tooltipBlock, $registry, $context, $data);
        $this->localeResolver = $localeResolver;
    }

    /**
     * @inheritDoc
     */
    public function getFieldClass(): string
    {
        $class = 'input-text ' . parent::getFieldClass();
        return $this->getField()->isAccessible() ? $class . ' _has-datepicker' : $class;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string
    {
        return htmlspecialchars(trim((string)$this->field->getPlaceholder() ?? ''));
    }

    /**
     * @return bool
     */
    public function isAccessible(): bool
    {
        return $this->field->isAccessible();
    }

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->field->getDateFormat();
    }

    /**
     * @return bool
     */
    public function getIsPastDisabled(): bool
    {
        return (bool)$this->getField()->getIsPastDisabled();
    }

    /**
     * @return bool
     */
    public function getIsFutureDisabled(): bool
    {
        return (bool)$this->getField()->getIsFutureDisabled();
    }

    /**
     * @return bool
     */
    public function getIsTodayDisabled(): bool
    {
        return (bool)$this->getField()->getIsTodayDisabled();
    }

    /**
     * @return int
     */
    public function getPastOffset(): int
    {
        return (int)$this->getField()->getPastOffset();
    }

    /**
     * @return int
     */
    public function getFutureOffset(): int
    {
        return (int)$this->getField()->getFutureOffset();
    }

    /**
     * @return string
     */
    public function getDisabledWeekDays(): string
    {
        return json_encode(array_map('intval', $this->getField()->getDisabledWeekDays()));
    }

    /**
     * @return string
     */
    public function getCustomRules(): string
    {
        return json_encode($this->getField()->getDisabledCustomRules());
    }

    /**
     * @return bool
     */
    public function getIsWeekNumbersShowed(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->localeResolver->getLocale();
    }
}
