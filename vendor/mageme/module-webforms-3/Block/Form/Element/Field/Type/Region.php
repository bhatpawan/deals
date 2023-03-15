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
use MageMe\WebForms\Block\Form\Element\Field\Tooltip;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Directory\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

class Region extends AbstractField
{
    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'region.phtml';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Region constructor.
     * @param TranslationHelper $translationHelper
     * @param Tooltip $tooltipBlock
     * @param Data $helper
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        TranslationHelper $translationHelper,
        Tooltip           $tooltipBlock,
        Data              $helper,
        Registry          $registry,
        Context           $context,
        array             $data = [])
    {
        parent::__construct($translationHelper, $tooltipBlock, $registry, $context, $data);
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getCountryFieldId(): string
    {
        return "field" . $this->field->getUid() . $this->field->getCountryFieldId();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getRegionJson(): string
    {
        return $this->helper->getRegionJson();
    }
}