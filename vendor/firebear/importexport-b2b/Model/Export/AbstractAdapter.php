<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export;

use Firebear\ImportExport\Model\Export\EntityInterface;
use Firebear\ImportExport\Helper\Data as Helper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @method $this setEntityTypeCode(string $entityTypeCode)
 * @method string getEntityTypeCode()
 * @method $this setLabel(string $label)
 * @method string getLabel()
 */
abstract class AbstractAdapter extends DataObject implements EntityInterface
{
    /**
     * Entity type code for adapter
     *
     * @var string
     */
    protected $entityTypeCode;

    /**
     * Label for output
     *
     * @var string
     */
    protected $label;

    /**
     * Table name for adapter
     *
     * @var string
     */
    protected $tableName;

    /**
     * Retrieve field in dependent table
     *
     * @var string
     */
    protected $retrieveField;

    /**
     * Relation field in main table
     *
     * @var string
     */
    protected $relationField = 'entity_id';

    /**
     * Column prefix for table
     *
     * @var string
     */
    protected $columnPrefix = '';

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ResourceConnection $resource
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resource,
        Helper $helper
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection('checkout');
        $this->helper = $helper;
    }

    public function getFieldColumns()
    {
        $columns = [];
        foreach ($this->describeTable() as $field) {
            $type = $this->helper->convertTypesTables($field['DATA_TYPE']);
            $select = $this->getSelectForField($field['COLUMN_NAME']);

            $preparedSelect = [];
            foreach ($select as $value => $label) {
                $preparedSelect[] = [
                    'value' => $value,
                    'label' => $label
                ];
            }

            $columns[] = [
                'field' => $field['COLUMN_NAME'],
                'type' => $select ? 'select' : $type,
                'select' => $preparedSelect
            ];
        }

        return $columns;
    }

    public function getFieldsForFilter()
    {
        $fields = [];
        foreach (array_keys($this->describeTable()) as $field) {
            $fields[] = [
                'label' => $field,
                'value' => $field
            ];
        }

        return $fields;
    }

    public function getFieldsForExport()
    {
        $fields = [
            'label' => $this->getLabel(),
            'optgroup-name' => $this->getEntityTypeCode(),
            'value' => []
        ];
        foreach ($this->getHeaderColumns() as $value => $label) {
            $fields['value'][] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $fields;
    }

    /**
     * Get header columns
     *
     * @return array
     */
    public function getHeaderColumns()
    {
        $columns = $this->_getHeaderColumns();
        $prefix = $this->columnPrefix ? $this->columnPrefix.':' : '';

        $result = [];
        foreach ($columns as $column) {
            $result[$prefix.$column] = $column;
        }

        return $result;
    }

    /**
     * Export one item
     *
     * @param AbstractModel $item
     * @return array
     */
    public function exportItem($item)
    {
        $select = $this->connection->select()
            ->from($this->getTableName(), $this->getHeaderColumns())
            ->where($this->retrieveField.' = ?', $item->getData($this->relationField));

        return array_values($this->connection->fetchAssoc($select));
    }

    /**
     * Apply filter to collection
     *
     * @param AbstractDb $collection
     * @param array $exportFilter
     */
    public function prepareEntityCollection(
        AbstractDb $collection,
        array $exportFilter
    ) {
        $columns = [];
        foreach ($this->getFieldColumns() as $column) {
            $columns[$column['field']] = $column;
        }

        $adapterFilter = array_filter($exportFilter, function ($filter) use ($columns) {
            return ($filter['entity'] == $this->getEntityTypeCode()) && isset($columns[$filter['field']]);
        });

        if ($adapterFilter) {
            $select = $this->connection->select()
                ->from($this->getTableName(), $this->retrieveField);

            foreach ($adapterFilter as $filter) {
                $field = $filter['field'];
                $value = $filter['value'];

                switch ($columns[$field]['type']) {
                    case 'int':
                        if (is_array($value) && (count($value) == 2)) {
                            $from = array_shift($value);
                            $to = array_shift($value);

                            if (is_numeric($from)) {
                                $select->where($field.' >= ?', $from);
                            }
                            if (is_numeric($to)) {
                                $select->where($field.' <= ?', $to);
                            }
                        }
                        break;
                    case 'text':
                        if (is_string($value) && strlen($value)) {
                            $select->where($field.' like ?', '%'.trim($value).'%');
                        } elseif (is_array($value) && (count($value) == 2)) {
                            $from = array_shift($value);
                            $to = array_shift($value);
                            if (is_numeric($from) && is_numeric($to)) {
                                $select->where($field.' >= ?', $from);
                                $select->where($field.' <= ?', $to);
                            } else {
                                $select->where($field . ' = ?', false);
                            }
                        }
                        break;
                    case 'date':
                        if (is_array($value) && (count($value) == 2)) {
                            $from = array_shift($value);
                            $to = array_shift($value);

                            if (is_scalar($from) && strlen($from)) {
                                $date = (new \DateTime($from))->format('m/d/Y');
                                $select->where($field.' >= ?', $date);
                            }
                            if (is_scalar($to) && strlen($to)) {
                                $date = (new \DateTime($to))->format('m/d/Y');
                                $select->where($field.' <= ?', $date);
                            }
                        }
                        break;
                    case 'select':
                        if (is_string($value) && strlen($value)) {
                            $fieldSelect = $this->getSelectForField($field);
                            if (isset($fieldSelect[$value])) {
                                $select->where($field.' = ?', $value);
                            }
                        }
                        break;
                }
            }

            $collection->addFieldToFilter(
                $this->relationField,
                ['in' => $this->connection->fetchCol($select)]
            );
        }
    }

    /**
     * Retrieve table name
     *
     * @return string
     */
    protected function getTableName()
    {
        return $this->resource->getTableName(
            $this->tableName
        );
    }

    /**
     * Describe the table
     *
     * @return array
     */
    protected function describeTable()
    {
        return $this->connection->describeTable(
            $this->getTableName()
        );
    }

    /**
     * Get header columns
     *
     * @return array
     */
    protected function _getHeaderColumns()
    {
        return array_keys($this->describeTable());
    }

    /**
     * Retrieve select array for particular column
     *
     * @param string $columnName
     * @return array
     */
    protected function getSelectForField($columnName)
    {
        return [];
    }
}
