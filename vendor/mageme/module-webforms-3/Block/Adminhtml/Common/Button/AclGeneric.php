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

namespace MageMe\WebForms\Block\Adminhtml\Common\Button;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

/**
 *
 */
abstract class AclGeneric extends Generic
{
    /**
     *
     */
    const ADMIN_RESOURCE = '';

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @param AuthorizationInterface $authorization
     * @param RequestInterface $request
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        AuthorizationInterface $authorization,
        RequestInterface       $request,
        Registry               $registry,
        Context                $context
    )
    {
        parent::__construct($request, $registry, $context);
        $this->authorization = $authorization;
    }

    /**
     * @return bool
     */
    protected function isAllowed(): bool
    {
        return $this->authorization->isAllowed(static::ADMIN_RESOURCE);
    }
}