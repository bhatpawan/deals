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

namespace MageMe\WebForms\Cron;


use Exception;
use MageMe\WebForms\Api\Data\MessageInterface;
use MageMe\WebForms\Api\FileMessageRepositoryInterface;
use MageMe\WebForms\Api\MessageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class MessageFilesPurge
{

    const FILES_PURGE_ENABLE = 'webforms/message/files_purge_enable';
    const FILES_PURGE_PERIOD = 'webforms/message/files_purge_period';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var MessageRepositoryInterface
     */
    protected $messageRepository;

    /**
     * @var FileMessageRepositoryInterface
     */
    protected $fileMessageRepository;

    /**
     * MessageFilesPurge constructor.
     * @param FileMessageRepositoryInterface $fileMessageRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        FileMessageRepositoryInterface $fileMessageRepository,
        MessageRepositoryInterface     $messageRepository,
        SearchCriteriaBuilder          $searchCriteriaBuilder,
        ScopeConfigInterface           $scopeConfig,
        LoggerInterface                $logger
    )
    {
        $this->logger                = $logger;
        $this->scopeConfig           = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->messageRepository     = $messageRepository;
        $this->fileMessageRepository = $fileMessageRepository;
    }

    /**
     * Purge message files
     */
    public function execute()
    {
        $purgeEnabled = $this->scopeConfig->getValue(self::FILES_PURGE_ENABLE, ScopeInterface::SCOPE_WEBSITE);
        if (!$purgeEnabled) {
            return;
        }
        $purgePeriod    = (int)$this->scopeConfig->getValue(self::FILES_PURGE_PERIOD, ScopeInterface::SCOPE_WEBSITE);
        $date           = date('Y-m-d', strtotime('-' . $purgePeriod . ' day'));
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(MessageInterface::CREATED_AT, $date, 'lt')
            ->create();
        try {
            $messages = $this->messageRepository->getList($searchCriteria)->getItems();
            foreach ($messages as $message) {
                foreach ($message->getFiles() as $file) {
                    $this->fileMessageRepository->delete($file);
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }

}
