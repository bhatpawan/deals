<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Export\Customer;

use Firebear\ImportExport\Traits\Export\Entity as ExportTrait;
use Firebear\ImportExport\Model\Export\EntityInterface;
use Magento\CustomerFinance\Helper\Data as HelperData;
use \Magento\CustomerFinance\Model\ResourceModel\Customer\Attribute\Finance\Collection as CustomerAttributeCollection;
use Magento\CustomerFinance\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class Finance
 *
 * @package Firebear\ImportExport\Model\Export\Customer
 */
class Finance extends AbstractEntity implements EntityInterface
{
    use ExportTrait;

    /**#@+
     * Permanent column names
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL = '_email';

    const COLUMN_FINANCE_WEBSITE = '_finance_website';

    const COLUMN_WEBSITE = '_website';

    /**#@-*/

    /**
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = CustomerAttributeCollection::class;

    /**
     * Website ID-to-code
     *
     * @var array
     */
    protected $_websiteIdToCode = [];

    /**
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = 'export/customer_page_size/finance';

    /**
     * Array of attributes for export
     *
     * @var string[]
     */
    protected $_entityAttributes;

    /**
     * Customers whose address are exported
     *
     * @var \Magento\CustomerFinance\Model\ResourceModel\Customer\Collection
     */
    protected $_customerCollection;

    /**
     * Permanent entity columns
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COLUMN_EMAIL, self::COLUMN_WEBSITE, self::COLUMN_FINANCE_WEBSITE];

    /**
     * Customers whose financial data is exported
     *
     * @var \Magento\CustomerImportExport\Model\Export\Customer
     */
    protected $_customerEntity;

    /**
     * @var CustomerCollectionFactory
     */
    protected $_customerCollectionFactory;

    /**
     * Helper to check whether modules are enabled/disabled
     *
     * @var HelperData
     */
    protected $_customerFinanceData;

    /**
     * @var \Magento\CustomerImportExport\Model\Export\CustomerFactory
     */
    protected $_eavCustomerFactory;

    /**
     * @var \Firebear\ImportExport\Model\IntegrationFactory
     */
    protected $_integrationFactory;

    /**
     * Finance constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param \Magento\CustomerImportExport\Model\Export\CustomerFactory $eavCustomerFactory
     * @param \Firebear\ImportExport\Model\IntegrationFactory $integrationFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        \Magento\CustomerImportExport\Model\Export\CustomerFactory $eavCustomerFactory,
        \Firebear\ImportExport\Model\IntegrationFactory $integrationFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);

        $this->_eavCustomerFactory = $eavCustomerFactory;
        $this->_integrationFactory = $integrationFactory;

        $this->_initFrontendWebsites()->_initWebsites(true);
        $this->setFileName($this->getEntityTypeCode());
    }

    /**
     * @return mixed
     */
    protected function _getHeaderColumns()
    {
        $headers = array_merge($this->getPermanentAttributes(), $this->_getExportAttributeCodes());

        return $this->changeHeaders($headers);
    }

    /**
     * Initialize frontend websites
     *
     * @return $this
     */
    protected function _initFrontendWebsites()
    {
        /** @var $website \Magento\Store\Model\Website */
        foreach ($this->_storeManager->getWebsites() as $website) {
            $this->_websiteIdToCode[$website->getId()] = $website->getCode();
        }
        return $this;
    }

    /**
     * Get customers collection
     *
     * @return \Magento\CustomerFinance\Model\ResourceModel\Customer\Collection
     */
    protected function _getEntityCollection()
    {
        if (empty($this->_customerCollection)) {
            if (empty($this->_customerCollectionFactory)) {
                $this->_customerCollectionFactory = $this->_integrationFactory
                    ->create(CustomerCollectionFactory::class);
            }
            $this->_customerCollection = $this->_customerCollectionFactory->create();
        }
        return $this->_customerCollection;
    }

