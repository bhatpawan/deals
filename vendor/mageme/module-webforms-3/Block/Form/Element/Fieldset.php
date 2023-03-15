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

use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Helper\TranslationHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Fieldset extends AbstractElement
{
    /**
     * @var int
     */
    protected $fieldsetId;

    /**
     * @var array
     */
    protected $fieldset;

    /**
     * @var Field
     */
    protected $fieldBlock;

    /**
     * @var FieldsetRepositoryInterface
     */
    private  $fieldsetRepository;

    /**
     * @var string
     */
    protected $_template = self::TEMPLATE_PATH . 'fieldset.phtml';

    /**
     * @param TranslationHelper $translationHelper
     * @param Field $fieldBlock
     * @param Context $context
     * @param FieldsetRepositoryInterface $fieldsetRepository
     * @param array $data
     */
    public function __construct(
        TranslationHelper           $translationHelper,
        Field                       $fieldBlock,
        Template\Context            $context,
        FieldsetRepositoryInterface $fieldsetRepository,
        array                       $data = [])
    {
        parent::__construct($translationHelper, $context, $data);
        $this->fieldBlock = $fieldBlock;
        $this->fieldsetRepository = $fieldsetRepository;
    }

    /**
     * @return Field
     */
    public function getFieldBlock(): Field
    {
        return $this->fieldBlock
            ->setResult($this->result)
            ->setUid($this->uid)
            ->setForm($this->form);
    }

    /**
     * @return int
     */
    public function getFieldsetId(): int
    {
        return $this->fieldsetId;
    }

    /**
     * @param int $fieldsetId
     * @return Fieldset
     */
    public function setFieldsetId(int $fieldsetId): Fieldset
    {
        $this->fieldsetId = $fieldsetId;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldset(): array
    {
        return $this->fieldset;
    }

    /**
     * @param array $fieldset
     * @return Fieldset
     */
    public function setFieldset(array $fieldset): Fieldset
    {
        $this->fieldset = $fieldset;
        return $this;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isLabelHidden(): bool
    {
        if($this->fieldsetId){
            $fieldsetModel = $this->fieldsetRepository->getById($this->fieldsetId);
            if($fieldsetModel)
                return $fieldsetModel->getIsLabelHidden();
        }
        return false;
    }
}