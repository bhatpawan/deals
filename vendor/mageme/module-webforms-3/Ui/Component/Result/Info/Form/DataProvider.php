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

namespace MageMe\WebForms\Ui\Component\Result\Info\Form;


use MageMe\WebForms\Api\Data\ResultInterface;
use MageMe\WebForms\Api\FormRepositoryInterface;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Model\ResourceModel\Result\CollectionFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @var array
     */
    protected $_loadedData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var FormRepositoryInterface
     */
    protected $formRepository;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;


    /**
     * DataProvider constructor.
     * @param StoreRepositoryInterface $storeRepository
     * @param FormRepositoryInterface $formRepository
     * @param ResultRepositoryInterface $resultRepository
     * @param CustomerRegistry $customerRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $timezone
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param PoolInterface $pool
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        StoreRepositoryInterface  $storeRepository,
        FormRepositoryInterface   $formRepository,
        ResultRepositoryInterface $resultRepository,
        CustomerRegistry          $customerRegistry,
        ScopeConfigInterface      $scopeConfig,
        TimezoneInterface         $timezone,
        UrlInterface              $urlBuilder,
        RequestInterface          $request,
        PoolInterface             $pool,
        CollectionFactory         $collectionFactory,
        string                    $name,
        string                    $primaryFieldName,
        string                    $requestFieldName,
        array                     $meta = [],
        array                     $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection       = $collectionFactory->create();
        $this->pool             = $pool;
        $this->request          = $request;
        $this->urlBuilder       = $urlBuilder;
        $this->timezone         = $timezone;
        $this->scopeConfig      = $scopeConfig;
        $this->customerRegistry = $customerRegistry;
        $this->resultRepository = $resultRepository;
        $this->formRepository   = $formRepository;
        $this->storeRepository  = $storeRepository;

    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getData(): array
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $result_id = (int)$this->request->getParam(ResultInterface::ID);
        if (!$result_id) {
            return $this->_loadedData;
        }
        $result            = $this->resultRepository->getById($result_id);
        $data              = [];
        $data['result_id'] = $result->getId();

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        $this->_loadedData[$result->getId()] = $data;

        return $this->_loadedData;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
