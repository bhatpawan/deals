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

namespace MageMe\WebForms\Block\Result;

use MageMe\WebForms\Api\Data\FieldInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FieldRepositoryInterface;
use MageMe\WebForms\Model\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

/**
 *
 */
class Rating extends Template
{
    /**
     * @var string
     */
    protected $_template = 'result/rating.phtml';

    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * @var FieldRepositoryInterface
     */
    protected $_fieldRepository;

    /**
     * Rating constructor.
     * @param FieldRepositoryInterface $fieldRepository
     * @param ResultFactory $resultFactory
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        ResultFactory            $resultFactory,
        Template\Context         $context,
        array                    $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_resultFactory   = $resultFactory;
        $this->_fieldRepository = $fieldRepository;
    }

    /**
     * @param $fieldId
     * @return FieldInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getField($fieldId): FieldInterface
    {
        return $this->_fieldRepository->getById(
            $fieldId,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @return array|bool
     * @throws NoSuchEntityException
     */
    public function getSummaryRatings()
    {
        $form_id  = $this->getData(ResultInterface::FORM_ID);
        $store_id = $this->_storeManager->getStore()->getId();
        if (!$form_id) {
            return false;
        }

        return $this->_resultFactory->create()->getResource()->getSummaryRatings($form_id, $store_id);
    }
}
