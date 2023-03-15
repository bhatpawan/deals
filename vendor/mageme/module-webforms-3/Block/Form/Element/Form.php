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

use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Framework\View\Element\Template;

class Form extends AbstractElement
{
    /**
     * @var Fieldset
     */
    protected $fieldsetBlock;

    /**
     * @var ActionsToolbar
     */
    protected $actionsToolbarBlock;

    /**
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'form.phtml';

    /**
     * @param TranslationHelper $translationHelper
     * @param Fieldset $fieldsetElementBlock
     * @param ActionsToolbar $actionsToolbarBlock
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        TranslationHelper $translationHelper,
        Fieldset          $fieldsetElementBlock,
        ActionsToolbar    $actionsToolbarBlock,
        Template\Context  $context,
        array             $data = [])
    {
        parent::__construct($translationHelper, $context, $data);
        $this->fieldsetBlock       = $fieldsetElementBlock;
        $this->actionsToolbarBlock = $actionsToolbarBlock;
    }

    /**
     * @return string
     */
    public function getEnctype(): string
    {
        return 'multipart/form-data';
    }

    /**
     * @return ActionsToolbar
     */
    public function getActionsToolbarBlock(): ActionsToolbar
    {
        return $this->actionsToolbarBlock
            ->setUid($this->uid)
            ->setForm($this->form);
    }

    /**
     * @return Fieldset
     */
    public function getFieldsetBlock(): Fieldset
    {
        return $this->fieldsetBlock
            ->setResult($this->result)
            ->setUid($this->uid)
            ->setForm($this->form);
    }
}