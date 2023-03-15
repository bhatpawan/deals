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

namespace MageMe\WebForms\Model\Fieldset;


use MageMe\Core\Helper\DateHelper;
use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\FieldsetInterface;
use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Helper\CssHelper;
use MageMe\WebForms\Model\AbstractModel;
use MageMe\WebForms\Model\FieldsetFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractFieldset extends AbstractModel implements FieldsetInterface
{
    /**
     * Fieldset cache tag
     */
    const CACHE_TAG = 'webforms_fieldset';

    /**
     * @inheritdoc
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FieldsetFactory
     */
    protected $fieldsetFactory;

    /**
     * @var FieldsetRepositoryInterface
     */
    protected $fieldsetRepository;

    /**
     * @var FieldRepositoryInterface
     */
    protected $fieldRepository;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var DateHelper
     */
    protected $dateHelper;
    /**
     * @var CssHelper
     */
    protected $cssHelper;

    /**
     * AbstractFieldset constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct(
            $context->getStoreRepository(),
            $context->getStoreFactory(),
            $context->getContext(),
            $context->getRegistry(),
            $context->getResource(),
            $context->getResourceCollection(),
            $context->getData());
        $this->searchCriteriaBuilder = $context->getSearchCriteriaBuilder();
        $this->fieldsetFactory       = $context->getFieldsetFactory();
        $this->fieldsetRepository    = $context->getFieldsetRepository();
        $this->fieldRepository       = $context->getFieldRepository();
        $this->formRepository        = $context->getFormRepository();
        $this->formKey               = $context->getFormKey();
        $this->scopeConfig           = $context->getScopeConfig();
        $this->dateHelper            = $context->getDateHelper();
        $this->cssHelper             = $context->getCssHelper();
    }

    /**
     * @inheritDoc
     */
    public function getIsLabelHidden(): bool
    {
        return (bool)$this->getData(self::IS_LABEL_HIDDEN);
    }

    /**
     * @inheritDoc
     */
    public function setIsLabelHidden(bool $isLabelHidden): FieldsetInterface
    {
        return $this->setData(self::IS_LABEL_HIDDEN, $isLabelHidden);
    }

    /**
     * @inheritDoc
     */
    public function getResponsiveCss(): string
    {
        return $this->cssHelper->getResponsiveCss($this->getWidthProportionLg(), $this->getWidthProportionMd(), $this->getWidthProportionSm(),
            $this->getIsDisplayedInNewRowLg(), $this->getIsDisplayedInNewRowMd(), $this->getIsDisplayedInNewRowSm());
    }

    /**
     * @inheritDoc
     */
    public function getWidthProportionLg(): ?string
    {
        return $this->getData(self::WIDTH_PROPORTION_LG);
    }

    /**
     * @inheritDoc
     */
    public function getWidthProportionMd(): ?string
    {
        return $this->getData(self::WIDTH_PROPORTION_MD);
    }

#region DB getters and setters

    /**
     * @inheritDoc
     */
    public function getWidthProportionSm(): ?string
    {
        return $this->getData(self::WIDTH_PROPORTION_SM);
    }

    /**
     * @inheritDoc
     */
    public function getIsDisplayedInNewRowLg(): bool
    {
        return (bool)$this->getData(self::IS_DISPLAYED_IN_NEW_ROW_LG);
    }

    /**
     * @inheritDoc
     */
    public function getIsDisplayedInNewRowMd(): bool
    {
        return (bool)$this->getData(self::IS_DISPLAYED_IN_NEW_ROW_MD);
    }

    /**
     * @inheritDoc
     */
    public function getIsDisplayedInNewRowSm(): bool
    {
        return (bool)$this->getData(self::IS_DISPLAYED_IN_NEW_ROW_SM);
    }

    /**
     * get form key
     *
     * @return string
     * @throws LocalizedException
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getId();
    }

    /**
     * @inheritDoc
     */
    public function setFormId(int $formId): FieldsetInterface
    {
        return $this->setData(self::FORM_ID, $formId);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): FieldsetInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getIsNameDisplayedInResult(): bool
    {
        return $this->getData(self::IS_NAME_DISPLAYED_IN_RESULT) ?? true;
    }

    /**
     * @inheritDoc
     */
    public function setIsNameDisplayedInResult(?bool $isNameDisplayedInResult): FieldsetInterface
    {
        return $this->setData(self::IS_NAME_DISPLAYED_IN_RESULT, $isNameDisplayedInResult);
    }

    /**
     * @inheritDoc
     */
    public function getCssClass(): ?string
    {
        return $this->getData(self::CSS_CLASS);
    }

    /**
     * @inheritDoc
     */
    public function setCssClass(?string $cssClass): FieldsetInterface
    {
        return $this->setData(self::CSS_CLASS, $cssClass);
    }

    /**
     * @inheritDoc
     */
    public function getCssStyle(): ?string
    {
        return $this->getData(self::CSS_STYLE);
    }

    /**
     * @inheritDoc
     */
    public function setCssStyle(?string $cssStyle): FieldsetInterface
    {
        return $this->setData(self::CSS_STYLE, $cssStyle);
    }

    /**
     * @inheritDoc
     */
    public function getPosition(): ?int
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @inheritDoc
     */
    public function setPosition(?int $position): FieldsetInterface
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): FieldsetInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(?string $updatedAt): FieldsetInterface
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getIsActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setIsActive(bool $isActive): FieldsetInterface
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritDoc
     */
    public function setWidthProportionLg(?string $widthProportionLg): FieldsetInterface
    {
        return $this->setData(self::WIDTH_PROPORTION_LG, $widthProportionLg);
    }

    /**
     * @inheritDoc
     */
    public function setWidthProportionMd(?string $widthProportionMd): FieldsetInterface
    {
        return $this->setData(self::WIDTH_PROPORTION_MD, $widthProportionMd);
    }

    /**
     * @inheritDoc
     */
    public function setWidthProportionSm(?string $widthProportionSm): FieldsetInterface
    {
        return $this->setData(self::WIDTH_PROPORTION_SM, $widthProportionSm);
    }

    /**
     * @inheritDoc
     */
    public function setIsDisplayedInNewRowLg(bool $isDisplayedInNewRowLg): FieldsetInterface
    {
        return $this->setData(self::IS_DISPLAYED_IN_NEW_ROW_LG, $isDisplayedInNewRowLg);
    }

    /**
     * @inheritDoc
     */
    public function setIsDisplayedInNewRowMd(bool $isDisplayedInNewRowMd): FieldsetInterface
    {
        return $this->setData(self::IS_DISPLAYED_IN_NEW_ROW_MD, $isDisplayedInNewRowMd);
    }

    /**
     * @inheritDoc
     */
    public function setIsDisplayedInNewRowSm(bool $isDisplayedInNewRowSm): FieldsetInterface
    {
        return $this->setData(self::IS_DISPLAYED_IN_NEW_ROW_SM, $isDisplayedInNewRowSm);
    }

    /**
     * @inheritDoc
     * @throws NoSuchEntityException
     */
    public function getForm(): FormInterface
    {
        return $this->formRepository->getById($this->getFormId());
    }

    #endregion

    /**
     * @inheritDoc
     */
    public function getFormId(): ?int
    {
        return $this->getData(self::FORM_ID);
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return $this->fieldRepository->getListByFieldsetId($this->getId(), $this->getStoreId())->getItems();
    }
}
