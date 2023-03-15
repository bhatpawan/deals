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

namespace MageMe\WebForms\Api\Data;


use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Model\Result;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\Template\Simple;

/**
 * Field interface.
 * @api
 * @since 3.0.0
 */
interface FieldInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    /** Information */
    const ID = 'field_id';
    const FORM_ID = 'form_id';
    const FIELDSET_ID = 'fieldset_id';
    const NAME = 'name';
    const TYPE = 'type';
    const CODE = 'code';
    const RESULT_LABEL = 'result_label';
    const COMMENT = 'comment';
    const TYPE_ATTRIBUTES_SERIALIZED = 'type_attributes_serialized';
    const IS_EMAIL_SUBJECT = 'is_email_subject';
    const IS_REQUIRED = 'is_required';
    const VALIDATION_REQUIRED_MESSAGE = 'validation_required_message';
    const POSITION = 'position';
    const IS_ACTIVE = 'is_active';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /** Design */
    const IS_LABEL_HIDDEN = 'is_label_hidden';
    const CUSTOM_ATTRIBUTES = 'custom_attributes';
    const WIDTH_PROPORTION_LG = 'width_proportion_lg';
    const WIDTH_PROPORTION_MD = 'width_proportion_md';
    const WIDTH_PROPORTION_SM = 'width_proportion_sm';
    const IS_DISPLAYED_IN_NEW_ROW_LG = 'is_displayed_in_new_row_lg';
    const IS_DISPLAYED_IN_NEW_ROW_MD = 'is_displayed_in_new_row_md';
    const IS_DISPLAYED_IN_NEW_ROW_SM = 'is_displayed_in_new_row_sm';
    const CSS_CONTAINER_CLASS = 'css_container_class';
    const CSS_INPUT_CLASS = 'css_input_class';
    const CSS_INPUT_STYLE = 'css_input_style';
    const DISPLAY_IN_RESULT = 'display_in_result';
    const BROWSER_AUTOCOMPLETE = 'browser_autocomplete';

    /** Validation */
    const IS_UNIQUE = 'is_unique';
    const UNIQUE_VALIDATION_MESSAGE = 'unique_validation_message';
    const MIN_LENGTH = 'min_length';
    const MIN_LENGTH_VALIDATION_MESSAGE = 'min_length_validation_message';
    const MAX_LENGTH = 'max_length';
    const MAX_LENGTH_VALIDATION_MESSAGE = 'max_length_validation_message';
    const REGEX_VALIDATION_PATTERN = 'regex_validation_pattern';
    const REGEX_VALIDATION_MESSAGE = 'regex_validation_message';
    /**#@-*/

    /**#@+
     * Additional constants for keys of data array.
     */
    const IS_LOGIC_TYPE = 'is_logic_type';
    /**#@-*/

    #region Information
    /**
     * Get ID
     *
     * @return mixed
     */
    public function getId();

    /**
     * Set ID
     *
     * @param mixed $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get form ID
     *
     * @return int|null
     */
    public function getFormId(): ?int;

    /**
     * Set form ID
     *
     * @param int $formId
     * @return $this
     */
    public function setFormId(int $formId): FieldInterface;

    /**
     * Get fieldset ID
     *
     * @return int|null
     */
    public function getFieldsetId(): ?int;

    /**
     * Set fieldset ID
     *
     * @param int|null $fieldsetId
     * @return $this
     */
    public function setFieldsetId(?int $fieldsetId): FieldInterface;

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): FieldInterface;

    /**
     * Get type
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType(string $type): FieldInterface;

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * Set code
     *
     * @param string|null $code
     * @return $this
     */
    public function setCode(?string $code): FieldInterface;

    /**
     * Get result label
     *
     * @return string|null
     */
    public function getResultLabel(): ?string;

    /**
     * Set result label
     *
     * @param string|null $resultLabel
     * @return $this
     */
    public function setResultLabel(?string $resultLabel): FieldInterface;

    /**
     * Get comment
     *
     * @return string|null
     */
    public function getComment(): ?string;

    /**
     * Set comment
     *
     * @param string|null $comment
     * @return $this
     */
    public function setComment(?string $comment): FieldInterface;

    /**
     * Get type attributes serialized
     *
     * @return string|null
     */
    public function getTypeAttributesSerialized(): ?string;

    /**
     * Set type attributes serialized
     *
     * @param string|null $typeAttributesSerialized
     * @return $this
     */
    public function setTypeAttributesSerialized(?string $typeAttributesSerialized): FieldInterface;

    /**
     * Get if field used as email subject
     *
     * @return bool
     */
    public function getIsEmailSubject(): bool;

    /**
     * Set if field used as email subject
     *
     * @param bool $isEmailSubject
     * @return $this
     */
    public function setIsEmailSubject(bool $isEmailSubject): FieldInterface;

    /**
     * Get if field is required
     *
     * @return bool
     */
    public function getIsRequired(): bool;

    /**
     * Set if field is required
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequired(bool $isRequired): FieldInterface;

    /**
     * Get custom validation error message if field is required
     *
     * @return string|null
     */
    public function getValidationRequiredMessage(): ?string;

    /**
     * Set custom validation error message if field is required
     *
     * @param string|null $validationRequiredMessage
     * @return $this
     */
    public function setValidationRequiredMessage(?string $validationRequiredMessage): FieldInterface;

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition(): ?int;

    /**
     * Set position
     *
     * @param int|null $position
     * @return $this
     */
    public function setPosition(?int $position): FieldInterface;

    /**
     * Set if field is active
     *
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * Set if field is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): FieldInterface;

    /**
     * Get created time
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created time
     *
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?string $createdAt): FieldInterface;

    /**
     * Get last update time
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set last update time
     *
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?string $updatedAt): FieldInterface;
    #endregion

    #region Design
    /**
     * Get if field's label is hidden
     *
     * @return bool
     */
    public function getIsLabelHidden(): bool;

    /**
     * Set if field's label is hidden
     *
     * @param bool $isLabelHidden
     * @return $this
     */
    public function setIsLabelHidden(bool $isLabelHidden): FieldInterface;

    /**
     * Get custom attributes
     *
     * @return string|null
     */
    public function getCustomAttributes(): ?string;

    /**
     * Set custom attributes
     *
     * @param string|null $customAttributes
     * @return $this
     */
    public function setCustomAttributes(?string $customAttributes): FieldInterface;

    /**
     * Get proportion of the fieldset width for large size screen devices
     * such as PC, Macbook, iMac etc.
     *
     * @return string|null
     */
    public function getWidthProportionLg(): ?string;

    /**
     * Set proportion of the fieldset width for large size screen devices
     * such as PC, Macbook, iMac etc.
     *
     * @param string|null $widthProportionLg
     * @return $this
     */
    public function setWidthProportionLg(?string $widthProportionLg): FieldInterface;

    /**
     * Get proportion of the fieldset width for medium size screen devices
     * such as iPad, Galaxy Tab, Surface etc.
     *
     * @return string|null
     */
    public function getWidthProportionMd(): ?string;

    /**
     * Set proportion of the fieldset width for medium size screen devices
     * such as iPad, Galaxy Tab, Surface etc.
     *
     * @param string|null $widthProportionMd
     * @return $this
     */
    public function setWidthProportionMd(?string $widthProportionMd): FieldInterface;

    /**
     * Get proportion of the fieldset width for small size screen devices
     * such as iPhone, Galaxy, Pixel etc.
     *
     * @return string|null
     */
    public function getWidthProportionSm(): ?string;

    /**
     * Set proportion of the fieldset width for small size screen devices
     * such as iPhone, Galaxy, Pixel etc.
     *
     * @param string|null $widthProportionSm
     * @return $this
     */
    public function setWidthProportionSm(?string $widthProportionSm): FieldInterface;

    /**
     * Set if field displayed in a new row on large screen
     *
     * @return bool
     */
    public function getIsDisplayedInNewRowLg(): bool;

    /**
     * Set if field displayed in a new row on large screen
     *
     * @param bool $isDisplayedInNewRowLg
     * @return $this
     */
    public function setIsDisplayedInNewRowLg(bool $isDisplayedInNewRowLg): FieldInterface;

    /**
     * Set if field displayed in a new row on medium screen
     *
     * @return bool
     */
    public function getIsDisplayedInNewRowMd(): bool;

    /**
     * Set if field displayed in a new row on medium screen
     *
     * @param bool $isDisplayedInNewRowMd
     * @return $this
     */
    public function setIsDisplayedInNewRowMd(bool $isDisplayedInNewRowMd): FieldInterface;

    /**
     * Set if field displayed in a new row on small screen
     *
     * @return bool
     */
    public function getIsDisplayedInNewRowSm(): bool;

    /**
     * Set if field displayed in a new row on small screen
     *
     * @param bool $isDisplayedInNewRowSm
     * @return $this
     */
    public function setIsDisplayedInNewRowSm(bool $isDisplayedInNewRowSm): FieldInterface;

    /**
     * Get CSS class for container
     *
     * @return string|null
     */
    public function getCssContainerClass(): ?string;

    /**
     * Set CSS class for container
     *
     * @param string|null $cssContainerClass
     * @return $this
     */
    public function setCssContainerClass(?string $cssContainerClass): FieldInterface;

    /**
     * Get CSS class for input
     *
     * @return string|null
     */
    public function getCssInputClass(): ?string;

    /**
     * Set CSS class for input
     *
     * @param string|null $cssInputClass
     * @return $this
     */
    public function setCssInputClass(?string $cssInputClass): FieldInterface;

    /**
     * Get CSS style for input
     *
     * @return string|null
     */
    public function getCssInputStyle(): ?string;

    /**
     * Set CSS style for input
     *
     * @param string|null $cssInputStyle
     * @return $this
     */
    public function setCssInputStyle(?string $cssInputStyle): FieldInterface;

    /**
     * Get result display
     *
     * @return string
     */
    public function getDisplayInResult(): string;

    /**
     * Set result display
     *
     * @param string $value
     * @return $this
     */
    public function setDisplayInResult(string $value): FieldInterface;

    /**
     * Get browser autocomplete
     *
     * @return string|null
     */
    public function getBrowserAutocomplete(): ?string;

    /**
     * Set browser autocomplete
     *
     * @param string|null $browserAutocomplete
     * @return $this
     */
    public function setBrowserAutocomplete(?string $browserAutocomplete): FieldInterface;
    #endregion

    #region Validation
    /**
     * Set if field is unique
     *
     * @return bool
     */
    public function getIsUnique(): bool;

    /**
     * Set if field is unique
     *
     * @param bool $isUnique
     * @return $this
     */
    public function setIsUnique(bool $isUnique): FieldInterface;

    /**
     * Get validation error message if field is unique
     *
     * @return string|null
     */
    public function getUniqueValidationMessage(): ?string;

    /**
     * Set validation error message if field is unique
     *
     * @param string|null $uniqueValidationMessage
     * @return $this
     */
    public function setUniqueValidationMessage(?string $uniqueValidationMessage): FieldInterface;

    /**
     * Get validate length min
     *
     * @return int|null
     */
    public function getMinLength(): ?int;

    /**
     * Set validate length min
     *
     * @param int|null $minLength
     * @return $this
     */
    public function setMinLength(?int $minLength): FieldInterface;

    /**
     * Get min error length message
     *
     * @return string|null
     */
    public function getMinLengthValidationMessage(): ?string;

    /**
     * Set min error length message
     *
     * @param string|null $minLengthValidationMessage
     * @return $this
     */
    public function setMinLengthValidationMessage(?string $minLengthValidationMessage): FieldInterface;

    /**
     * Get maximum length
     *
     * @return int|null
     */
    public function getMaxLength(): ?int;

    /**
     * Set minimum length
     *
     * @param int|null $maxLength
     * @return $this
     */
    public function setMaxLength(?int $maxLength): FieldInterface;

    /**
     * Get max error length message
     *
     * @return string|null
     */
    public function getMaxLengthValidationMessage(): ?string;

    /**
     * Set max error length message
     *
     * @param string|null $maxLengthValidationMessage
     * @return $this
     */
    public function setMaxLengthValidationMessage(?string $maxLengthValidationMessage): FieldInterface;

    /**
     * Get validation RegExp
     *
     * @return string|null
     */
    public function getRegexValidationPattern(): ?string;

    /**
     * Set validation RegExp
     *
     * @param string|null $regexValidationPattern
     * @return $this
     */
    public function setRegexValidationPattern(?string $regexValidationPattern): FieldInterface;

    /**
     * Get validation error message if regex validation fails
     *
     * @return string|null
     */
    public function getRegexValidationMessage(): ?string;

    /**
     * Set validation error message if regex validation fails
     *
     * @param string|null $regexValidationMessage
     * @return $this
     */
    public function setRegexValidationMessage(?string $regexValidationMessage): FieldInterface;
    #endregion
    #endregion

    /**
     * Get value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set value
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value): FieldInterface;

    /**
     * Get responsive CSS tags
     *
     * @return string
     */
    public function getResponsiveCss(): string;

    /**
     * Get form
     *
     * @return FormInterface
     */
    public function getForm(): FormInterface;

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey(): string;

    /**
     * TODO: comment
     *
     * @return mixed
     */
    public function toHtml();

    /**
     * TODO: comment
     *
     * @return array
     */
    public function getValidation(): array;

    /**
     * Clone field
     *
     * @return $this
     */
    public function duplicate(): FieldInterface;

    /**
     * Clone field with new parameters
     *
     * @param array $parameters
     * @return $this
     */
    public function clone(array $parameters = []): FieldInterface;

    /**
     * TODO: comment
     *
     * @return array
     */
    public function getDisplayOptions(): array;

    /**
     * Get filter for replace {{...}} codes with pre-filled data for registered customer
     *
     * @return Simple
     */
    public function getFilter(): Simple;

    /**
     * TODO: comment
     *
     * @return array|string
     */
    public function getFilteredFieldValue();

    /**
     * TODO: comment
     *
     * @param mixed $value
     * @return mixed
     */
    public function getValueForSubject($value);

    /**
     * TODO: comment
     *
     * @return LogicInterface[]
     * @throws LocalizedException
     */
    public function getLogic(): array;

    /**
     * TODO: comment
     *
     * @return array
     * @throws LocalizedException
     */
    public function getLogicTargetOptionsArray(): array;

    /**
     * TODO: comment
     *
     * @param mixed $option
     * @return mixed
     */
    public function getTooltip($option = false);

    /**
     * Load type attributes as DataObject properties from JSON at database
     *
     * @param string|null $json
     * @return $this
     */
    public function loadTypeAttributesFormJSON(?string $json): FieldInterface;

    /**
     * Get type attributes as JSON for save to database
     *
     * @return string|null
     */
    public function getTypeAttributesAsJSON(): ?string;

    /**
     * Get errors after post validation
     *
     * @param array $postData
     * @param bool $logicVisibility
     * @param array $config
     * @return array
     */
    public function getPostErrors(array $postData, bool $logicVisibility, array $config = []): array;

    /**
     * Return array with type attributes names
     *
     * @return array
     */
    public function getTypeAttributesNames(): array;

    /**
     * Replace codes {{...}} in value with data
     *
     * @param string $value
     * @return string
     */
    public function replaceCodesWithData(string $value): string;

    /**
     * Get field value for template result var
     *
     * @param mixed $value
     * @param int|null $resultId
     * @param array $options
     * @return mixed
     */
    public function getValueForResultTemplate($value, ?int $resultId = null, array $options = []);

    /**
     * Get field value for template result var
     *
     * @param mixed $value
     * @param int|null $resultId
     * @return mixed
     */
    public function getValueForExport($value, ?int $resultId = null);

    /**
     * Get field html for result
     *
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function getValueForResultHtml($value, array $options = []);

    /**
     * Get result value for admin grid
     *
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function getValueForResultAdminhtml($value, array $options = []);

    /**
     * Get result html for admin
     *
     * @param mixed $value
     * @param array $options
     * @return mixed
     */
    public function getValueForResultAdminGrid($value, array $options = []);

    /**
     * Get fixed value for result
     *
     * @param mixed $value
     * @param ResultInterface $result
     * @return mixed
     */
    public function getValueForResultAfterSave($value, ResultInterface $result);

    /**
     * Get field html for value renderer
     *
     * @param DataObject $row
     * @return string
     */
    public function getValueForResultValueRenderer(DataObject $row): string;

    /**
     * Get fixed value for collection filter
     *
     * @param mixed $value
     * @return mixed
     */
    public function getValueForResultCollectionFilter($value);

    /**
     * Get collection filter condition
     *
     * @param mixed $value
     * @param string $prefix
     * @return string
     */
    public function getResultCollectionFilterCondition($value, string $prefix = '%'): string;

    /**
     * Get value for result default frontend template
     *
     * @param string $value
     * @param array $options
     * @return string
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string;

    /**
     * Get "label for" for field at form default template
     *
     * @param string $uid
     * @return string
     */
    public function getLabelForForFormDefaultTemplate(string $uid): string;

    /**
     * Get CSS for field container
     *
     * @return string
     */
    public function getTypeCssForContainer(): string;

    /**
     * Get logic flag
     *
     * @return bool
     */
    public function getIsLogicType(): bool;

    /**
     * Process specific attribute data on field save
     *
     * @param array $data
     * @param int $storeId
     * @return $this
     */
    public function processTypeAttributesOnSave(array &$data, int $storeId): FieldInterface;

    /**
     * Process field type dependent column data
     *
     * @param array $dataSource
     * @return $this
     */
    public function processColumnDataSource(array &$dataSource): FieldInterface;

    /**
     * Process submitted value after result was saved
     *
     * @param ResultInterface|Result $result
     * @return $this
     */
    public function processNewResult(ResultInterface $result): FieldInterface;

    /**
     * Prepare post data before saving result
     *
     * @param array $postData
     * @param array $config
     * @param ResultInterface|null $result
     * @param bool $isAdmin
     * @return $this
     */
    public function preparePostData(
        array &$postData,
        array $config = [],
        ResultInterface $result = null,
        bool $isAdmin = false
    ): FieldInterface;

    /**
     * Process result data after post
     *
     * @param ResultInterface $result
     * @return $this
     */
    public function processPostResult(ResultInterface $result): FieldInterface;

    /**
     * Convert raw value to usable object
     *
     * @param $value
     * @param array $config
     * @return mixed
     */
    public function convertRawValue($value, array $config = []);

    /**
     * Post process field on import
     *
     * @return bool
     */
    public function isImportPostProcess(): bool;

    /**
     * Post process field after import
     *
     * @param array $logicMatrix
     * @return $this
     */
    public function importPostProcess(array $logicMatrix): FieldInterface;

    /**
     * @return FieldUiInterface
     */
    public function getFieldUi(): FieldUiInterface;

    /**
     * Get value from post data
     *
     * @param array $postData
     * @param array $config
     * @param bool $visibility
     * @param bool $emptyFieldArray
     * @return mixed
     */
    public function getPostValue(array $postData, array $config = [], bool $visibility = true, bool $emptyFieldArray = false);
}
