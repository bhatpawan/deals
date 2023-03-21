<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\RequisitionList;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;

class Item extends AbstractAdapter
{
    protected $tableName = 'requisition_list_item';
    protected $retrieveField = RequisitionListItemInterface::REQUISITION_LIST_ID;
    protected $columnPrefix = 'item';

    protected function _getHeaderColumns()
    {
        return [
            RequisitionListItemInterface::SKU,
            RequisitionListItemInterface::STORE_ID,
            RequisitionListItemInterface::ADDED_AT,
            RequisitionListItemInterface::QTY,
            RequisitionListItemInterface::OPTIONS
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
