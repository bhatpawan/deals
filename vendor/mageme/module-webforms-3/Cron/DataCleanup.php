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


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotDeleteException;

class DataCleanup
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * Purge constructor.
     * @param ResultRepositoryInterface $resultRepository
     * @param FormRepositoryInterface $formRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ResultRepositoryInterface $resultRepository,
        FormRepositoryInterface   $formRepository,
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        ScopeConfigInterface      $scopeConfig
    )
    {
        $this->scopeConfig           = $scopeConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->formRepository        = $formRepository;
        $this->resultRepository      = $resultRepository;
    }

    /**
     * Cronjob Description
     *
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(): void
    {
        $forms = $this->formRepository->getList()->getItems();
        foreach ($forms as $form) {
            if ($form->getIsCleanupEnabled()) {

                $cleanupPeriod = $form->getCleanupPeriod();
                if ($cleanupPeriod > 0) {
                    $date           = date('Y-m-d', strtotime('-' . $cleanupPeriod . ' day'));
                    $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter(ResultInterface::FORM_ID, $form->getId())
                        ->addFilter(ResultInterface::CREATED_AT, $date, 'lt')
                        ->create();
                    $results        = $this->resultRepository->getList($searchCriteria)->getItems();
                    foreach ($results as $result) {
                        $this->resultRepository->delete($result);
                    }
                }
            }
        }
    }
}
