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

namespace MageMe\WebForms\Model\Field\Type;


use MageMe\WebForms\Api\Ui\FieldUiInterface;
use MageMe\WebForms\Api\Utility\Field\FieldBlockInterface;
use MageMe\WebForms\Helper\ColorHelper;
use MageMe\WebForms\Model\Field\Context;

class Colorpicker extends Text
{
    const SWATCH_SIZE = '15px';

    /**
     * @var ColorHelper
     */
    protected $colorHelper;

    /**
     * Colorpicker constructor.
     * @param ColorHelper $colorHelper
     * @param Context $context
     * @param FieldUiInterface $fieldUi
     * @param FieldBlockInterface $fieldBlock
     */
    public function __construct(
        ColorHelper         $colorHelper,
        Context             $context,
        FieldUiInterface    $fieldUi,
        FieldBlockInterface $fieldBlock
    )
    {
        parent::__construct($context, $fieldUi, $fieldBlock);
        $this->colorHelper = $colorHelper;
    }

    /**
     * @inheritdoc
     */
    public function getValueForResultHtml($value, array $options = [])
    {
        $value = htmlentities((string)$value);
        $swatch = $this->colorHelper->isHexColor($value) ?
            '<div style="display:inline;float:left;height:' . static::SWATCH_SIZE . '; width:' . static::SWATCH_SIZE . '; background:' . $value . '"></div>&nbsp;' :
            '';

        return $swatch . $value;
    }

    /**
     * @param string $value
     * @param array $options
     * @return string
     */
    public function getValueForResultDefaultTemplate(
        string $value,
        array  $options = []
    ): string
    {
        return $this->getValueForResultHtml($value, $options);
    }
}
