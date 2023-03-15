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

namespace MageMe\WebForms\Block\Adminhtml\Result;

use MageMe\WebForms\Api\Data\ResultInterface;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;

class Popup extends Container
{
    protected $_template = 'MageMe_WebForms::result/popup.phtml';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * Popup constructor.
     * @param AuthorizationInterface $authorization
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        Registry               $registry,
        Context                $context,
        array                  $data = []
    )
    {
        parent::__construct($context, $data);
        $this->registry      = $registry;
        $this->authorization = $authorization;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $result = $this->getResult();
        if ($result) {
            $this->prepareButtons($result);
        }
        $resultView = $this->getLayout()->createBlock(View::class, 'result_view')->setResult($result)->setOptions(['skip_fields' => []]);
        $this->setChild('result_view', $resultView);
        return parent::_prepareLayout();
    }

    /**
     * @return ResultInterface|null
     */
    public function getResult(): ?ResultInterface
    {
        return $this->registry->registry('webforms_result');
    }

    /**
     * @param ResultInterface $result
     */
    public function prepareButtons(ResultInterface $result)
    {
        if ($this->authorization->isAllowed('MageMe_WebForms::edit_result')) {
            $this->buttonList->add('edit', [
                'label' => __('Edit Result'),
                'onclick' => 'window.location.href = \'' . $this->getUrl('*/*/edit', [
                        ResultInterface::ID => $result->getId(),
                        ResultInterface::FORM_ID => $result->getFormId(),
                        ResultInterface::CUSTOMER_ID => $result->getCustomerId()
                    ]) . '\'',
                'sort_order' => 10,
            ]);
        }
        if ($this->authorization->isAllowed('MageMe_WebForms::reply')) {
            $this->buttonList->add('reply', [
                'label' => __('Reply'),
                'class' => 'primary',
                'onclick' => 'window.location.href = \'' . $this->getUrl('*/*/reply', [
                        ResultInterface::ID => $result->getId(),
                        ResultInterface::FORM_ID => $result->getFormId(),
                        ResultInterface::CUSTOMER_ID => $result->getCustomerId()
                    ]) . '\'',
                'sort_order' => 20,
            ]);
        }
    }
}
