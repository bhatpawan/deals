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
use MageMe\WebForms\Config\Options\DaysOfWeek;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Component;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\DataType;
use MageMe\WebForms\Ui\Component\Common\Listing\Constants\Filter;
use MageMe\WebForms\Ui\Field\AbstractField;
use Magento\Ui\Component\Form;
use Magento\Framework\Locale\Bundle\DataBundle;

class Date extends AbstractField implements FieldResultListingColumnInterface, FieldResultFormInterface
{
    const PLACEHOLDER = Type\Date::PLACEHOLDER;
    const IS_PAST_DISABLED = Type\Date::IS_PAST_DISABLED;
    const IS_FUTURE_DISABLED = Type\Date::IS_FUTURE_DISABLED;
    const IS_TODAY_DISABLED = Type\Date::IS_TODAY_DISABLED;
    const PAST_OFFSET = Type\Date::PAST_OFFSET;
    const FUTURE_OFFSET = Type\Date::FUTURE_OFFSET;
    const DISABLED_WEEK_DAYS = Type\Date::DISABLED_WEEK_DAYS;
    const DISABLED_CUSTOM_DATES = Type\Date::DISABLED_CUSTOM_DATES;

    /**
     * @var DaysOfWeek
     */
    private $daysOfWeek;
    /**
     * @var DataBundle
     */
    private $dataBundle;

    /**
     * Date constructor.
     *
     * @param DataBundle $dataBundle
     * @param DaysOfWeek $daysOfWeek
     */
    public function __construct(
        DataBundle $dataBundle,
        DaysOfWeek $daysOfWeek
    )
    {
        $this->daysOfWeek = $daysOfWeek;
        $this->dataBundle = $dataBundle;
    }

    /**
     * @inheritDoc
     */
    public function getUiMeta(string $prefix = ''): array
    {
        return [
            'information' => [
                'children' => [
                    $prefix . '_' . static::PLACEHOLDER => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::PLACEHOLDER,
                                    'visible' => 0,
                                    'sortOrder' => 40,
                                    'label' => __('Placeholder'),
                                    'additionalInfo' => __('Placeholder text will appear in the input and disappear on the focus'),
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_TODAY_DISABLED => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_TODAY_DISABLED,
                                    'visible' => 0,
                                    'sortOrder' => 41,
                                    'label' => __('Disable Today'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::DISABLED_WEEK_DAYS => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\MultiSelect::NAME,
                                    'dataScope' => static::DISABLED_WEEK_DAYS,
                                    'visible' => 0,
                                    'sortOrder' => 42,
                                    'label' => __('Disabled Days'),
                                    'additionalInfo' => __('Disable days of the week.'),
                                    'options' => $this->daysOfWeek->toOptionArray(),
                                    'size' => 7,
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_PAST_DISABLED => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_PAST_DISABLED,
                                    'visible' => 0,
                                    'sortOrder' => 46,
                                    'label' => __('Disable Past Dates'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::PAST_OFFSET => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::PAST_OFFSET,
                                    'visible' => 0,
                                    'sortOrder' => 47,
                                    'label' => __('Past Offset(days)'),
                                    'additionalInfo' => __('Shift disabled days in future or in the past. Use negative values to shift dates to the past.'),
                                    'validation' => [
                                        'validate-number' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::IS_FUTURE_DISABLED => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Checkbox::NAME,
                                    'dataType' => Form\Element\DataType\Boolean::NAME,
                                    'dataScope' => static::IS_FUTURE_DISABLED,
                                    'visible' => 0,
                                    'sortOrder' => 48,
                                    'label' => __('Disable Future Dates'),
                                    'default' => '0',
                                    'prefer' => 'toggle',
                                    'valueMap' => ['false' => '0', 'true' => '1'],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::FUTURE_OFFSET => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'dataType' => Form\Element\DataType\Number::NAME,
                                    'dataScope' => static::FUTURE_OFFSET,
                                    'visible' => 0,
                                    'sortOrder' => 49,
                                    'label' => __('Future Offset(days)'),
                                    'additionalInfo' => __('Shift disabled days in future or in the past. Use negative values to shift dates to the past.'),
                                    'validation' => [
                                        'validate-number' => true,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    $prefix . '_' . static::DISABLED_CUSTOM_DATES => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'source' => 'field',
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Textarea::NAME,
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'dataScope' => static::DISABLED_CUSTOM_DATES,
                                    'visible' => 0,
                                    'sortOrder' => 52,
                                    'label' => __('Custom Disabled Dates'),
                                    'additionalInfo' => __('Dates should be separated with ",", ";" or new line<br>
                                                                  Use <i>dd (example 01)</i> or <i>d (example 1)</i> to add single date for all months and years<br>
                                                                  Use <i>dd.mm (example 01.01)</i> or <i>d.m (example 1.1)</i> to add single date for all years<br>
                                                                  Use <i>dd.mm.yyyy (example 01.01.1999)</i> or <i>d.m.yyyy (example 1.1.1999)</i> to add single date<br>
                                                                  Use <i>01-03</i> or <i>1-3</i> to add date range for all months and years<br>
                                                                  Use <i>01.01-03.01</i> or <i>1.1-3.1</i> to add date range for all years
                                                                  Use <i>01.01.1998-03.01.1999</i> or <i>1.1.1998-3.1.1999</i> to add date range'),
                                    'rows' => 5,
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
        $config               = $this->getDefaultUIResultColumnConfig($sortOrder);
        $config['dataType']   = DataType::DATE;
        $config['filter']     = Filter::DATE_RANGE;
        $config['component']  = Component::DATE;
        $config['dateFormat'] = $this->getField()->getGridFormat();
        $config['storeLocale'] = $this->getField()->getLocale();

        $localeData = $this->dataBundle->get($this->getField()->getLocale());
        $monthsData = $localeData['calendar']['gregorian']['monthNames'];
        $months = array_values(iterator_to_array($monthsData['format']['wide']));
        $monthsShort = array_values(
            iterator_to_array(
                null !== $monthsData->get('format')->get('abbreviated')
                    ? $monthsData['format']['abbreviated']
                    : $monthsData['format']['wide']
            )
        );
        $config['calendarConfig'] = [
            'months' => $months,
            'monthsShort' => $monthsShort,
        ];

        return $config;
    }

    /**
     * @inheritDoc
     */
    public function getResultAdminFormConfig(ResultInterface $result = null): array
    {
        $config                = $this->getDefaultResultAdminFormConfig();
        $config['type']        = 'date';
        $config['date_format'] = $this->getField()->getDateFormat();
        return $config;
    }
}