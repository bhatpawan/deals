<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\RequisitionList;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;

class Entity extends AbstractAdapter
{
    protected $tableName = 'requisition_list';
    protected $retrieveField = RequisitionListInterface::REQUISITION_LIST_ID;

    protected function _getHeaderColumns()
    {
        return [
            RequisitionListInterface::CUSTOMER_ID,
            RequisitionListInterface::NAME,
            RequisitionListInterface::DESCRIPTION,
            RequisitionListInterface::UPDATED_AT
        ];
    }

    /**
     * Describe the table
     *
     * @return array
     */
    protected function describeTable()
    {
        return $this->resource->getConnection()->describeTable(
            $this->getTableName()
        );
    }
}
