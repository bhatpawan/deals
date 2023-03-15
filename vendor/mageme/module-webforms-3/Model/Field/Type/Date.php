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


use DateInterval;
use DateTimeImmutable;
use Exception;
use IntlDateFormatter;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Model\Field\AbstractField;
use MageMe\WebForms\Model\Field\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Zend_Date;
use Zend_Date_Exception;
use Zend_Locale_Format;

class Date extends AbstractField
{
    /**
     * Attributes
     */
    const PLACEHOLDER = 'placeholder';
    const IS_PAST_DISABLED = 'is_past_disabled';
    const IS_FUTURE_DISABLED = 'is_future_disabled';
    const IS_TODAY_DISABLED = 'is_today_disabled';
    const PAST_OFFSET = 'past_offset';
    const FUTURE_OFFSET = 'future_offset';
    const DISABLED_WEEK_DAYS = 'disabled_week_days';
    const DISABLED_CUSTOM_DATES = 'disabled_custom_dates';
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param ResolverInterface $localeResolver
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(ResolverInterface $localeResolver, Context $context, FieldUiInterface $fieldUi, FieldBlockInterface $fieldBlock)
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->localeResolver = $localeResolver;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @inheritDoc
     */
    public function getFilteredFieldValue()
    {
        $customer_value = $this->getCustomerValue();
        if ($customer_value) {
            if (is_array($customer_value) && isset($customer_value[0])) {
                return $customer_value[0];
            }
            return $customer_value;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isAccessible(): bool
    {
        return (bool)$this->scopeConfig->getValue('webforms/accessibility/accessible_calendar',
            ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get field placeholder
     *
     * @return string
     */
    public function getPlaceholder(): string
    {
        return (string)$this->getData(self::PLACEHOLDER);
    }

    /**
     * Set field placeholder
     *
     * @param string $placeholder
     * @return $this
     */
    public function setPlaceholder(string $placeholder): Date
    {
        return $this->setData(self::PLACEHOLDER, $placeholder);
    }

    /**
     * @param bool $isPastDisabled
     * @return $this
     */
    public function setIsPastDisabled(bool $isPastDisabled): Date
    {
        return $this->setData(self::IS_PAST_DISABLED, $isPastDisabled);
    }

    #region type attributes

    /**
     * @param bool $isFutureDisabled
     * @return $this
     */
    public function setIsFutureDisabled(bool $isFutureDisabled): Date
    {
        return $this->setData(self::IS_FUTURE_DISABLED, $isFutureDisabled);
    }

    /**
     * @param bool $isTodayDisabled
     * @return $this
     */
    public function setIsTodayDisabled(bool $isTodayDisabled): Date
    {
        return $this->setData(self::IS_TODAY_DISABLED, $isTodayDisabled);
    }

    /**
     * @param int|null $pastOffset
     * @return $this
     */
    public function setPastOffset(?int $pastOffset): Date
    {
        return $this->setData(self::PAST_OFFSET, $pastOffset);
    }

    /**
     * @param int|null $futureOffset
     * @return $this
     */
    public function setFutureOffset(?int $futureOffset): Date
    {
        return $this->setData(self::FUTURE_OFFSET, $futureOffset);
    }

    /**
     * @param array $disabledWeekDays
     * @return $this
     */
    public function setDisabledWeekDays(array $disabledWeekDays): Date
    {
        return $this->setData(self::DISABLED_WEEK_DAYS, $disabledWeekDays);
    }

    /**
     * @param string|null $disabledCustomDates
     * @return $this
     */
    public function setDisabledCustomDates(?string $disabledCustomDates): Date
    {
        return $this->setData(self::DISABLED_CUSTOM_DATES, $disabledCustomDates);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAfterSave($value, ResultInterface $result)
    {
        $value = parent::getValueForResultAfterSave($value, $result);
        return $this->getFormattedValue($value);
    }

    /**
     * @param $value
     * @return string
     */
    private function getFormattedValue($value): string
    {
        if (date($this->getDbFormat(), strtotime($value)) == $value) {
            return $value;
        }
        if (strlen((string)$value) > 0) {
            $date = DateTimeImmutable::createFromFormat($this->getDateFormatPhp(), $value);
            if($date) $value = $date->format($this->getDbFormat());
        }
        return $value;
    }

    /**
     * @return string
     */
    public function getDbFormat(): string
    {
        return $this->dateHelper::DB_DATE_FORMAT;
    }

    /**
     * @return string
     */
    public function getGridFormat(): string
    {
        return $this->dateHelper->getDateFormat(IntlDateFormatter::MEDIUM);
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->getDateFormat();
    }

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        $format = $this->dateHelper->getDateFormat();
        $arr    = [
            'yyyy' => 'Y',
            'yy' => 'y',
            'y' => 'Y',
            'MM' => 'M',
            'mm' => 'm',
            'dd' => 'd',
            'DD' => 'd'
        ];
        foreach ($arr as $search => $replace) {
            $format = preg_replace('/(^|[^%])' . $search . '/', '$1' . $replace, $format);
        }
        return $format;
    }

    /**
     * @return string
     */
    public function getDateFormatPhp(): string
    {
        $format = $this->dateHelper->getDateFormat();
        $arr    = [
            'yyyy' => 'Y',
            'yy' => 'y',
            'y' => 'Y',
            'MM' => 'm',
            'mm' => 'm',
            'M' => 'm',
            'dd' => 'd',
            'DD' => 'd'
        ];
        foreach ($arr as $search => $replace) {
            $format = preg_replace('/(^|[^%])' . $search . '/', '$1' . $replace, $format);
        }
        return $format;
    }


    /**
     * @inheritDoc
     */
    public function getValueForResultCollectionFilter($value)
    {
        if (!empty($value['from'])) {
            $value['from'] = "'" . date($this->getDbFormat(), strtotime($value['orig_from'])) . "'";
        }
        if (!empty($value['to'])) {
            $value['to'] = "'" . date($this->getDbFormat(), strtotime($value['orig_to'])) . "'";
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultValueRenderer(DataObject $row): string
    {
        $fieldIndex = 'field_' . $this->getId();
        $value      = $row->getData($fieldIndex);
        return $this->getValueForResultTemplate($value);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = [])
    {
        if (!is_string($value)) {
            return '';
        }
        $date = DateTimeImmutable::createFromFormat($this->getDbFormat(), $value);
        if (!$date) {
            return $value;
        }
        if (!empty($options['date_format'])) {
            return $date->format($options['date_format']);
        }
        $formatter = new IntlDateFormatter(
            $this->getLocale(),
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE
        );
        return $formatter->format($date);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string
    {
        return $this->getValueForResultTemplate($value);
    }

    /**
     * @inheritDoc
     */
    public function getValueForSubject($value)
    {
        $value = $this->getValueForResultHtml($value);
        return parent::getValueForSubject($value);
    }

    /**
     * @inheritDoc
     */
    public function getValueForResultAdminGrid($value, array $options = [])
    {
        if (!is_string($value)) {
            return '';
        }
        $date = DateTimeImmutable::createFromFormat($this->getDbFormat(), $value);
        return $date ? $date->format('Y-m-d') : '';
    }
    #endregion

    /**
     * @inheritDoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        return $this->getValueForResultTemplate($value);
    }

    public function getPostErrors(array $postData, bool $logicVisibility, array $config = []): array
    {
        $errors = parent::getPostErrors($postData, $logicVisibility, $config);

        $now = new DateTimeImmutable('today');

        try {
            // check past offset
            if ($this->getIsPastDisabled()) {
                if (!$this->validatePostPastOffset($postData, $now)) {
                    $errors[] = __('%1: The specified date is lower than the min date.', __($this->getName()));
                }
            }

            // check future offset
            if ($this->getIsFutureDisabled()) {
                if (!$this->validatePostFutureOffset($postData, $now)) {
                    $errors[] = __('%1: The specified date is greater than the max date.', __($this->getName()));
                }
            }

            // check today
            if ($this->getIsTodayDisabled()) {
                if (!$this->validatePostToday($postData, $now)) {
                    $errors[] = __('%1: Today is disabled.', __($this->getName()));
                }
            }

            // check weekdays
            if ($this->getDisabledWeekDays()) {
                if (!$this->validatePostWeekdays($postData)) {
                    $errors[] = __('%1: The specified weekday is disabled.', __($this->getName()));
                }
            }

            // check weekdays
            if ($this->getDisabledCustomDates()) {
                if (!$this->validatePostCustomRules($postData)) {
                    $errors[] = __('%1: The specified date is disabled by rules.', __($this->getName()));
                }
            }
        } catch (Exception $e) {
            $errors[] = __('Incorrect date: ' . $e->getMessage());
        }

        return $errors;
    }

    /**
     * @return bool
     */
    public function getIsPastDisabled(): bool
    {
        return (bool)$this->getData(self::IS_PAST_DISABLED);
    }

    /**
     * @param array $postData
     * @param DateTimeImmutable $now
     * @return bool
     * @throws Exception
     */
    protected function validatePostPastOffset(array $postData, DateTimeImmutable $now): bool
    {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $value   = $this->getFormattedValue($fields[$this->getId()]);
        $offset  = $this->getPastOffset();
        $minDate = $offset > 0 ?
            $now->add(new DateInterval('P' . $offset . 'D')) :
            $now->sub(new DateInterval('P' . abs($offset) . 'D'));
        $date    = DateTimeImmutable::createFromFormat($this->getDbFormat(), $value)->setTime(0, 0, 0);
        return $date >= $minDate;
    }

    /**
     * @return int
     */
    public function getPastOffset(): int
    {
        return (int)$this->getData(self::PAST_OFFSET);
    }

    /**
     * @return bool
     */
    public function getIsFutureDisabled(): bool
    {
        return (bool)$this->getData(self::IS_FUTURE_DISABLED);
    }

    /**
     * @param array $postData
     * @param DateTimeImmutable $now
     * @return bool
     * @throws Exception
     */
    protected function validatePostFutureOffset(array $postData, DateTimeImmutable $now): bool
    {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $value   = $this->getFormattedValue($fields[$this->getId()]);
        $offset  = $this->getFutureOffset();
        $maxDate = $offset > 0 ?
            $now->add(new DateInterval('P' . $offset . 'D')) :
            $now->sub(new DateInterval('P' . abs($offset) . 'D'));
        $date    = DateTimeImmutable::createFromFormat($this->getDbFormat(), $value)->setTime(0, 0, 0);
        return $date <= $maxDate;
    }

    /**
     * @return int
     */
    public function getFutureOffset(): int
    {
        return (int)$this->getData(self::FUTURE_OFFSET);
    }

    /**
     * @return bool
     */
    public function getIsTodayDisabled(): bool
    {
        return (bool)$this->getData(self::IS_TODAY_DISABLED);
    }

    /**
     * @param array $postData
     * @param DateTimeImmutable $now
     * @return bool
     */
    protected function validatePostToday(array $postData, DateTimeImmutable $now): bool
    {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $value = $this->getFormattedValue($fields[$this->getId()]);
        $date  = DateTimeImmutable::createFromFormat($this->getDbFormat(), $value)->setTime(0, 0, 0);
        $date->format('N');
        return $now->diff($date)->days !== 0;
    }

    /**
     * @return array
     */
    public function getDisabledWeekDays(): array
    {
        return is_array($this->getData(self::DISABLED_WEEK_DAYS)) ? $this->getData(self::DISABLED_WEEK_DAYS) : [];
    }

    /**
     * @param array $postData
     * @return bool
     */
    protected function validatePostWeekdays(array $postData): bool
    {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $value   = $this->getFormattedValue($fields[$this->getId()]);
        $date    = DateTimeImmutable::createFromFormat($this->getDbFormat(), $value)->setTime(0, 0, 0);
        $weekday = $date->format('N');
        return !in_array($weekday, $this->getDisabledWeekDays());
    }

    /**
     * @param array $postData
     * @return bool
     */
    protected function validatePostCustomRules(array $postData): bool
    {
        $fields = $postData['field'];
        if (empty($fields[$this->getId()])) {
            return true;
        }
        $value = $this->getFormattedValue($fields[$this->getId()]);
        $date  = DateTimeImmutable::createFromFormat($this->getDbFormat(), $value)->setTime(0, 0, 0);
        $day   = (int)$date->format('d');
        $month = (int)$date->format('m') - 1;
        $year = (int)$date->format('Y');
        $date = $date->setDate($year, $month, $day);
        foreach ($this->getDisabledCustomRules() as $rule) {
            if (!$rule['range']) {
                if (isset($rule['year'])) {
                    if ($day == $rule['day'] &&
                        $month == $rule['month'] &&
                        $year == $rule['year']) {
                        return false;
                    }
                } elseif (isset($rule['month'])) {
                    if ($day == $rule['day'] &&
                        $month == $rule['month']) {
                        return false;
                    }
                } else {
                    if ($day == $rule['day']) {
                        return false;
                    }
                }
            } else {
                if (isset($rule['minYear'])) {
                    $minDate = $date->setDate($rule['minYear'], $rule['minMonth'], $rule['minDay']);
                    $maxDate = $date->setDate($rule['maxYear'], $rule['maxMonth'], $rule['maxDay']);
                    if ($date >= $minDate && $date <= $maxDate) {
                        return false;
                    }
                } elseif (isset($rule['minMonth'])) {
                    if ($rule['minMonth'] === $rule['maxMonth']) {
                        if ($month === $rule['minMonth'] && $day >= $rule['minDay'] && $day <= $rule['maxDay']) {
                            return false;
                        }
                    } elseif ($month === $rule['minMonth'] && $month <= $rule['maxMonth'] &&
                        $day >= $rule['minDay']  ||
                        $month > $rule['minMonth'] && $month < $rule['maxMonth'] ||
                        $month === $rule['maxMonth'] && $month >= $rule['minMonth'] &&
                        $day <= $rule['maxDay']) {
                        return false;
                    }
                } else {
                    if ($day >= $rule['minDay'] && $day <= $rule['maxDay']) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function getDisabledCustomRules(): array
    {
        $rules = [];
        $dates = preg_split('([^\d.-]+)', (string)$this->getDisabledCustomDates());
        foreach ($dates as $date) {
            if (preg_match('((\d+)[.\\\/]*(\d*)[.\\\/]*(\d*)-(\d+)[.\\\/]*(\d*)[.\\\/]*(\d*))', (string)$date, $matches)) {
                if (preg_match('((\d+).(\d+).(\d+)-(\d+).(\d+).(\d+))', (string)$date, $matches)) {
                    $rules[] = [
                        'range' => true,
                        'minDay' => (int)$matches[1],
                        'minMonth' => (int)$matches[2] - 1,
                        'minYear' => (int)$matches[3],
                        'maxDay' => (int)$matches[4],
                        'maxMonth' => (int)$matches[5] - 1,
                        'maxYear' => (int)$matches[6],
                    ];
                } elseif (preg_match('((\d+).(\d+)-(\d+).(\d+))', (string)$date, $matches)) {
                    $rules[] = [
                        'range' => true,
                        'minDay' => (int)$matches[1],
                        'minMonth' => (int)$matches[2] - 1,
                        'maxDay' => (int)$matches[3],
                        'maxMonth' => (int)$matches[4] - 1,
                    ];
                } elseif (preg_match('((\d+)-(\d+))', (string)$date, $matches)) {
                    $rules[] = [
                        'range' => true,
                        'minDay' => (int)$matches[1],
                        'maxDay' => (int)$matches[2],
                    ];
                }
            } else {
                if (preg_match('((\d+).(\d+).(\d+))', (string)$date, $matches)) {
                    $rules[] = [
                        'range' => false,
                        'day' => (int)$matches[1],
                        'month' => (int)$matches[2] - 1,
                        'year' => (int)$matches[3],
                    ];
                } elseif (preg_match('((\d+).(\d+))', (string)$date, $matches)) {
                    $rules[] = [
                        'range' => false,
                        'day' => (int)$matches[1],
                        'month' => (int)$matches[2] - 1,
                    ];
                } elseif (is_numeric($date) && (int)$date > 0 && (int)$date < 32) {
                    $rules[] = [
                        'range' => false,
                        'day' => (int)$date,
                    ];
                }
            }
        }
        return $rules;
    }

    /**
     * @return string|null
     */
    public function getDisabledCustomDates(): ?string
    {
        return $this->getData(self::DISABLED_CUSTOM_DATES);
    }
}
