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

use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Framework\View\Element\Template;

abstract class AbstractElement extends Template
{
    const TEMPLATE_PATH = 'form/element/';

    /**
     * @var string
     */
    protected $uid;

    /** @var FormInterface */
    protected $form;

    /** @var ResultInterface */
    protected $result;
    /**
     * @var TranslationHelper
     */
    private $translationHelper;

    /**
     * @param TranslationHelper $translationHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        TranslationHelper $translationHelper,
        Template\Context  $context,
        array             $data = []
    )
    {
        parent::__construct($context, $data);
        $this->translationHelper = $translationHelper;
    }

    /**
     * @return ResultInterface|null
     */
    public function getResult(): ?ResultInterface
    {
        return $this->result;
    }

    /**
     * @param ResultInterface|null $result
     * @return $this
     */
    public function setResult(?ResultInterface $result): AbstractElement
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUid(): string
    {
        return $this->uid ?? $this->getForm()->getId();
    }

    /**
     * @param string|null $uid
     * @return $this
     */
    public function setUid(?string $uid): AbstractElement
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @param FormInterface $form
     * @return $this
     */
    public function setForm(FormInterface $form): AbstractElement
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @param string|null $str
     * @return string
     */
    public function applyTranslation(?string $str): ?string
    {
        return $this->translationHelper->applyTranslation($str);
    }
}