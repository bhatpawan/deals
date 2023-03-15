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

namespace MageMe\WebForms\Block\Widget;

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 *
 */
class Button extends Template implements BlockInterface
{
    const FORM_ID = 'form_id';
    const BUTTON_TEXT = 'button_text';
    const BUTTON_CSS_CLASS = 'button_css_class';
    const POPUP_TITLE = 'popup_title';
    const POPUP_CSS_CLASS = 'popup_css_class';
    const CONTAINER_ID = 'container_id';

    /**
     * @var Random
     */
    protected $random;
    /**
     * @var string
     */
    protected $containerId;
    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    /**
     * @param TranslationHelper $translationHelper
     * @param Template\Context $context
     * @param Random $random
     * @param array $data
     */
    public function __construct(
        TranslationHelper $translationHelper,
        Template\Context  $context,
        Random            $random,
        array             $data = []
    )
    {
        parent::__construct($context, $data);

        $this->random            = $random;
        $this->translationHelper = $translationHelper;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getContainerId(): string
    {
        if (!$this->containerId) {
            $this->containerId = $this->random->getRandomString(10);
        }
        return $this->containerId;
    }

    /**
     * @return array|mixed|null
     */
    public function getFormId()
    {
        return $this->getData(self::FORM_ID);
    }

    /**
     * @return string|null
     */
    public function getPopupTitle(): ?string
    {
        return $this->applyTranslation($this->getData(self::POPUP_TITLE));
    }

    /**
     * @return string|null
     */
    public function getButtonText(): ?string
    {
        return $this->applyTranslation($this->getData(self::BUTTON_TEXT));
    }

    /**
     * @param string|null $str
     * @return string
     */
    public function applyTranslation(?string $str): ?string
    {
        return $this->translationHelper->applyTranslation($str);
    }

    /**
     * @return array|mixed|null
     */
    public function getButtonCssClass()
    {
        return $this->getData(self::BUTTON_CSS_CLASS);
    }

    /**
     * @return array|mixed|null
     */
    public function getPopupCssClass()
    {
        return $this->getData(self::POPUP_CSS_CLASS);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAjaxUrl(): string
    {
        return $this->_urlBuilder->getUrl('webforms/form/popup', [
            self::CONTAINER_ID => $this->getContainerId(),
            self::POPUP_CSS_CLASS => $this->getPopupCssClass(),
            self::POPUP_TITLE => $this->getPopupTitle(),
            FormInterface::ID => $this->getFormId()
        ]);
    }

    /**
     * @return string
     */
    protected function _toHtml(): string
    {
        $this->compatibilityPatch();
        return parent::_toHtml();
    }

    /**
     * Compatibility patch
     */
    protected function compatibilityPatch()
    {
        $template = str_replace('webforms/', '', $this->getTemplate(), $replaced);
        $changed  = (bool)$replaced;
        $template = str_replace('VladimirPopov', 'MageMe', $template, $replaced);
        $changed  = $changed || $replaced;
        if ($changed) {
            $this->setTemplate($template);
        }
    }
}
