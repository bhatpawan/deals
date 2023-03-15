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

namespace MageMe\WebForms\Block\Form\Element\Field;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Block\Form\Element\AbstractElement;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

abstract class AbstractField extends AbstractElement implements FieldBlockInterface
{
    const TEMPLATE_PATH = 'form/element/field/type/';

    /** @var  FieldInterface */
    protected $field;

    /** @var Registry */
    protected $registry;

    /** @var array */
    protected $validation;

    /**
     * @var Tooltip
     */
    protected $tooltipBlock;

    /**
     * AbstractField constructor.
     * @param TranslationHelper $translationHelper
     * @param Tooltip $tooltipBlock
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        TranslationHelper $translationHelper,
        Tooltip           $tooltipBlock,
        Registry          $registry,
        Context           $context,
        array             $data = []
    )
    {
        parent::__construct($translationHelper, $context, $data);
        $this->registry     = $registry;
        $this->tooltipBlock = $tooltipBlock;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function registry(string $key = '')
    {
        return $this->registry->registry($key);
    }

    /**
     * @inheritDoc
     */
    public function getField(): FieldInterface
    {
        return $this->field;
    }

    /**
     * @param FieldInterface $field
     * @return FieldBlockInterface
     */
    public function setField(FieldInterface $field): FieldBlockInterface
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        if (!isset($this->validation)) {
            $this->validation = $this->field->getValidation();
        }
        return $this->field->getValidation()['rules'];
    }

    /**
     * @return array
     */
    public function getValidationDescriptions(): array
    {
        if (!isset($this->validation)) {
            $this->validation = $this->field->getValidation();
        }
        return $this->field->getValidation()['descriptions'];
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return "field[" . $this->field->getId() . "]";
    }

    /**
     * @return string
     */
    public function getFieldId(): string
    {
        return "field" . $this->getFieldUid();
    }

    /**
     * @return string
     * @noinspection PhpUndefinedMethodInspection
     */
    public function getFieldUid(): string
    {
        return $this->field->getUid() . $this->field->getId();
    }

    /**
     * @return array|string
     */
    public function getFieldValue()
    {
        return $this->field->getFilteredFieldValue() ?? '';
    }

    /**
     * @return string
     */
    public function getFieldClass(): string
    {
        return $this->field->getCssInputClass() ?? '';
    }

    /**
     * @return string
     */
    public function getFieldStyle(): string
    {
        return $this->field->getCssInputStyle() ?? '';
    }

    /**
     * @return string
     */
    public function getCustomAttributes(): string
    {
        return $this->field->getCustomAttributes() ?? '';
    }

    /**
     * @return bool
     */
    public function getIsLabelHidden(): bool
    {
        return $this->field->getIsLabelHidden();
    }

    /**
     * @return string|null
     */
    public function getFieldLabel(): ?string
    {
        return $this->applyTranslation($this->field->getName());
    }

    /**
     * @return bool
     */
    public function getIsRequired(): bool
    {
        return $this->field->getIsRequired();
    }

    /**
     * @return string|null
     */
    public function getAutocomplete(): ?string
    {
        return $this->field->getBrowserAutocomplete();
    }

    /**
     * @return Tooltip
     */
    public function getTooltipBlock(): Tooltip
    {
        return $this->tooltipBlock->setField($this->field)->setHtmlId('');
    }
}
