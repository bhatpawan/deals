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


class Stars extends Select
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'stars.phtml';

    /**
     * @inheritDoc
     */
    public function getFieldId(): string
    {
        return parent::getFieldId() . 'container';
    }

    /**
     * @inheritDoc
     */
    public function getFieldClass(): string
    {
        return 'br-rating validate-hidden ' . parent::getFieldClass();
    }

    /**
     * @return int
     */
    public function getInitStars(): int
    {
        return $this->field->getInitStars();
    }

    /**
     * @return int
     */
    public function getMaxStars(): int
    {
        return $this->field->getMaxStars();
    }
}
