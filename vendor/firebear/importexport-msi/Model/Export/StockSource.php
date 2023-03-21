<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExportMsi\Model\Export;

use Firebear\ImportExport\Model\Export\EntityInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Firebear\ImportExport\Model\ExportJob\Processor;
use Firebear\ImportExport\Traits\Export\Entity as ExportTrait;

/**
 * StockSource Export
 */
class StockSource extends AbstractEntity implements EntityInterface
{
    use ExportTrait;

    /**
     * Source collection factory
     *
     * @var \Magento\Inventory\Model\ResourceModel\Source\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Export columns
     *
     * @var array
     */
    protected $_columns = [
        SourceInterface::SOURCE_CODE,
        SourceInterface::NAME,
        SourceInterface::CONTACT_NAME,
        SourceInterface::EMAIL,
        SourceInterface::ENABLED,
        SourceInterface::DESCRIPTION,
        SourceInterface::LATITUDE,
        SourceInterface::LONGITUDE,
        SourceInterface::COUNTRY_ID,
        SourceInterface::REGION_ID,
        SourceInterface::REGION,
        SourceInterface::CITY,
        SourceInterface::STREET,
        SourceInterface::POSTCODE,
        SourceInterface::PHONE,
        SourceInterface::FAX,
        SourceInterface::USE_DEFAULT_CARRIER_CONFIG,
        SourceInterface::CARRIER_LINKS
    ];

    /**
     * Initialize export
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ExportFactory $exportFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param ConsoleOutput $output
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $exportFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        ConsoleOutput $output,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_logger = $logger;
        $this->output = $output;
        $this->_collectionFactory = $collectionFactory;

        parent::__construct(
            $scopeConfig,
            $storeManager,
            $exportFactory,
            $resourceColFactory,
            $data
        );
    }

    /**
     * Retrieve header columns
     *
     * @return string[]
     */
    protected function _getHeaderColumns()
    {
        return $this->changeHeaders(
            $this->_columns
        );
    }

    /**
     * Retrieve entity field for export
     *
     * @return array
     */
    public function getFieldsForExport()
    {
        return $this->_columns;
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'stock_sources';
    }

    /**
     * Export process
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export()
    {
        //Execution time may be very long
        set_time_limit(0);

        $this->addLogWriteln(__('Begin Export'), $this->output);
        $this->addLogWriteln(__('Scope Data'), $this->output);

        $collection = $this->_getEntityCollection();
        $this->_prepareEntityCollection($collection);
        $writer = $this->getWriter();
        // create export file
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($collection);

        return [
            $writer->getContents(),
            $this->_processedEntitiesCount,
            $this->lastEntityId
        ];
    }

    /**
     * Export one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function exportItem($item)
    {
        $data = $item->toArray();
        // prepare item data
        $data['carrier_links'] = implode('|', $data['carrier_links'] ?? []);
        $data = $this->changeRow($data);

        $this->getWriter()->writeRow($data);
        $this->_processedEntitiesCount++;
    }

    /**
     * Retrieve entity collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection()
    {
        return $this->_collectionFactory->create();
    }

    /**
     * Apply filter to collection
     *
     * @param AbstractCollection $collection
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function _prepareEntityCollection(AbstractCollection $collection)
    {
        if (!empty($this->_parameters[Processor::LAST_ENTITY_ID]) &&
            $this->_parameters[Processor::LAST_ENTITY_SWITCH] > 0
        ) {
            $collection->addFieldToFilter(
                'main_table.source_code',
                ['gt' => $this->_parameters[Processor::LAST_ENTITY_ID]]
            );
        }

        if (!isset($this->_parameters[Processor::EXPORT_FILTER_TABLE]) ||
            !is_array($this->_parameters[Processor::EXPORT_FILTER_TABLE])) {
            $exportFilter = [];
        } else {
            $exportFilter = $this->_parameters[Processor::EXPORT_FILTER_TABLE];
        }

        $filters = [];
        $entity = $this->getEntityTypeCode();
        foreach ($exportFilter as $data) {
            if ($data['entity'] == $entity) {
                $filters[$data['field']] = $data['value'];
            }
        }

        $fields = [];
        $columns = $this->getFieldColumns();
        foreach ($columns[$this->getEntityTypeCode()] as $field) {
            $fields[$field['field']] = $field['type'];
        }

        foreach ($filters as $key => $value) {
            if (isset($fields[$key])) {
                $type = $fields[$key];

                if ('text' == $type) {
                    if (is_scalar($value)) {
                        trim($value);
                    }
                    $collection->addFieldToFilter($key, ['like' => "%{$value}%"]);
                } elseif ('select' == $type) {
                    $collection->addFieldToFilter($key, ['eq' => $value]);
                } elseif ('int' == $type) {
                    if (is_array($value) && count($value) == 2) {
                        $from = array_shift($value);
                        $to = array_shift($value);

                        if (is_numeric($from)) {
                            $collection->addFieldToFilter($key, ['from' => $from]);
                        }
                        if (is_numeric($to)) {
                            $collection->addFieldToFilter($key, ['to' => $to]);
                        }
                    }
                } elseif ('date' == $type) {
                    if (is_array($value) && count($value) == 2) {
                        $from = array_shift($value);
                        $to = array_shift($value);

                        if (is_scalar($from) && !empty($from)) {
                            $date = (new \DateTime($from))->format('m/d/Y');
                            $collection->addFieldToFilter($key, ['from' => $date, 'date' => true]);
                        }
                        if (is_scalar($to) && !empty($to)) {
                            $date = (new \DateTime($to))->format('m/d/Y');
                            $collection->addFieldToFilter($key, ['to' => $date, 'date' => true]);
                        }
                    }
                }
            }
        }
        return $collection;
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
        foreach ($this->_columns as $field) {
            $type = 'text';
            $select = [];
            if ('enabled' == $field) {
                $select[] = ['label' => __('Yes'), 'value' => 1];
                $select[] = ['label' => __('No'), 'value' => 0];
                $type = 'select';
            }
            $options[$this->getEntityTypeCode()][] = ['field' => $field, 'type' => $type, 'select' => $select];
        }
        return $options;
    }
}
