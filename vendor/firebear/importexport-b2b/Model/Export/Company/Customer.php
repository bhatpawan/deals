<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Company;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class Customer extends AbstractAdapter
{
    protected $tableName = 'customer_entity';
    protected $retrieveField = 'entity_id';
    protected $relationField = CompanyInterface::SUPER_USER_ID;

    protected function _getHeaderColumns()
    {
        return [
            CustomerInterface::WEBSITE_ID,
            CustomerInterface::EMAIL,
            CustomerInterface::PREFIX,
            CustomerInterface::FIRSTNAME,
            CustomerInterface::MIDDLENAME,
            CustomerInterface::LASTNAME,
            CustomerInterface::SUFFIX,
            CustomerInterface::GENDER
        ];
    }

    protected function getSelectForField($columnName)
    {
        if ($columnName == CustomerInterface::GENDER) {
            return [
                1 => __('Male'),
                2 => __('Female'),
                3 => __('Not Specified')
            ];
        }

        return parent::getSelectForField($columnName);
    }

    /**
     * Retrieve customer field for filter
     *
     * @return array
     */
    public function getFieldsForFilter()
    {
        $fields = [];
        foreach (array_keys($this->describeTable()) as $field) {
            if ($field == 'increment_id') {
                continue;
            }
            $fields[] = [
                'label' => $field,
                'value' => $field
            ];
        }

        return $fields;
    }

    /**
     * Apply filter to collection
     *
     * @param AbstractDb $collection
     * @param array $exportFilter
     * @throws \Exception
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
                        }
                        break;
                    case 'date':
                        if (is_array($value) && (count($value) == 2)) {
                            $from = array_shift($value);
                            $to = array_shift($value);

                            if (is_scalar($from) && strlen($from)) {
                                $date = (new \DateTime($from))->format('Y/m/d');
                                $select->where($field.' >= ?', $date);
                            }
                            if (is_scalar($to) && strlen($to)) {
                                $date = (new \DateTime($to))->format('Y/m/d');
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
}
