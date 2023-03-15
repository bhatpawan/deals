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


/**
 * Fieldset interface.
 * @api
 * @since 3.0.0
 */
interface FieldsetInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    /** Information */
    const ID = 'fieldset_id';
    const FORM_ID = 'form_id';
    const NAME = 'name';
    const POSITION = 'position';
    const IS_ACTIVE = 'is_active';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /** Design */
    const IS_LABEL_HIDDEN = 'is_label_hidden';
    const WIDTH_PROPORTION_LG = 'width_proportion_lg';
    const WIDTH_PROPORTION_MD = 'width_proportion_md';
    const WIDTH_PROPORTION_SM = 'width_proportion_sm';
    const IS_DISPLAYED_IN_NEW_ROW_LG = 'is_displayed_in_new_row_lg';
    const IS_DISPLAYED_IN_NEW_ROW_MD = 'is_displayed_in_new_row_md';
    const IS_DISPLAYED_IN_NEW_ROW_SM = 'is_displayed_in_new_row_sm';

    /** CSS */
    const CSS_CLASS = 'css_class';
    const CSS_STYLE = 'css_style';

    /** Results / Notifications Settings */
    const IS_NAME_DISPLAYED_IN_RESULT = 'is_name_displayed_in_result';
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
     * Get webform ID
     *
     * @return int|null
     */
    public function getFormId(): ?int;

    /**
     * Set webform ID
     *
     * @param int $formId
     * @return $this
     */
    public function setFormId(int $formId): FieldsetInterface;

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
    public function setName(string $name): FieldsetInterface;

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
    public function setPosition(?int $position): FieldsetInterface;

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
    public function setIsActive(bool $isActive): FieldsetInterface;

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
    public function setCreatedAt(?string $createdAt): FieldsetInterface;

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
    public function setUpdatedAt(?string $updatedAt): FieldsetInterface;
    #endregion

    #region Design
    /**
     * Get if fieldset's label is hidden
     *
     * @return bool
     */
    public function getIsLabelHidden(): bool;

    /**
     * Set if fieldset's label is hidden
     *
     * @param bool $isLabelHidden
     * @return $this
     */
    public function setIsLabelHidden(bool $isLabelHidden): FieldsetInterface;

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
    public function setWidthProportionLg(?string $widthProportionLg): FieldsetInterface;

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
    public function setWidthProportionMd(?string $widthProportionMd): FieldsetInterface;

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
    public function setWidthProportionSm(?string $widthProportionSm): FieldsetInterface;

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
    public function setIsDisplayedInNewRowLg(bool $isDisplayedInNewRowLg): FieldsetInterface;

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
    public function setIsDisplayedInNewRowMd(bool $isDisplayedInNewRowMd): FieldsetInterface;

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
    public function setIsDisplayedInNewRowSm(bool $isDisplayedInNewRowSm): FieldsetInterface;
    #endregion

    #region CSS
    /**
     * Get CSS class for input
     *
     * @return string|null
     */
    public function getCssClass(): ?string;

    /**
     * Set CSS class for input
     *
     * @param string|null $cssClass
     * @return $this
     */
    public function setCssClass(?string $cssClass): FieldsetInterface;

    /**
     * Get CSS style for input
     *
     * @return string|null
     */
    public function getCssStyle(): ?string;

    /**
     * Set CSS style for input
     *
     * @param string|null $cssStyle
     * @return $this
     */
    public function setCssStyle(?string $cssStyle): FieldsetInterface;
    #endregion

    #region Results / Notifications Settings
    /**
     * Get result display
     *
     * @return bool
     */
    public function getIsNameDisplayedInResult(): bool;

    /**
     * Set result display
     *
     * @param bool|null $isNameDisplayedInResult
     * @return $this
     */
    public function setIsNameDisplayedInResult(?bool $isNameDisplayedInResult): FieldsetInterface;
    #endregion

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
     * Clone fieldset
     *
     * @return $this
     */
    public function duplicate(): FieldsetInterface;

    /**
     * Clone fieldset with new parameters
     *
     * @param array $parameters
     * @return $this
     */
    public function clone(array $parameters = []): FieldsetInterface;

    /**
     * Get fields
     *
     * @return FieldInterface[]
     */
    public function getFields(): array;

}
