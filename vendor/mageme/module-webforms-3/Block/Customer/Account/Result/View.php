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

namespace MageMe\WebForms\Block\Customer\Account\Result;

use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Model\ResourceModel;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class View extends Template
{
    /**
     * @var bool
     */
    protected $_isScopePrivate = true;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ResourceModel\Message\CollectionFactory
     */
    protected $messageCollectionFactory;

    /**
     * View constructor.
     * @param Template\Context $context
     * @param ResourceModel\Message\CollectionFactory $messageCollectionFactory
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Template\Context                        $context,
        ResourceModel\Message\CollectionFactory $messageCollectionFactory,
        Registry                                $coreRegistry,
        array                                   $data = []
    )
    {
        parent::__construct($context, $data);

        $this->registry                 = $coreRegistry;
        $this->messageCollectionFactory = $messageCollectionFactory;
    }

    /**
     * @return array|ResourceModel\Message\Collection
     */
    public function getMessages()
    {
        $result = $this->getResult();
        if ($result !== null && $result->getId()) {
            return $this->messageCollectionFactory->create()
                ->addFilter(MessageInterface::RESULT_ID, $result->getId())
                ->addOrder(MessageInterface::CREATED_AT, 'desc');
        }
        return [];
    }

    /**
     * @return ResultInterface|null
     */
    public function getResult(): ?ResultInterface
    {
        return $this->registry->registry('webforms_result');
    }
}
