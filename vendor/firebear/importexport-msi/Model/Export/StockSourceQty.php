<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExportMsi\Model\Export;

use Firebear\ImportExport\Model\Export\EntityInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryImportExport\Model\Export\Sources as AbstractEntity;
use Magento\InventoryImportExport\Model\Export\AttributeCollectionProvider;
use Magento\InventoryImportExport\Model\Export\SourceItemCollectionFactoryInterface;
use Magento\InventoryImportExport\Model\Export\ColumnProviderInterface;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Firebear\ImportExport\Model\Export\Dependencies\Config as ExportConfig;
use Firebear\ImportExport\Traits\Export\Entity as ExportTrait;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
/**
 * StockSourceQty Export
 */
class StockSourceQty extends AbstractEntity implements EntityInterface
{
    use ExportTrait;

    /**
     * Export config data
     *
     * @var array
     */
    protected $_exportConfig;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;
    /**
     * @var SourceItemCollectionFactoryInterface
     */
    protected $_sourceItemCollectionFactory;

    /**
     * Initialize export
     *
     * @param CollectionFactory $productCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ExportFactory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param SourceItemCollectionFactoryInterface $sourceItemCollectionFactory
     * @param ColumnProviderInterface $columnProvider
     * @param ConsoleOutput $output
     * @param LoggerInterface $logger
     * @param ExportConfig $exportConfig
     * @param array $data
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        AttributeCollectionProvider $attributeCollectionProvider,
        SourceItemCollectionFactoryInterface $sourceItemCollectionFactory,
        ColumnProviderInterface $columnProvider,
        ConsoleOutput $output,
        LoggerInterface $logger,
        ExportConfig $exportConfig,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_logger = $logger;
        $this->output = $output;
        $this->_exportConfig = $exportConfig->get();
        $this->_sourceItemCollectionFactory = $sourceItemCollectionFactory;

        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $attributeCollectionProvider,
            $sourceItemCollectionFactory,
            $columnProvider,
            $data
        );
    }

    /**
     * Export process
     *
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export()
    {
        //Execution time may be very long
        set_time_limit(0);

        $this->addLogWriteln(__('Begin Export'), $this->output);
        $this->addLogWriteln(__('Scope Data'), $this->output);

        $count = 0;
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());

        $parameters = $this->_parameters;
        unset($parameters['export_filter']['tier_price']);
        unset($parameters['export_filter']['updated_at']);
        unset($parameters['export_filter']['value']);

        /** @var SourceItemCollection $collection */
        $collection = $this->_sourceItemCollectionFactory->create(
            $this->getAttributeCollection(),
            $parameters
        );
        $collection = $collection->addFieldToFilter('sku', ['in' => $this->getSkuList()]);

        foreach ($collection->getData() as $data) {
            // last entity id
            $this->lastEntityId = $data[SourceItem::ID_FIELD_NAME];
            unset($data[SourceItem::ID_FIELD_NAME]);

            $data = $this->changeRow($data);
            $writer->writeRow($data);
            $count++;
        }
        // create export file
        return [
            $writer->getContents(),
            $count,
            $this->lastEntityId
        ];
    }

    /**
     * @return array
     */
    protected function getSkuList()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('sku');
        $skuList = [];
        foreach ($collection as $product) {
            $skuList[] = $product->getData()['sku'];
        }

        return $skuList;
    }

    /**
     * Retrieve header columns
     *
     * @return string[]
     */
    protected function _getHeaderColumns()
    {
        return $this->changeHeaders(
            parent::_getHeaderColumns()
        );
    }

    /**
     * Retrieve entity field for export
     *
     * @return array
     */
    public function getFieldsForExport()
    {
        return parent::_getHeaderColumns();
    }

    /**
     * Retrieve entity field for filter
     *
     * @return array
     */
    public function getFieldsForFilter()
    {
        $options = [];
        foreach ($this->getFieldsForExport() as $field) {
            $options[] = [
                'label' => $field,
                'value' => $field
            ];
        }
        return [$this->getEntityTypeCode() => $options];
    }

    /**
     * Retrieve entity field columns
     *
     * @return array
     */
    public function getFieldColumns()
    {
        $options = [];
        foreach (parent::_getHeaderColumns() as $field) {
            $type = 'text';
            $select = [];
            if ('status' == $field) {
                $select[] = ['label' => __('Active'), 'value' => 1];
                $select[] = ['label' => __('Inactive'), 'value' => 0];
                $type = 'select';
            }
            $options['stock_sources_qty'][] = ['field' => $field, 'type' => $type, 'select' => $select];
        }
        return $options;
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'stock_sources_qty';
    }
}
