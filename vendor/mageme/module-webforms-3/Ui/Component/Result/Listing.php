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

namespace MageMe\WebForms\Ui\Component\Result;

use MageMe\WebForms\Api\Data\FormInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Listing extends \Magento\Ui\Component\Listing
{
    /**
     * Constructor
     *
     * @param AuthorizationInterface $authorization
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ContextInterface       $context,
        UrlInterface           $urlBuilder,
        RequestInterface       $request,
        array                  $components = [],
        array                  $data = []
    ) {
        if ($authorization->isAllowed('MageMe_WebForms::edit_result')) {
            $data['buttons'][] = [
                'name' => __('Add Result'),
                'label' => __('Add Result'),
                'class' => 'primary',
                'url' => $urlBuilder->getUrl('*/*/new', [FormInterface::ID => $request->getParam(FormInterface::ID)])
            ];
        }

        $data['buttons'][] = [
            'name' => __('Edit Form'),
            'label' => __('Edit Form'),
            'class' => 'edit action-secondary',
            'url' => $urlBuilder->getUrl('*/form/edit', [FormInterface::ID => $request->getParam(FormInterface::ID)])
        ];

        parent::__construct($context, $components, $data);
    }
}
