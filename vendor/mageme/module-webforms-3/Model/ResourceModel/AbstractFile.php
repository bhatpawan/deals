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

namespace MageMe\WebForms\Model\ResourceModel;


use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;

abstract class AbstractFile extends AbstractDb
{
    const DELETE_EVENT_NAME = '';

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * AbstractFile constructor.
     * @param ManagerInterface $eventManager
     * @param Context $context
     * @param null $connectionName
     */
    public function __construct(
        ManagerInterface $eventManager,
        Context          $context,
                         $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->eventManager = $eventManager;
    }

    /**
     * @param AbstractModel|\MageMe\WebForms\Model\File\AbstractFile $object
     * @return AbstractFile
     * @throws NoSuchEntityException
     */
    protected function _beforeDelete(AbstractModel $object): AbstractFile
    {
        @unlink($object->getFullPath());
        $this->eventManager->dispatch(static::DELETE_EVENT_NAME, ['file' => $object]);
        return parent::_beforeDelete($object);
    }
}