    /**
     * Set parameters (push filters from post into export customer model)
     *
     * @param string[] $parameters
     * @return \Magento\CustomerImportExport\Model\Export\Address
     */
    public function setParameters(array $parameters)
    {
        if (empty($this->_customerEntity)) {
            $this->_customerEntity = $this->_eavCustomerFactory->create();
        }

        if (empty($this->_customerFinanceData)) {
            $this->_customerFinanceData = $this->_integrationFactory->create(HelperData::class);
        }

        if ($this->_customerFinanceData->isCustomerBalanceEnabled()) {
            $this->_getEntityCollection()->joinWithCustomerBalance(
                $this->_customerEntity->getAttributeCollection(),
                $this->getAttributeCollection()
            );
        }

        if ($this->_customerFinanceData->isRewardPointsEnabled()) {
            $this->_getEntityCollection()->joinWithRewardPoints(
                $this->_customerEntity->getAttributeCollection(),
                $this->getAttributeCollection()
            );
        }

        $this->_customerEntity->setParameters($parameters);

        return parent::setParameters($parameters);
    }

    /**
     * Get list of permanent attributes
     *
     * @return string[]
     */
    public function getPermanentAttributes()
    {
        return $this->_permanentAttributes;
    }

    /**
     * @return mixed
     */
    public function getFieldsForExport()
    {
        return array_unique(
            array_merge($this->getPermanentAttributes(), $this->_getExportAttributeCodes())
        );
    }

    /**
     * Retrieve entity field for filter
     *
     * @return array
     */
    public function getFieldsForFilter()
    {
        $fields = [];
        foreach ($this->_getExportAttributeCodes() as $field) {
            $fields[] = ['value' => $field, 'label' => $field];
        }
        return [$this->getEntityTypeCode() => $fields];
    }

    /**
     * Retrieve entity field columns
     *
     * @return array
     * @throws LocalizedException
     */
    public function getFieldColumns()
    {
        $options = [];
        foreach ($this->_getExportAttributeCodes() as $field) {
            $select = [];
            $type = 'text';
            $options[$this->getEntityTypeCode()][] = [
                'field' => $field,
                'type' => $type,
                'select' => $select
            ];
        }
        return $options;
    }

    /**
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export()
    {
        $writer = $this->getWriter();

        //Execution time may be very long
        set_time_limit(0);

        $this->addLogWriteln(__('Begin Export'), $this->output);
        $this->addLogWriteln(__('Scope Data'), $this->output);

        $collection = $this->_getEntityCollection();
        $this->_prepareEntityCollection($collection);
        $this->_exportCollectionByPages($collection);

        return [
            $writer->getContents(),
            $this->_processedEntitiesCount
        ];
    }

    /**
     * @param $item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function exportItem($item)
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();

        foreach ($this->_websiteIdToCode as $websiteCode) {
            $row = [];
            foreach ($validAttributeCodes as $code) {
                $attributeCode = $websiteCode . '_' . $code;
                $websiteData = $item->getData($attributeCode);
                if (null !== $websiteData) {
                    $row[$code] = $websiteData;
                }
            }

            if (isset($this->_parameters['export_filter']['_finance_website'])
                && $websiteCode != $this->_parameters['export_filter']['_finance_website']
            ) {
                continue;
            }

            if (!empty($row)) {
                $row += array_fill_keys($validAttributeCodes, '');
                ksort($row);
                $row[self::COLUMN_EMAIL] = $item->getEmail();
                $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
                $row[self::COLUMN_FINANCE_WEBSITE] = $websiteCode;
                $this->getWriter()->writeRow($this->changeRow($row));
            }
        }
        $this->_processedEntitiesCount++;
    }

    /**
     * Apply filter to collection and add not skipped attributes to select
     *
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    protected function _prepareEntityCollection(AbstractCollection $collection)
    {
        $this->additionalFilters($collection);
        $this->_customerEntity->filterEntityCollection($collection);
        $this->filterAttributeCollection($collection);

        return $collection;
    }

    /**
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    public function additionalFilters($collection)
    {
        $code = $this->_parameters['export_filter']['code'] ?? false;
        $financeWebsite = $this->_parameters['export_filter']['_finance_website'] ?? false;
        if ($code || $financeWebsite) {
            if ($financeWebsite) {
                $code = $financeWebsite;
            }
            $collection->getSelect()->joinLeft(
                ['sw' => 'store_website'],
                'e.website_id = sw.website_id',
                ['sw.code']
            )
                ->where('sw.code = ?', $code)
                ->group('e.entity_id');
        }

        return $collection;
    }

    /**
     * Entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'customer_finance';
    }

    /**
     * Retrieve attributes codes which are appropriate for export
     *
     * @return array
     */
    protected function _getExportAttrCodes()
    {
        return [];
    }
}
