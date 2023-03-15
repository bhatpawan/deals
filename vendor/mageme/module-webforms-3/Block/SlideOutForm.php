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

namespace MageMe\WebForms\Block;

use MageMe\WebForms\Api\Data\FormInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Widget\Block\BlockInterface;

/**
 *
 */
class SlideOutForm extends Form implements BlockInterface
{
    /**
     *
     */
    const SLIDE_OUT_TEMPLATE = 'form/slide_out.phtml';

    const DEFAULT_FORM_WIDTH = 350;
    const DEFAULT_BUTTON_WIDTH = 25;
    const IS_SLIDE_OUT = 'is_slide_out';

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFormData(): array
    {

        $formData[FormInterface::ID] = $this->getData(FormInterface::ID) ?: $this->_scopeConfig->getValue('webforms/slideout/webform',
            $this->getScope());
        $this->setData(self::IS_SLIDE_OUT, true);
        return $formData;
    }

    /**
     * @return string
     */
    public function getSliderPosition(): string
    {
        return $this->getData('slider_position') ?: $this->_scopeConfig->getValue('webforms/slideout/slider_position',
            $this->getScope());
    }

    /**
     * @return string
     */
    public function getButtonText(): string
    {
        return $this->applyTranslation(
            $this->getData('button_text') ?:
                $this->_scopeConfig->getValue('webforms/slideout/button_text', $this->getScope()));
    }

    /**
     * @return string
     */
    public function getButtonColor(): string
    {
        return $this->getData('button_color') ?: $this->_scopeConfig->getValue('webforms/slideout/button_color',
            $this->getScope());
    }

    /**
     * @return string
     */
    public function getButtonTextColor(): string
    {
        return $this->getData('button_text_color') ?: $this->_scopeConfig->getValue('webforms/slideout/button_text_color',
            $this->getScope());
    }

    /**
     * @return string
     */
    public function getButtonWidth(): string
    {
        $width = $this->getData('button_width') ?: $this->_scopeConfig->getValue('webforms/slideout/button_width',
            $this->getScope());
        return $width ?: self::DEFAULT_BUTTON_WIDTH;
    }

    /**
     * @return string
     */
    public function getBackgroundColor(): string
    {
        return $this->getData('background_color') ?: $this->_scopeConfig->getValue('webforms/slideout/background_color',
            $this->getScope());
    }

    /**
     * @return string
     */
    public function getBorderColor(): string
    {
        return $this->getData('border_color') ?: $this->_scopeConfig->getValue('webforms/slideout/border_color',
            $this->getScope());
    }

    /**
     * @return string
     */
    public function getFormWidth(): string
    {
        $width = $this->getData('form_width') ?: $this->_scopeConfig->getValue('webforms/slideout/form_width',
            $this->getScope());
        return $width ?: self::DEFAULT_FORM_WIDTH;
    }

    /**
     * @return string
     */
    public function getFormMarginBottom(): string
    {
        return $this->getData('form_margin_bottom') ?: $this->_scopeConfig->getValue('webforms/slideout/form_margin_bottom',
            $this->getScope());
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws ValidatorException
     */
    protected function _toHtml()
    {
        $this->setCustomerSessionData();
        $this->setData(self::OVERRIDDEN_TEMPLATE, self::SLIDE_OUT_TEMPLATE);

        try {
            $this->initForm();
        } catch (NoSuchEntityException $e) {
            return $this->formNotInitializedAction($e->getMessage());
        }
        $this->compatibilityPatch();

        if (!$this->getForm()->canAccess()) {
            $this->setTemplate(self::ACCESS_DENIED_TEMPLATE);
        }

        $html = $this->getTemplate() ? $this->fetchView($this->getTemplateFile()) : '';

        $messages = $this->getMessages();

        return $messages->getGroupedHtml() . $html;
    }

    /**
     * Disable for slide out
     */
    protected function switchToAsyncTemplate()
    {
    }
}
