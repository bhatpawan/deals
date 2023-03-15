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
use MageMe\WebForms\Block\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;

/**
 *
 */
class Tooltip extends AbstractElement
{

    /**
     * @var FieldInterface
     */
    protected $field;

    /**
     * @var string
     */
    protected $tooltip;

    /**
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'field/tooltip.phtml';

    /**
     * @var string
     */
    protected $htmlId;

    /**
     * @return FieldInterface
     */
    public function getField(): FieldInterface
    {
        return $this->field;
    }

    /**
     * @param FieldInterface $field
     * @return $this
     */
    public function setField(FieldInterface $field): Tooltip
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getTooltip(): string
    {
        return $this->applyTranslation($this->tooltip);
    }

    /**
     * @param $tooltip
     * @return $this
     */
    public function setTooltip($tooltip): Tooltip
    {
        $subject = preg_replace('/\s+/', ' ', (string)$tooltip);
        if (is_string($subject)) {
            $subject = trim($subject);
        }
        $this->tooltip = str_replace("'", "\'", $subject);
        return $this;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getHtmlId(): string
    {
        if ($this->htmlId) {
            return $this->htmlId;
        } else {
            $this->htmlId = 'tooltip' . Random::getRandomNumber(6);
        }
        return $this->htmlId;
    }

    public function setHtmlId(string $htmlId): self
    {
        $this->htmlId = $htmlId;
        return $this;
    }
}
