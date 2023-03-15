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

namespace MageMe\WebForms\Controller\Adminhtml\Fieldset;

use MageMe\WebForms\Api\FieldsetRepositoryInterface;
use MageMe\WebForms\Helper\Form\AccessHelper;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;

class NewAction extends AbstractFieldsetAction
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param ForwardFactory $resultForwardFactory
     * @param FieldsetRepositoryInterface $repository
     * @param AccessHelper $accessHelper
     * @param Context $context
     */
    public function __construct(
        ForwardFactory              $resultForwardFactory,
        FieldsetRepositoryInterface $repository,
        AccessHelper                $accessHelper,
        Context                     $context
    )
    {
        parent::__construct($repository, $accessHelper, $context);
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
