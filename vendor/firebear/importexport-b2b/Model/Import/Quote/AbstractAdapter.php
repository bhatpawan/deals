<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote;

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
    protected $incrementQuoteId = 0;
    protected $incrementItemId = 0;
    protected $incrementCommentId = 0;

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
            ->setQuoteIdsMap([])
            ->setItemIdsMap([])
            ->setCommentIdsMap([]);
    }

    public function prepareRowData(array $rowData)
    {
        $extractQuoteRow = $this->_extractField($rowData, '');
        if (array_filter($extractQuoteRow)) {
            $this->incrementQuoteId++;
        }

        $extractItemRow = $this->_extractField($rowData, 'item');
        if (array_filter($extractItemRow)) {
            $this->incrementItemId++;
        }

        $extractCommentRow = $this->_extractField($rowData, 'negotiable_comment');
        if (array_filter($extractCommentRow)) {
            $this->incrementCommentId++;
        }

        return parent::prepareRowData($rowData);
    }

    /**
     * Retrieve Quote Id If Quote Present In Database
     *
     * @return bool|int
     */
    protected function getQuoteIdFromMap()
    {
        $quoteIdsMap = $this->storage->getQuoteIdsMap();
        if (isset($quoteIdsMap[$this->incrementQuoteId])) {
            return $quoteIdsMap[$this->incrementQuoteId];
        }

        return false;
    }

    /**
     * Retrieve Item Id If Item Present In Database
     *
     * @return bool|int
     */
    protected function getItemIdFromMap()
    {
        $itemIdsMap = $this->storage->getItemIdsMap();
        if (isset($itemIdsMap[$this->incrementItemId])) {
            return $itemIdsMap[$this->incrementItemId];
        }

        return false;
    }

    /**
     * Retrieve Comment Id If Comment Present In Database
     *
     * @return bool|int
     */
    protected function getCommentIdFromMap()
    {
        $commentIdsMap = $this->storage->getCommentIdsMap();
        if (isset($commentIdsMap[$this->incrementCommentId])) {
            return $commentIdsMap[$this->incrementCommentId];
        }

        return false;
    }
}
