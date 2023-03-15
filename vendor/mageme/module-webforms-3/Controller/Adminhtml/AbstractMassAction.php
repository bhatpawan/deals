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

namespace MageMe\WebForms\Controller\Adminhtml;


use MageMe\WebForms\Api\Data\FormInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action;

abstract class AbstractMassAction extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'MageMe_WebForms::manage_forms';
    const ID_FIELD = 'id';
    const REDIRECT_URL = '*/*/';

    /**
     * @var array
     */
    protected $redirect_params = [];

    /**
     * @var AccessHelper
     */
    protected $accessHelper;

    /**
     * AbstractMassAction constructor.
     * @param AccessHelper $accessHelper
     * @param Action\Context $context
     */
    public function __construct(
        AccessHelper   $accessHelper,
        Action\Context $context
    )
    {
        parent::__construct($context);
        $this->accessHelper = $accessHelper;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed(): bool
    {
        $isAllowed = parent::_isAllowed();
        $formId    = (int)$this->getRequest()->getParam(FormInterface::ID);
        if ($formId && !$this->accessHelper->isAllowed($formId)) {
            $isAllowed = false;
        }
        return $isAllowed;
    }

    /**
     * @return array
     */
    protected function getIds(): array
    {
        $Ids = $this->getRequest()->getParam(static::ID_FIELD);
        return is_array($Ids) ? $Ids : [];
    }
}