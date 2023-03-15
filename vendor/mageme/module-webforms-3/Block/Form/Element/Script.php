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

use Exception;
use MageMe\WebForms\Block\Form\Element\Script\Captcha;
use MageMe\WebForms\Block\Form\Element\Script\Logic;
use MageMe\WebForms\Block\Form\Element\Script\Submit;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 *
 */
class Script extends AbstractElement
{
    protected $_template = self::TEMPLATE_PATH . 'script.phtml';

    /**
     * @var Submit
     */
    protected $submitScript;

    /**
     * @var Logic
     */
    protected $logicScript;

    /**
     * @var Captcha
     */
    protected $captchaScript;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @param FilterProvider $filterProvider
     * @param TranslationHelper $translationHelper
     * @param Submit $submitScript
     * @param Logic $logicScript
     * @param Captcha $captchaScript
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        FilterProvider $filterProvider,
        TranslationHelper $translationHelper,
        Submit            $submitScript,
        Logic             $logicScript,
        Captcha           $captchaScript,
        Template\Context  $context,
        array             $data = [])
    {
        parent::__construct($translationHelper, $context, $data);
        $this->submitScript  = $submitScript;
        $this->logicScript   = $logicScript;
        $this->captchaScript = $captchaScript;
        $this->filterProvider = $filterProvider;
    }

    /**
     * @return Submit
     */
    public function getSubmitScript(): Submit
    {
        return $this->submitScript
            ->setUid($this->uid)
            ->setForm($this->form);
    }

    /**
     * @return Logic
     */
    public function getLogicScript(): Logic
    {
        return $this->logicScript
            ->setUid($this->uid)
            ->setForm($this->form);
    }

    /**
     * @return Captcha
     */
    public function getCaptchaScript(): Captcha
    {
        return $this->captchaScript
            ->setUid($this->uid)
            ->setForm($this->form);
    }

    /**
     * @return string
     */
    public function getFormOnLoadScript() {
        return $this->replaceCodesWithData($this->getForm()->getOnLoadScript());
    }

    /**
     * @param string $value
     * @return string
     */
    public function replaceCodesWithData(string $value): string
    {
        try {
            $filter  = $this->filterProvider->getPageFilter();
            return $filter->filter($value);
        } catch (Exception $e) {
            return $value;
        }
    }
}