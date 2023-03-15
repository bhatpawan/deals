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

class Gallery extends AbstractField
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'gallery.phtml';

    /**
     * @inheritDoc
     */
    public function getFieldName(): string
    {
        $name = parent::getFieldName();
        if ($this->getIsMultiselect()) {
            $name .= '[]';
        }
        return $name;
    }

    /**
     * @return bool
     */
    public function getIsMultiselect(): bool
    {
        return $this->field->getIsMultiselect();
    }

    /**
     * @inheritDoc
     */
    public function getFieldClass(): string
    {
        return 'validate-hidden webforms-gallery ' . parent::getFieldClass();
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->field->getImages(true);
    }

    /**
     * @return int
     */
    public function getImagesWidth(): int
    {
        return $this->field->getImagesWidth();
    }

    /**
     * @return int
     */
    public function getImagesHeight(): int
    {
        return $this->field->getImagesHeight();
    }

    /**
     * @return bool
     */
    public function getIsLabeled(): bool
    {
        return $this->field->getIsLabeled();
    }
}
