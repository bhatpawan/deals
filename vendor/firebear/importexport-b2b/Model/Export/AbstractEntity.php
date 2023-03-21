<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export;

use Firebear\ImportExport\Model\Export\Dependencies\Config;
use Firebear\ImportExport\Model\Export\EntityInterface;
use Firebear\ImportExport\Model\ExportJob\Processor;
use Firebear\ImportExport\Model\Source\Factory as SourceFactory;
use Firebear\ImportExport\Traits\Export\Entity as ExportTrait;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\ImportExport\Model\Export\AbstractEntity as BaseAbstractEntity;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class AbstractEntity extends BaseAbstractEntity implements EntityInterface
{
    use ExportTrait;

    /**
     * @var ConsoleOutput
     */
    protected $consoleOutput;

    /**
     * Entity collection
     *
     * @var AbstractDb
     */
    protected $entityCollection;

    /**
     * Entity type code
     *
     * @var string
     */
    protected $entityTypeCode;

    /**
     * Array of adapters for export
     *
     * @var AbstractAdapter[]
     */
    protected $adapters;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param ConsoleOutput $consoleOutput
     * @param Config $config
     * @param SourceFactory $sourceFactory
     * @param AbstractDb $entityCollection
     * @param $entityTypeCode
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectionByPagesIteratorFactory $resourceColFactory,
        ConsoleOutput $consoleOutput,
        Config $config,
        SourceFactory $sourceFactory,
        AbstractDb $entityCollection,
        $entityTypeCode,
        array $data = []
    ) {
        $this->consoleOutput = $consoleOutput;
        $this->entityCollection = $entityCollection;
        $this->entityTypeCode = $entityTypeCode;

        $this->adapters = [];
        foreach ($config->get($entityTypeCode)['fields'] as $key => $value) {
            /** @var AbstractAdapter $adapter */
            $adapter = $sourceFactory->create($value['model']);
            $adapter
                ->setEntityTypeCode($key)
                ->setLabel($value['label']);

            $this->adapters[] = $adapter;
        };

        $this->_pageSize = isset(
            $data['page_size']
        ) ? $data['page_size'] : (static::XML_PATH_PAGE_SIZE ? (int)$scopeConfig->getValue(
            static::XML_PATH_PAGE_SIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) : 0);
        $this->_byPagesIterator = isset(
            $data['collection_by_pages_iterator']
        ) ? $data['collection_by_pages_iterator'] : $resourceColFactory->create();
    }

    public function getEntityTypeCode()
    {
        return $this->entityTypeCode;
    }

    public function getFieldColumns()
    {
        $columns = [];
        foreach ($this->adapters as $adapter) {
            $columns[$adapter->getEntityTypeCode()] = $adapter->getFieldColumns();
        }

        return $columns;
    }

    public function getFieldsForFilter()
    {
        $fields = [];
        foreach ($this->adapters as $adapter) {
            $fields[$adapter->getEntityTypeCode()] = $adapter->getFieldsForFilter();
        }

        return $fields;
    }

    public function getFieldsForExport()
    {
        $fields = [];
        foreach ($this->adapters as $adapter) {
            $fields[$adapter->getEntityTypeCode()] = $adapter->getFieldsForExport();
        }

        return $fields;
    }

    public function export()
    {
        // Execution time may be very long
        set_time_limit(0);

        if (!$this->getAdapters()) {
            $this->addLogWriteln(
                __('You need select entities for export'),
                $this->consoleOutput,
                'error'
            );

            return false;
        }

        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());

        $collection = $this->_getEntityCollection();
        $this->_prepareEntityCollection($collection);
        $this->_exportCollectionByPages($collection);

        // create export file
        return [
            $writer->getContents(),
            $this->_processedEntitiesCount,
            $this->lastEntityId
        ];
    }

    public function exportItem($item)
    {
        $data = [];
        foreach ($this->getAdapters() as $adapter) {
            $data = array_replace_recursive($data, $adapter->exportItem($item));
        }

        $separator = isset($this->_parameters[Processor::BEHAVIOR_DATA]['multiple_value_separator']) ?
            $this->_parameters[Processor::BEHAVIOR_DATA]['multiple_value_separator'] :
            Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;

        foreach ($data as $row) {
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    $row[$key] = implode($separator, $value);
                }
            }

            $this->getWriter()->writeRow(
                $this->changeRow($row)
            );
        }
        $this->_processedEntitiesCount++;
    }

    protected function _getHeaderColumns()
    {
        $columns = [];
        foreach ($this->getAdapters() as $adapter) {
            $columns = array_merge(
                $columns,
                array_keys($adapter->getHeaderColumns())
            );
        }

        return $this->changeHeaders($columns);
    }

    protected function _getEntityCollection()
    {
        return $this->entityCollection;
    }

    protected function _prepareEntityCollection(
        AbstractDB $collection
    ) {
        if (isset($this->_parameters[Processor::LAST_ENTITY_ID]) &&
            $this->_parameters[Processor::LAST_ENTITY_ID] > 0 &&
            $this->_parameters[Processor::LAST_ENTITY_SWITCH] > 0
        ) {
            $collection->addFieldToFilter(
                'entity_id',
                ['gt' => $this->_parameters[Processor::LAST_ENTITY_ID]]
            );
        }

        if (!isset($this->_parameters[Processor::EXPORT_FILTER_TABLE]) ||
            !is_array($this->_parameters[Processor::EXPORT_FILTER_TABLE])) {
            return;
        }

        foreach ($this->getAdapters() as $adapter) {
            $adapter->prepareEntityCollection(
                $collection,
                $this->_parameters[Processor::EXPORT_FILTER_TABLE]
            );
        }
    }

    /**
     * Retrieve adapters with dependencies
     *
     * @return AbstractAdapter[]
     */
    protected function getAdapters()
    {
        $dependencies = [];
        if (isset($this->_parameters[Processor::BEHAVIOR_DATA]['deps'])) {
            $dependencies = $this->_parameters[Processor::BEHAVIOR_DATA]['deps'];
        }

        return array_filter(
            $this->adapters,
            function (AbstractAdapter $adapter) use ($dependencies) {
                return in_array($adapter->getEntityTypeCode(), $dependencies);
            }
        );
    }
}
