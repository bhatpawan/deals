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

namespace MageMe\WebForms\Ui\Component\Result\Listing\MassActions\SubActions;


use MageMe\WebForms\Config\Options\Result\ApprovalStatus;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

class AjaxUpdateStatus extends Action
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ApprovalStatus
     */
    protected $approvalStatus;

    /**
     * @param ApprovalStatus $approvalStatus
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param null $actions
     */
    public function __construct(
        ApprovalStatus   $approvalStatus,
        UrlInterface     $urlBuilder,
        ContextInterface $context,
        array            $components = [],
        array            $data = [],
                         $actions = null
    )
    {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder     = $urlBuilder;
        $this->approvalStatus = $approvalStatus;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $statuses = $this->approvalStatus->toOptionArray();
        $actions  = [];

        foreach ($statuses as $option) {
            $actions[] = [
                'type' => $option['label'],
                'label' => $option['label'],
                'url' => $this->urlBuilder->getUrl('webforms/result/customer_massStatus', ['status' => $option['value']]),
                'isAjax' => true,
                'isSourceReloaded' => true,
                'confirm' => [
                    'title' => __('Update status'),
                    'message' => __('Are you sure to update status for selected results?'),
                    '__disableTmpl' => true,
                ],
            ];
        }

        $this->actions = $actions;
        parent::prepare();
    }

}
