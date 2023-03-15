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

namespace MageMe\WebForms\Block\Form\Element;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Block\Form\Element\Field\Tooltip;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Framework\View\Element\Template;

class Field extends AbstractElement
{
    /**
     * @var FieldInterface
     */
    protected $field;

    /**
     * @var Tooltip
     */
    protected $tooltipBlock;

    /**
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'field.phtml';

    /**
     * @param TranslationHelper $translationHelper
     * @param Tooltip $tooltipBlock
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        TranslationHelper $translationHelper,
        Tooltip           $tooltipBlock,
        Template\Context  $context,
        array             $data = []
    )
    {
        parent::__construct($translationHelper, $context, $data);
        $this->tooltipBlock = $tooltipBlock;
    }

    /**
     * @return Tooltip
     */
    public function getTooltipBlock(): Tooltip
    {
        return $this->tooltipBlock->setField($this->field)->setTooltip($this->field->getTooltip())->setHtmlId('');
    }

    /**
     * @return FieldInterface
     */
    public function getField(): FieldInterface
    {
        return $this->field;
    }

    /**
     * @param FieldInterface $field
     * @return Field
     */
    public function setField(FieldInterface $field): Field
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldUid(): string
    {
        if ($this->field)
            return $this->getUid() . $this->field->getId();
        return '';
    }
}