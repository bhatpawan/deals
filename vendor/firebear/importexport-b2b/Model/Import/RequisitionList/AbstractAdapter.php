<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\RequisitionList;

use Firebear\ImportExport\Model\Import\Context;
use Firebear\ImportExportB2b\Model\Import\AbstractAdapter as BaseAbstractAdapter;
use Magento\Framework\Model\ResourceModel\AbstractResource;

abstract class AbstractAdapter extends BaseAbstractAdapter
{
    /**
     * Increment Variables
     *
     * @var int
     */
    protected $incrementRequisitionListId = 0;

    /**
     * Storage For Maps
     *
     * @var Storage
     */
    protected $storage;

    /**
     * @param Context $context
     * @param AbstractResource $resourceModel
     * @param Storage $storage
     * @param array $data
     */
    public function __construct(
        Context $context,
        AbstractResource $resourceModel,
        Storage $storage,
        array $data = []
    ) {
        parent::__construct($context, $resourceModel, $data);

        $this->storage = $storage
            ->setRequisitionListIdsMap([]);
    }

    public function prepareRowData(array $rowData)
    {
        $extractRequisitionListRow = $this->_extractField($rowData, '');
        if (array_filter($extractRequisitionListRow)) {
            $this->incrementRequisitionListId++;
        }

        return parent::prepareRowData($rowData);
    }
}
