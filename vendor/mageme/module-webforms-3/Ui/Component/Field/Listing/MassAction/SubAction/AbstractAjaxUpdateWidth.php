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

namespace MageMe\WebForms\Ui\Component\Field\Listing\MassAction\SubAction;


use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Config\Options\Field\ResponsiveSize;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

abstract class AbstractAjaxUpdateWidth extends \MageMe\WebForms\Ui\Component\Common\Listing\MassAction\SubAction\AbstractAjaxUpdateWidth
{
    /**
     * AbstractAjaxUpdateWidth constructor.
     * @param ResponsiveSize $options
     * @param FormRepositoryInterface $formRepository
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param null $actions
     */
    public function __construct(
        ResponsiveSize          $options,
        FormRepositoryInterface $formRepository,
        RequestInterface        $request,
        UrlInterface            $urlBuilder,
        ContextInterface        $context,
        array                   $components = [],
        array                   $data = [],
                                $actions = null
    )
    {
        parent::__construct($formRepository, $request, $urlBuilder, $context, $components, $data, $actions);
        $this->options = $options->toOptionArray();
    }
}