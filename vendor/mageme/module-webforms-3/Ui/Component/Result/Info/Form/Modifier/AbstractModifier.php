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

namespace MageMe\WebForms\Ui\Component\Result\Info\Form\Modifier;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

abstract class AbstractModifier extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var ResultInterface
     */
    protected $result;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param FormRepositoryInterface $formRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param TimezoneInterface $timezone
     * @param RequestInterface $request
     */
    public function __construct(
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        ScopeConfigInterface      $scopeConfig,
        FormRepositoryInterface   $formRepository,
        ResultRepositoryInterface $resultRepository,
        TimezoneInterface         $timezone,
        RequestInterface          $request
    )
    {
        $this->request               = $request;
        $this->resultRepository      = $resultRepository;
        $this->formRepository        = $formRepository;
        $this->scopeConfig           = $scopeConfig;
        $this->timezone              = $timezone;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @return ResultInterface|\MageMe\WebForms\Model\Result
     * @throws NoSuchEntityException
     */
    public function getResult()
    {
        if (!$this->result) {
            $result_id    = $this->request->getParam(ResultInterface::ID);
            $this->result = $this->resultRepository->getById($result_id);
        }
        return $this->result;
    }
}
