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

namespace MageMe\WebForms\Helper\ConvertVersion;


use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Config\Options\Field\DisplayOption;
use MageMe\WebForms\Model\Field\Type;
use MageMe\WebForms\Model\FieldFactory;
use MageMe\WebForms\Setup\Table\FieldTable;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class FieldConverter
{
    /**#@+
     * V2 constants
     */
    const ID = 'id';
    const WEBFORM_ID = 'webform_id';
    const FIELDSET_ID = 'fieldset_id';
    const NAME = 'name';
    const CODE = 'code';
    const COMMENT = 'comment';
    const RESULT_LABEL = 'result_label';
    const RESULT_DISPLAY = 'result_display';
    const TYPE = 'type';
    const SIZE = 'size';
    const VALUE = 'value';
    const EMAIL_SUBJECT = 'email_subject';
    const CSS_CLASS = 'css_class';
    const CSS_CLASS_CONTAINER = 'css_class_container';
    const CSS_STYLE = 'css_style';
    const VALIDATE_MESSAGE = 'validate_message';
    const VALIDATE_REGEX = 'validate_regex';
    const VALIDATE_LENGTH_MIN = 'validate_length_min';
    const VALIDATE_LENGTH_MAX = 'validate_length_max';
    const VALIDATE_LENGTH_MIN_MESSAGE = 'validate_length_min_message';
    const VALIDATE_LENGTH_MAX_MESSAGE = 'validate_length_max_message';
    const POSITION = 'position';
    const REQUIRED = 'required';
    const VALIDATION_ADVICE = 'validation_advice';
    const CREATED_TIME = 'created_time';
    const UPDATE_TIME = 'update_time';
    const IS_ACTIVE = 'is_active';
    const HINT = 'hint';
    const VALIDATE_UNIQUE = 'validate_unique';
    const VALIDATE_UNIQUE_MESSAGE = 'validate_unique_message';
    const BROWSER_AUTOCOMPLETE = 'browser_autocomplete';
    const HIDE_LABEL = 'hide_label';
    const INLINE_ELEMENTS = 'inline_elements';
    const WIDTH_LG = 'width_lg';
    const WIDTH_MD = 'width_md';
    const WIDTH_SM = 'width_sm';
    const ROW_LG = 'row_lg';
    const ROW_MD = 'row_md';
    const ROW_SM = 'row_sm';
    const CUSTOM_ATTRIBUTES = 'custom_attributes';
    const TYPE_ATTRIBUTES = 'type_attributes';
    /**#@-*/

    const TABLE_FIELDS = 'webforms_fields';

    /**
     * For display in result fix
     * @var array
     */
    private $displayOptions = [
        DisplayOption::OPTION_ON,
        DisplayOption::OPTION_OFF,
        DisplayOption::OPTION_VALUE
    ];

    /**
     * @var FieldFactory
     */
    private $fieldFactory;

    /**
     * @var FieldRepositoryInterface
     */
    private $fieldRepository;

    /**
     * FieldConverter constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param FieldFactory $fieldFactory
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        FieldFactory             $fieldFactory
    )
    {
        $this->fieldFactory    = $fieldFactory;
        $this->fieldRepository = $fieldRepository;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function convert(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select     = $connection->select()->from($setup->getTable(self::TABLE_FIELDS));
        $query      = $select->query()->fetchAll();
        foreach ($query as $oldData) {
            $connection->insertOnDuplicate($setup->getTable(FieldTable::TABLE_NAME), [
                FieldInterface::ID => $oldData[self::ID],
                FieldInterface::FORM_ID => $oldData[self::WEBFORM_ID],
                FieldInterface::NAME => $oldData[self::NAME],
                FieldInterface::TYPE => $oldData[self::TYPE]
            ]);
            $fieldData = $this->convertV2Data($oldData);
            $field     = $this->fieldFactory->create($fieldData[FieldInterface::TYPE]);
            $field->setData($fieldData);
            $this->fieldRepository->save($field);
        }
    }

    /**
     * @param array $oldData
     * @return array
     */
    public function convertV2Data(array $oldData): array
    {
        $oldData   = $this->convertFieldValue($oldData);
        $fieldData = [
            FieldInterface::ID => $oldData[self::ID] ?? null,
            FieldInterface::FORM_ID => $oldData[self::WEBFORM_ID] ?? null,
            FieldInterface::FIELDSET_ID => $oldData[self::FIELDSET_ID] ?? null,
            FieldInterface::NAME => $oldData[self::NAME] ?? null,
            FieldInterface::TYPE => $oldData[self::TYPE] ?? null,
            FieldInterface::CODE => $oldData[self::CODE] ?? null,
            FieldInterface::RESULT_LABEL => $oldData[self::RESULT_LABEL] ?? null,
            FieldInterface::COMMENT => $oldData[self::COMMENT] ?? null,
            FieldInterface::IS_EMAIL_SUBJECT => $oldData[self::EMAIL_SUBJECT] ?? null,
            FieldInterface::IS_REQUIRED => $oldData[self::REQUIRED] ?? null,
            FieldInterface::VALIDATION_REQUIRED_MESSAGE => $oldData[self::VALIDATION_ADVICE] ?? null,
            FieldInterface::POSITION => $oldData[self::POSITION] ?? null,
            FieldInterface::IS_ACTIVE => $oldData[self::IS_ACTIVE] ?? null,
            FieldInterface::CREATED_AT => $oldData[self::CREATED_TIME] ?? null,
            FieldInterface::UPDATED_AT => $oldData[self::UPDATE_TIME] ?? null,

            FieldInterface::IS_LABEL_HIDDEN => $oldData[self::HIDE_LABEL] ?? null,
            FieldInterface::CUSTOM_ATTRIBUTES => $oldData[self::CUSTOM_ATTRIBUTES] ?? null,
            FieldInterface::WIDTH_PROPORTION_LG => $oldData[self::WIDTH_LG] ?? null,
            FieldInterface::WIDTH_PROPORTION_MD => $oldData[self::WIDTH_MD] ?? null,
            FieldInterface::WIDTH_PROPORTION_SM => $oldData[self::WIDTH_SM] ?? null,
            FieldInterface::IS_DISPLAYED_IN_NEW_ROW_LG => $oldData[self::ROW_LG] ?? null,
            FieldInterface::IS_DISPLAYED_IN_NEW_ROW_MD => $oldData[self::ROW_MD] ?? null,
            FieldInterface::IS_DISPLAYED_IN_NEW_ROW_SM => $oldData[self::ROW_SM] ?? null,
            FieldInterface::CSS_CONTAINER_CLASS => $oldData[self::CSS_CLASS_CONTAINER] ?? null,
            FieldInterface::CSS_INPUT_CLASS => $oldData[self::CSS_CLASS] ?? null,
            FieldInterface::CSS_INPUT_STYLE => $oldData[self::CSS_STYLE] ?? null,
            FieldInterface::DISPLAY_IN_RESULT => in_array($oldData[self::RESULT_DISPLAY],
                $this->displayOptions) ? $oldData[self::RESULT_DISPLAY] : DisplayOption::OPTION_ON,
            FieldInterface::BROWSER_AUTOCOMPLETE => $oldData[self::BROWSER_AUTOCOMPLETE] ?? null,

            FieldInterface::IS_UNIQUE => $oldData[self::VALIDATE_UNIQUE] ?? null,
            FieldInterface::UNIQUE_VALIDATION_MESSAGE => $oldData[self::VALIDATE_UNIQUE_MESSAGE] ?? null,
            FieldInterface::MIN_LENGTH => $oldData[self::VALIDATE_LENGTH_MIN] ?? null,
            FieldInterface::MIN_LENGTH_VALIDATION_MESSAGE => $oldData[self::VALIDATE_LENGTH_MIN_MESSAGE] ?? null,
            FieldInterface::MAX_LENGTH => $oldData[self::VALIDATE_LENGTH_MAX] ?? null,
            FieldInterface::MAX_LENGTH_VALIDATION_MESSAGE => $oldData[self::VALIDATE_LENGTH_MAX_MESSAGE] ?? null,
            FieldInterface::REGEX_VALIDATION_PATTERN => $oldData[self::VALIDATE_REGEX] ?? null,
            FieldInterface::REGEX_VALIDATION_MESSAGE => $oldData[self::VALIDATE_MESSAGE] ?? null
        ];
        switch ($fieldData[FieldInterface::TYPE]) {
            case 'text':
            {
                $fieldData[Type\Text::PLACEHOLDER] = $oldData[self::HINT] ?? '';
                $fieldData[Type\Text::TEXT]        = $oldData[Type\Text::TEXT] ?? '';
                break;
            }
            case 'email':
            {
                $fieldData[Type\Email::PLACEHOLDER]                      = $oldData[self::HINT] ?? '';
                $fieldData[Type\Email::TEXT]                             = $oldData[Type\Email::TEXT] ?? '';
                $fieldData[Type\Email::IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL] = $oldData[Type\Email::IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL] ?? '';
                break;
            }
            case 'number':
            {
                $fieldData[Type\Number::PLACEHOLDER] = $oldData[self::HINT] ?? '';
                $fieldData[Type\Number::MIN]         = $oldData[Type\Number::MIN] ?? '';
                $fieldData[Type\Number::MAX]         = $oldData[Type\Number::MAX] ?? '';
                break;
            }
            case 'url':
            {
                $fieldData[Type\Url::PLACEHOLDER] = $oldData[self::HINT] ?? '';
                $fieldData[Type\Url::TEXT]        = $oldData[Type\Url::TEXT] ?? '';
                break;
            }
            case 'password':
            {
                $fieldData[Type\Password::PLACEHOLDER] = $oldData[self::HINT] ?? '';
                break;
            }
            case 'autocomplete':
            {
                $fieldData[Type\Autocomplete::TEXT] = $oldData[Type\Autocomplete::TEXT] ?? '';
                break;
            }
            case 'textarea':
            {
                $fieldData[Type\Textarea::PLACEHOLDER] = $oldData[self::HINT] ?? '';
                $fieldData[Type\Textarea::TEXT]        = $oldData[Type\Textarea::TEXT] ?? '';
                break;
            }
            case 'colorpicker':
            {
                $fieldData[Type\Colorpicker::PLACEHOLDER] = $oldData[self::HINT] ?? '';
                break;
            }
            case 'select':
            {
                $fieldData[Type\Select::OPTIONS]        = $oldData[Type\Select::OPTIONS] ?? '';
                $fieldData[Type\Select::IS_MULTISELECT] = $oldData[Type\Select::IS_MULTISELECT] ?? '';
                break;
            }
            case 'select_radio':
            {
                $fieldData[Type\SelectRadio::OPTIONS]                     = $oldData[Type\SelectRadio::OPTIONS] ?? '';
                $fieldData[Type\SelectRadio::IS_INTERNAL_ELEMENTS_INLINE] = $oldData[self::INLINE_ELEMENTS] ?? '';
                break;
            }
            case 'select_checkbox':
            {
                $fieldData[Type\SelectCheckbox::OPTIONS]                     = $oldData[Type\SelectCheckbox::OPTIONS] ?? '';
                $fieldData[Type\SelectCheckbox::MIN_OPTIONS]                 = $oldData[Type\SelectCheckbox::MIN_OPTIONS] ?? '';
                $fieldData[Type\SelectCheckbox::MAX_OPTIONS]                 = $oldData[Type\SelectCheckbox::MAX_OPTIONS] ?? '';
                $fieldData[Type\SelectCheckbox::MIN_OPTIONS_ERROR_TEXT]      = $oldData[Type\SelectCheckbox::MIN_OPTIONS_ERROR_TEXT] ?? '';
                $fieldData[Type\SelectCheckbox::MAX_OPTIONS_ERROR_TEXT]      = $oldData[Type\SelectCheckbox::MAX_OPTIONS_ERROR_TEXT] ?? '';
                $fieldData[Type\SelectCheckbox::IS_INTERNAL_ELEMENTS_INLINE] = $oldData[self::INLINE_ELEMENTS] ?? '';
                break;
            }
            case 'select_contact':
            {
                $fieldData[Type\SelectContact::OPTIONS] = $oldData[Type\SelectContact::OPTIONS] ?? '';
                break;
            }
            case 'country':
            {
                $fieldData[Type\Country::DEFAULT_COUNTRY] = $oldData[Type\Country::DEFAULT_COUNTRY] ?? '';
                break;
            }
            case 'region':
            {
                $fieldData[Type\Region::COUNTRY_FIELD_ID] = $oldData[Type\Region::COUNTRY_FIELD_ID] ?? '';
                break;
            }
            case 'subscribe':
            {
                $fieldData[Type\Subscribe::TEXT] = $oldData[Type\Subscribe::TEXT] ?? '';
                break;
            }
            case 'date':
            {
                $fieldData[Type\Date::PLACEHOLDER] = $oldData[self::HINT] ?? '';
                break;
            }
            case 'date_dob':
            {
                $fieldData[Type\Dob::PLACEHOLDER]               = $oldData[self::HINT] ?? '';
                $fieldData[Type\Dob::IS_FILLED_BY_CUSTOMER_DOB] = $oldData[Type\Dob::IS_FILLED_BY_CUSTOMER_DOB] ?? '';
                break;
            }
            case 'stars':
            {
                $fieldData[Type\Stars::INIT_STARS] = $oldData[Type\Stars::INIT_STARS] ?? '';
                $fieldData[Type\Stars::MAX_STARS]  = $oldData[Type\Stars::MAX_STARS] ?? '';
                break;
            }
            case 'file':
            {
                $fieldData[Type\File::IS_DROPZONE]        = $oldData[Type\File::IS_DROPZONE] ?? '';
                $fieldData[Type\File::DROPZONE_TEXT]      = $oldData[Type\File::DROPZONE_TEXT] ?? '';
                $fieldData[Type\File::DROPZONE_MAX_FILES] = $oldData[Type\File::DROPZONE_MAX_FILES] ?? '';
                $fieldData[Type\File::ALLOWED_EXTENSIONS] = $oldData[Type\File::ALLOWED_EXTENSIONS] ?? '';
                break;
            }
            case 'image':
            {
                $fieldData[Type\Image::IS_DROPZONE]        = $oldData[Type\Image::IS_DROPZONE] ?? '';
                $fieldData[Type\Image::DROPZONE_TEXT]      = $oldData[Type\Image::DROPZONE_TEXT] ?? '';
                $fieldData[Type\Image::DROPZONE_MAX_FILES] = $oldData[Type\Image::DROPZONE_MAX_FILES] ?? '';
                $fieldData[Type\Image::IS_RESIZED]         = $oldData[Type\Image::IS_RESIZED] ?? '';
                $fieldData[Type\Image::RESIZE_WIDTH]       = $oldData[Type\Image::RESIZE_WIDTH] ?? '';
                $fieldData[Type\Image::RESIZE_HEIGHT]      = $oldData[Type\Image::RESIZE_HEIGHT] ?? '';
                break;
            }
            case 'html':
            {
                $fieldData[Type\Html::HTML] = $oldData[Type\Html::HTML] ?? '';
                break;
            }
            case 'hidden':
            {
                $fieldData[Type\Hidden::TEXT] = $oldData[Type\Hidden::TEXT] ?? '';
                break;
            }
            default:
            {
                break;
            }
        }
        return $fieldData;
    }

    /**
     * @param array $fieldData
     * @return array
     */
    protected function convertFieldValue(array $fieldData): array
    {
        if (!isset($fieldData[self::TYPE])) {
            $fieldData[self::TYPE] = 'text';
        }
        if (strpos((string)$fieldData[self::TYPE], '/')) {
            $fieldData[self::TYPE] = str_replace('/', '_', (string)$fieldData[self::TYPE]);
        }
        if (empty($fieldData[self::VALUE])) {
            $this->setTypeValues($fieldData);
        }
        $value = $this->unserializeV2Value($fieldData[self::VALUE]);
        switch ($fieldData['type']) {
            case 'url':
                if (!empty($value["text_url"])) {
                    $value["text"] = $value["text_url"];
                }
                break;
            case 'email':
                if (!empty($value["text_email"])) {
                    $value["text"] = $value["text_email"];
                }
                break;
            case 'select_radio':
                if (!empty($value["options_radio"])) {
                    $value["options"] = $value["options_radio"];
                }
                break;
            case 'select_checkbox':
                if (!empty($value["options_checkbox"])) {
                    $value["options"] = $value["options_checkbox"];
                }
                break;
            case 'select_contact':
                if (!empty($value["options_contact"])) {
                    $value["options"] = $value["options_contact"];
                }
                break;
        }
        if (!empty($value["text"])) {
            if (!empty($value["text_url"])) {
                $value["text_url"] = $value["text"];
            }
            if (!empty($value["text_email"])) {
                $value["text_email"] = $value["text"];
            }
        }
        if (!empty($value["options"])) {
            if (empty($value["options_radio"])) {
                $value["options_radio"] = $value["options"];
            }
            if (empty($value["options_checkbox"])) {
                $value["options_checkbox"] = $value["options"];
            }
            if (empty($value["options_contact"])) {
                $value["options_contact"] = $value["options"];
            }
        }
        if (!empty($value["dropzone"])) {
            if (empty($value["dropzone_image"])) {
                $value["dropzone_image"] = $value["dropzone"];
            }
        }
        if (!empty($value["dropzone_text"])) {
            if (empty($value["dropzone_text_image"])) {
                $value["dropzone_text_image"] = $value["dropzone_text"];
            }
        }
        if (!empty($value["dropzone_maxfiles"])) {
            if (empty($value["dropzone_maxfiles_image"])) {
                $value["dropzone_maxfiles_image"] = $value["dropzone_maxfiles"];
            }
        }

        return $this->setTypeValues($fieldData, $value);
    }

    protected function setTypeValues(array $fieldData, array $value = []): array
    {
        $value['text']                            = $value['text'] ?? '';
        $value['text_email']                      = $value['text_email'] ?? '';
        $value['assign_customer_id_by_email']     =
            (isset($value['assign_customer_id_by_email']) && !empty($value["assign_customer_id_by_email"])) ?
                $value['assign_customer_id_by_email'] : 0;
        $value['number_min']                      = $value['number_min'] ?? '';
        $value['number_max']                      = $value['number_max'] ?? '';
        $value['text_url']                        = $value['text_url'] ?? '';
        $value['autocomplete_choices']            = $value['autocomplete_choices'] ?? '';
        $value['textarea']                        = $value['textarea'] ?? '';
        $value['options']                         = $value['options'] ?? '';
        $value['multiselect']                     =
            (isset($value['multiselect']) && !empty($value["multiselect"])) ? $value['multiselect'] : 0;
        $value['options_radio']                   = $value['options_radio'] ?? '';
        $value['options_checkbox']                = $value['options_checkbox'] ?? '';
        $value['options_checkbox_min']            = $value['options_checkbox_min'] ?? '';
        $value['options_checkbox_max']            = $value['options_checkbox_max'] ?? '';
        $value['options_checkbox_min_error_text'] = $value['options_checkbox_min_error_text'] ?? '';
        $value['options_checkbox_max_error_text'] = $value['options_checkbox_max_error_text'] ?? '';
        $value['default_country']                 = $value['default_country'] ?? '';
        $value['newsletter_label']                = $value['newsletter_label'] ?? '';
        $value['dob_customer']                    = $value['dob_customer'] ?? '';
        $value['stars_init']                      =
            (isset($value['stars_init']) && !empty($value["stars_init"])) ? $value['stars_init'] : 3;
        $value['stars_max']                       =
            (isset($value['stars_max']) && !empty($value["stars_max"])) ? $value['stars_max'] : 5;
        $value['allowed_extensions']              = $value['allowed_extensions'] ?? '';
        $value['dropzone']                        =
            (isset($value['dropzone']) && !empty($value["dropzone"])) ? $value['dropzone'] : 0;
        $value['dropzone_text']                   =
            (isset($value['dropzone_text']) && !empty($value["dropzone_text"])) ? $value['dropzone_text'] : 'Add files or drop here';
        $value['dropzone_maxfiles']               =
            (isset($value['dropzone_maxfiles']) && !empty($value["dropzone_maxfiles"])) ? $value['dropzone_maxfiles'] : 5;
        $value['dropzone_image']                  =
            (isset($value['dropzone_image']) && !empty($value["dropzone_image"])) ? $value['dropzone_image'] : 0;
        $value['dropzone_text_image']             =
            (isset($value['dropzone_text_image']) && !empty($value["dropzone_text_image"])) ?
                $value['dropzone_text_image'] : 'Add files or drop here';
        $value['dropzone_maxfiles_image']         =
            (isset($value['dropzone_maxfiles_image']) && !empty($value["dropzone_maxfiles_image"])) ?
                $value['dropzone_maxfiles_image'] : 5;
        $value['image_resize']                    =
            (isset($value['image_resize']) && !empty($value["image_resize"])) ? $value['image_resize'] : 0;
        $value['image_resize_width']              = $value['image_resize_width'] ?? '';
        $value['image_resize_height']             = $value['image_resize_height'] ?? '';
        $value['html']                            = $value['html'] ?? '';
        $value['hidden']                          = $value['hidden'] ?? '';
        $value['region_country_field_id']         = $value['region_country_field_id'] ?? null;

        switch ($fieldData['type']) {
            case 'text':
            {
                if (!empty($value['text']))

                    $fieldData[Type\Text::TEXT] = $value['text'];
                break;
            }
            case 'email':
            {
                if (!empty($value['text_email']))
                    $fieldData[Type\Email::TEXT] = $value['text_email'];
                if (!empty($value['assign_customer_id_by_email']))
                    $fieldData[Type\Email::IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL] = (string)(int)$value['assign_customer_id_by_email'];
                break;
            }
            case 'number':
            {
                if (!empty($value['number_min']))
                    $fieldData[Type\Number::MIN] = (int)$value['number_min'];
                if (!empty($value['number_max']))
                    $fieldData[Type\Number::MAX] = (int)$value['number_max'];
                break;
            }
            case 'url':
            {
                if (!empty($value['text_url']))
                    $fieldData[Type\Url::TEXT] = $value['text_url'];
                break;
            }
            case 'autocomplete':
            {
                if (!empty($value['autocomplete_choices']))
                    $fieldData[Type\Autocomplete::TEXT] = $value['autocomplete_choices'];
                break;
            }
            case 'textarea':
            {
                if (!empty($value['textarea']))
                    $fieldData[Type\Textarea::TEXT] = $value['textarea'];
                break;
            }
            case 'select':
            {
                if (!empty($value['options']))
                    $fieldData[Type\Select::OPTIONS] = $value['options'];
                if (!empty($value['multiselect']))
                    $fieldData[Type\Select::IS_MULTISELECT] = (string)(int)$value['multiselect'];
                break;
            }
            case 'select_radio':
            {
                if (!empty($value['options_radio']))
                    $fieldData[Type\SelectRadio::OPTIONS] = $value['options_radio'];
                break;
            }
            case 'select_checkbox':
            {
                if (!empty($value['options_checkbox']))
                    $fieldData[Type\SelectCheckbox::OPTIONS] = $value['options_checkbox'];
                if (!empty($value['options_checkbox_min']))
                    $fieldData[Type\SelectCheckbox::MIN_OPTIONS] = (int)$value['options_checkbox_min'];
                if (!empty($value['options_checkbox_max']))
                    $fieldData[Type\SelectCheckbox::MAX_OPTIONS] = (int)$value['options_checkbox_max'];
                if (!empty($value['options_checkbox_min_error_text']))
                    $fieldData[Type\SelectCheckbox::MIN_OPTIONS_ERROR_TEXT] = $value['options_checkbox_min_error_text'];
                if (!empty($value['options_checkbox_max_error_text']))
                    $fieldData[Type\SelectCheckbox::MAX_OPTIONS_ERROR_TEXT] = $value['options_checkbox_max_error_text'];
                break;
            }
            case 'select_contact':
            {
                if (!empty($value['options_contact']))
                    $fieldData[Type\SelectContact::OPTIONS] = $value['options_contact'];
                break;
            }
            case 'country':
            {
                if (!empty($value['default_country']))
                    $fieldData[Type\Country::DEFAULT_COUNTRY] = $value['default_country'];
                break;
            }
            case 'region':
            {
                if (!empty($value['region_country_field_id']))
                    $fieldData[Type\Region::COUNTRY_FIELD_ID] = $value['region_country_field_id'];
                break;
            }
            case 'subscribe':
            {
                if (!empty($value['newsletter_label']))
                    $fieldData[Type\Subscribe::TEXT] = $value['newsletter_label'];
                break;
            }
            case 'date_dob':
            {
                if (!empty($value['dob_customer']))
                    $fieldData[Type\Dob::IS_FILLED_BY_CUSTOMER_DOB] = (string)(int)$value['dob_customer'];
                break;
            }
            case 'stars':
            {
                if (!empty($value['stars_init']))
                    $fieldData[Type\Stars::INIT_STARS] = (int)$value['stars_init'];
                if (!empty($value['stars_max']))
                    $fieldData[Type\Stars::MAX_STARS] = (int)$value['stars_max'];
                break;
            }
            case 'file':
            {
                if (!empty($value['allowed_extensions']))
                    $fieldData[Type\File::ALLOWED_EXTENSIONS] = $value['allowed_extensions'];
                if (!empty($value['dropzone']))
                    $fieldData[Type\File::IS_DROPZONE] = (string)(int)$value['dropzone'];
                if (!empty($value['dropzone_text']))
                    $fieldData[Type\File::DROPZONE_TEXT] = $value['dropzone_text'];
                if (!empty($value['dropzone_maxfiles']))
                    $fieldData[Type\File::DROPZONE_MAX_FILES] = (int)$value['dropzone_maxfiles'];
                break;
            }
            case 'image':
            {
                if (!empty($value['dropzone_image']))
                    $fieldData[Type\Image::IS_DROPZONE] = (string)(int)$value['dropzone_image'];
                if (!empty($value['dropzone_text_image']))
                    $fieldData[Type\Image::DROPZONE_TEXT] = $value['dropzone_text_image'];
                if (!empty($value['dropzone_maxfiles_image']))
                    $fieldData[Type\Image::DROPZONE_MAX_FILES] = (int)$value['dropzone_maxfiles_image'];
                if (!empty($value['image_resize']))
                    $fieldData[Type\Image::IS_RESIZED] = (string)(int)$value['image_resize'];
                if (!empty($value['image_resize_width']))
                    $fieldData[Type\Image::RESIZE_WIDTH] = (int)$value['image_resize_width'];
                if (!empty($value['image_resize_height']))
                    $fieldData[Type\Image::RESIZE_HEIGHT] = (int)$value['image_resize_height'];
                break;
            }
            case 'html':
            {
                if (!empty($value['html']))
                    $fieldData[Type\Html::HTML] = $value['html'];
                break;
            }
            case 'hidden':
            {
                if (!empty($value['hidden']))
                    $fieldData[Type\Hidden::TEXT] = $value['hidden'];
                break;
            }
            case 'password':
            case 'wysiwyg':
            case 'date':
            case 'colorpicker':
            {
                break;
            }
        }
        return $fieldData;
    }

    /**
     * @param mixed $value
     * @return array
     */
    protected function unserializeV2Value($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        $unserialized_value = @unserialize($value);
        if ($unserialized_value) {
            $value = $unserialized_value;
        } else {

            // support for old value format
            $stars_value = explode("\n", (string)$value);
            if (empty($stars_value[1])) {
                $stars_value[1] = false;
            }
            $value_array = [
                'text' => $value,
                'text_email' => $value,
                'text_url' => $value,
                'textarea' => $value,
                'newsletter' => $value,
                'stars_init' => $stars_value[1],
                'stars_max' => $stars_value[0],
                'options' => $value,
                'options_radio' => $value,
                'options_checkbox' => $value,
                'options_contact' => $value,
                'allowed_extensions' => $value,
                'html' => $value,
                'hidden' => $value,
            ];
            $value       = $value_array;
        }
        return $value;
    }

    /**
     * Convert V2 store data
     *
     * @param array $storeData
     * @param string $type
     * @return array
     */
    public function convertV2StoreData(array $storeData, string $type): array
    {
        $newData = [];
        foreach ($storeData as $key => $value) {
            switch ($key) {
                case self::FIELDSET_ID:
                {
                    $newData[FieldInterface::FIELDSET_ID] = $value;
                    break;
                }
                case self::NAME:
                {
                    $newData[FieldInterface::NAME] = $value;
                    break;
                }
                case self::TYPE:
                {
                    if (strpos((string)$value, '/')) {
                        $value = str_replace('/', '_', (string)$value);
                    }
                    $newData[FieldInterface::TYPE] = $value;
                    break;
                }
                case self::CODE:
                {
                    $newData[FieldInterface::CODE] = $value;
                    break;
                }
                case self::RESULT_LABEL:
                {
                    $newData[FieldInterface::RESULT_LABEL] = $value;
                    break;
                }
                case self::COMMENT:
                {
                    $newData[FieldInterface::COMMENT] = $value;
                    break;
                }
                case self::EMAIL_SUBJECT:
                {
                    $newData[FieldInterface::IS_EMAIL_SUBJECT] = $value;
                    break;
                }
                case self::REQUIRED:
                {
                    $newData[FieldInterface::IS_REQUIRED] = $value;
                    break;
                }
                case self::VALIDATION_ADVICE:
                {
                    $newData[FieldInterface::VALIDATION_REQUIRED_MESSAGE] = $value;
                    break;
                }
                case self::POSITION:
                {
                    $newData[FieldInterface::POSITION] = $value;
                    break;
                }
                case self::IS_ACTIVE:
                {
                    $newData[FieldInterface::IS_ACTIVE] = $value;
                    break;
                }

                case self::HIDE_LABEL:
                {
                    $newData[FieldInterface::IS_LABEL_HIDDEN] = $value;
                    break;
                }
                case self::CUSTOM_ATTRIBUTES:
                {
                    $newData[FieldInterface::CUSTOM_ATTRIBUTES] = $value;
                    break;
                }
                case self::WIDTH_LG:
                {
                    $newData[FieldInterface::WIDTH_PROPORTION_LG] = $value;
                    break;
                }
                case self::WIDTH_MD:
                {
                    $newData[FieldInterface::WIDTH_PROPORTION_MD] = $value;
                    break;
                }
                case self::WIDTH_SM:
                {
                    $newData[FieldInterface::WIDTH_PROPORTION_SM] = $value;
                    break;
                }
                case self::ROW_LG:
                {
                    $newData[FieldInterface::IS_DISPLAYED_IN_NEW_ROW_LG] = $value;
                    break;
                }
                case self::ROW_MD:
                {
                    $newData[FieldInterface::IS_DISPLAYED_IN_NEW_ROW_MD] = $value;
                    break;
                }
                case self::ROW_SM:
                {
                    $newData[FieldInterface::IS_DISPLAYED_IN_NEW_ROW_SM] = $value;
                    break;
                }
                case self::CSS_CLASS_CONTAINER:
                {
                    $newData[FieldInterface::CSS_CONTAINER_CLASS] = $value;
                    break;
                }
                case self::CSS_CLASS:
                {
                    $newData[FieldInterface::CSS_INPUT_CLASS] = $value;
                    break;
                }
                case self::CSS_STYLE:
                {
                    $newData[FieldInterface::CSS_INPUT_STYLE] = $value;
                    break;
                }
                case self::RESULT_DISPLAY:
                {
                    $newData[FieldInterface::DISPLAY_IN_RESULT] = in_array($value,
                        $this->displayOptions) ? $value : DisplayOption::OPTION_ON;
                    break;
                }
                case self::BROWSER_AUTOCOMPLETE:
                {
                    $newData[FieldInterface::BROWSER_AUTOCOMPLETE] = $value;
                    break;
                }

                case self::VALIDATE_UNIQUE:
                {
                    $newData[FieldInterface::IS_UNIQUE] = $value;
                    break;
                }
                case self::VALIDATE_UNIQUE_MESSAGE:
                {
                    $newData[FieldInterface::UNIQUE_VALIDATION_MESSAGE] = $value;
                    break;
                }
                case self::VALIDATE_LENGTH_MIN:
                {
                    $newData[FieldInterface::MIN_LENGTH] = $value;
                    break;
                }
                case self::VALIDATE_LENGTH_MIN_MESSAGE:
                {
                    $newData[FieldInterface::MIN_LENGTH_VALIDATION_MESSAGE] = $value;
                    break;
                }
                case self::VALIDATE_LENGTH_MAX:
                {
                    $newData[FieldInterface::MAX_LENGTH] = $value;
                    break;
                }
                case self::VALIDATE_LENGTH_MAX_MESSAGE:
                {
                    $newData[FieldInterface::MAX_LENGTH_VALIDATION_MESSAGE] = $value;
                    break;
                }
                case self::VALIDATE_REGEX:
                {
                    $newData[FieldInterface::REGEX_VALIDATION_PATTERN] = $value;
                    break;
                }
                case self::VALIDATE_MESSAGE:
                {
                    $newData[FieldInterface::REGEX_VALIDATION_MESSAGE] = $value;
                    break;
                }
                default:
                {
                    break;
                }
            }
        }
        if (isset($storeData[self::VALUE])) {
            if (!empty($newData[FieldInterface::TYPE])) {
                $type = $newData[FieldInterface::TYPE];
            }
            $value = $this->unserializeV2Value($storeData[self::VALUE]);

            switch ($type) {
                case 'text':
                {
                    if (isset($storeData[self::HINT])) {
                        $newData[Type\Text::PLACEHOLDER] = $storeData[self::HINT];
                    }
                    if (isset($value['text'])) {
                        $newData[Type\Text::TEXT] = $value['text'];
                    }
                    break;
                }
                case 'email':
                {
                    if (isset($storeData[self::HINT])) {
                        $newData[Type\Email::PLACEHOLDER] = $storeData[self::HINT];
                    }
                    if (isset($value['text_email'])) {
                        $newData[Type\Email::TEXT] = $value['text_email'];
                    }
                    if (isset($value['assign_customer_id_by_email'])) {
                        $newData[Type\Email::IS_ASSIGNED_CUSTOMER_ID_BY_EMAIL] = (string)(int)$value['assign_customer_id_by_email'];
                    }
                    break;
                }
                case 'number':
                {
                    if (isset($storeData[self::HINT])) {
                        $newData[Type\Number::PLACEHOLDER] = $storeData[self::HINT];
                    }
                    if (isset($value['number_min'])) {
                        $newData[Type\Number::MIN] = (int)$value['number_min'];
                    }
                    if (isset($value['number_max'])) {
                        $newData[Type\Number::MAX] = (int)$value['number_max'];
                    }
                    break;
                }
                case 'url':
                {
                    if (isset($storeData[self::HINT])) {
                        $newData[Type\Url::PLACEHOLDER] = $storeData[self::HINT];
                    }
                    if (isset($value['text_url'])) {
                        $newData[Type\Url::TEXT] = $value['text_url'];
                    }
                    break;
                }
                case 'password':
                {
                    if (isset($storeData[self::HINT])) {
                        $newData[Type\Password::PLACEHOLDER] = $storeData[self::HINT];
                    }
                    break;
                }
                case 'autocomplete':
                {
                    if (isset($value['autocomplete_choices'])) {
                        $newData[Type\Autocomplete::TEXT] = $value['autocomplete_choices'];
                    }
                    break;
                }
                case 'textarea':
                {
                    if (isset($storeData[self::HINT])) {
                        $newData[Type\Textarea::PLACEHOLDER] = $storeData[self::HINT];
                    }
                    if (isset($value['textarea'])) {
                        $newData[Type\Textarea::TEXT] = $value['textarea'];
                    }
                    break;
                }
                case 'colorpicker':
                {
                    if (isset($storeData[self::HINT])) {
                        $newData[Type\Colorpicker::PLACEHOLDER] = $storeData[self::HINT];
                    }
                    break;
                }
                case 'select':
                {
                    if (isset($value['options'])) {
                        $newData[Type\Select::OPTIONS] = $value['options'];
                    }
                    if (isset($value['multiselect'])) {
                        $newData[Type\Select::IS_MULTISELECT] = (string)(int)$value['multiselect'];
                    }
                    break;
                }
                case 'select_radio':
                {
                    if (isset($value['options_radio'])) {
                        $newData[Type\SelectRadio::OPTIONS] = $value['options_radio'];
                    }
                    if (isset($storeData[self::INLINE_ELEMENTS])) {
                        $newData[Type\SelectRadio::IS_INTERNAL_ELEMENTS_INLINE] = $storeData[self::INLINE_ELEMENTS];
                    }
                    break;
                }
                case 'select_checkbox':
                {
                    if (isset($value['options_checkbox'])) {
                        $newData[Type\SelectCheckbox::OPTIONS] = $value['options_checkbox'];
                    }
                    if (isset($value['options_checkbox_min'])) {
                        $newData[Type\SelectCheckbox::MIN_OPTIONS] = (int)$value['options_checkbox_min'];
                    }
                    if (isset($value['options_checkbox_max'])) {
                        $newData[Type\SelectCheckbox::MAX_OPTIONS] = (int)$value['options_checkbox_max'];
                    }
                    if (isset($value['options_checkbox_min_error_text'])) {
                        $newData[Type\SelectCheckbox::MIN_OPTIONS_ERROR_TEXT] = $value['options_checkbox_min_error_text'];
                    }
                    if (isset($value['options_checkbox_max_error_text'])) {
                        $newData[Type\SelectCheckbox::MAX_OPTIONS_ERROR_TEXT] = $value['options_checkbox_max_error_text'];
                    }
                    if (isset($storeData[self::INLINE_ELEMENTS])) {
                        $newData[Type\SelectCheckbox::IS_INTERNAL_ELEMENTS_INLINE] = $storeData[self::INLINE_ELEMENTS];
                    }
                    break;
                }
                case 'select_contact':
                {
                    if (isset($value['options_contact'])) {
                        $newData[Type\SelectContact::OPTIONS] = $value['options_contact'];
                    }
                    break;
                }
                case 'country':
                {
                    if (isset($value['default_country'])) {
                        $newData[Type\Country::DEFAULT_COUNTRY] = $value['default_country'];
                    }
                    break;
                }
                case 'region':
                {
                    if (isset($value['region_country_field_id'])) {
                        $newData[Type\Region::COUNTRY_FIELD_ID] = $value['region_country_field_id'];
                    }
                    break;
                }
                case 'subscribe':
                {
                    if (isset($value['newsletter_label'])) {
                        $newData[Type\Subscribe::TEXT] = $value['newsletter_label'];
                    }
                    break;
                }
                case 'date':
                {
                    if (isset($storeData[self::HINT])) {
                        $newData[Type\Date::PLACEHOLDER] = $storeData[self::HINT];
                    }
                    break;
                }
                case 'date_dob':
                {
                    if (isset($storeData[self::HINT])) {
                        $newData[Type\Dob::PLACEHOLDER] = $storeData[self::HINT];
                    }
                    if (isset($value['dob_customer'])) {
                        $newData[Type\Dob::IS_FILLED_BY_CUSTOMER_DOB] = (string)(int)$value['dob_customer'];
                    }
                    break;
                }
                case 'stars':
                {
                    if (isset($value['stars_init'])) {
                        $newData[Type\Stars::INIT_STARS] = (int)$value['stars_init'];
                    }
                    if (isset($value['stars_max'])) {
                        $newData[Type\Stars::MAX_STARS] = (int)$value['stars_max'];
                    }
                    break;
                }
                case 'file':
                {
                    if (isset($value['dropzone'])) {
                        $newData[Type\File::IS_DROPZONE] = (string)(int)$value['dropzone'];
                    }
                    if (isset($value['dropzone_text'])) {
                        $newData[Type\File::DROPZONE_TEXT] = $value['dropzone_text'];
                    }
                    if (isset($value['dropzone_maxfiles'])) {
                        $newData[Type\File::DROPZONE_MAX_FILES] = (int)$value['dropzone_maxfiles'];
                    }
                    if (isset($value['allowed_extensions'])) {
                        $newData[Type\File::ALLOWED_EXTENSIONS] = $value['allowed_extensions'];
                    }
                    break;
                }
                case 'image':
                {
                    if (isset($value['dropzone_image'])) {
                        $newData[Type\Image::IS_DROPZONE] = (string)(int)$value['dropzone_image'];
                    }
                    if (isset($value['dropzone_text_image'])) {
                        $newData[Type\Image::DROPZONE_TEXT] = $value['dropzone_text_image'];
                    }
                    if (isset($value['dropzone_maxfiles_image'])) {
                        $newData[Type\Image::DROPZONE_MAX_FILES] = (int)$value['dropzone_maxfiles_image'];
                    }
                    if (isset($value['image_resize'])) {
                        $newData[Type\Image::IS_RESIZED] = (string)(int)$value['image_resize'];
                    }
                    if (isset($value['image_resize_width'])) {
                        $newData[Type\Image::RESIZE_WIDTH] = (int)$value['image_resize_width'];
                    }
                    if (isset($value['image_resize_height'])) {
                        $newData[Type\Image::RESIZE_HEIGHT] = (int)$value['image_resize_height'];
                    }
                    break;
                }
                case 'html':
                {
                    if (isset($value['html'])) {
                        $newData[Type\Html::HTML] = $value['html'];
                    }
                    break;
                }
                case 'hidden':
                {
                    if (isset($value['hidden'])) {
                        $newData[Type\Hidden::TEXT] = $value['hidden'];
                    }
                    break;
                }
                default:
                {
                    break;
                }
            }
        }
        return $newData;
    }
}
