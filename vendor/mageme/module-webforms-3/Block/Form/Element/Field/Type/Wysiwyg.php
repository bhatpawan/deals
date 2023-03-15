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


use MageMe\Core\Helper\ConfigHelper;
use MageMe\WebForms\Block\Form\Element\Field\AbstractField;
use MageMe\WebForms\Block\Form\Element\Field\Tooltip;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 *
 */
class Wysiwyg extends AbstractField
{
    /**
     *
     */
    private const TINYMCE_OLD = 'tinymce';
    /**
     *
     */
    private const TINYMCE = 'mage/adminhtml/wysiwyg/tiny_mce/setup';

    /**
     * Block's template
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'wysiwyg.phtml';

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * Wysiwyg constructor.
     * @param TranslationHelper $translationHelper
     * @param Tooltip $tooltipBlock
     * @param ConfigHelper $configHelper
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        TranslationHelper $translationHelper,
        Tooltip           $tooltipBlock,
        ConfigHelper      $configHelper,
        Registry          $registry,
        Context           $context,
        array             $data = []
    )
    {
        parent::__construct($translationHelper, $tooltipBlock, $registry, $context, $data);
        $this->configHelper = $configHelper;
    }

    /**
     * @inheritDoc
     */
    public function getFieldClass(): string
    {
        return 'mceEditor validate-hidden ' . parent::getFieldClass();
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function toHtml(): string
    {
        $html = parent::toHtml();

        // activate tinyMCE
        $require_tinymce = version_compare($this->configHelper->getMagentoVersion(), '2.3', '<') ?
            self::TINYMCE_OLD :
            self::TINYMCE;

        /** @var Template $tiny_mce */
        $tiny_mce = $this->getLayout()->createBlock('Magento\Framework\View\Element\Template',
            null, [
                'data' => [
                    'require_tinymce' => $require_tinymce,
                    'field_uid' => $this->getFieldId()
                ]
            ])
            ->setTemplate('MageMe_WebForms::form/element/field/type/wysiwyg/tiny_mce.phtml');
        $html     .= $tiny_mce->toHtml();

        return $html;
    }
}
