<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

use Firebear\ImportExport\Model\Import\Context;
use Firebear\ImportExportB2b\Model\Import\AbstractAdapter as BaseAbstractAdapter;
use Magento\Framework\Model\ResourceModel\AbstractResource;

abstract class AbstractAdapter extends BaseAbstractAdapter
{
    /**
     * Storage for maps
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
            ->setCompanyIdsMap([])
            ->setCustomerIdsMap([])
            ->setCreditIdsMap([]);
    }

    /**
     * Retrieve Company Id If Company Is Present In Database
     *
     * @return bool|int
     */
    protected function getCompanyIdFromMap()
    {
        $companyIdsMap = $this->storage->getCompanyIdsMap();
        if (isset($companyIdsMap[$this->_processedRowsCount])) {
            return $companyIdsMap[$this->_processedRowsCount];
        }

        return false;
    }

    /**
     * Retrieve Customer Id If Customer Present In Database
     *
     * @return bool|int
     */
    protected function getCustomerIdFromMap()
    {
        $customerIdsMap = $this->storage->getCustomerIdsMap();
        if (isset($customerIdsMap[$this->_processedRowsCount])) {
            return $customerIdsMap[$this->_processedRowsCount];
        }

        return false;
    }

    /**
     * Retrieve Old Customer Id If Customer Present In Database
     *
     * @return bool|int
     */
    protected function getOldCustomerIdFromMap()
    {
        $oldCustomerIdsMap = $this->storage->getOldCustomerIdsMap();
        if (isset($oldCustomerIdsMap[$this->_processedRowsCount])) {
            return $oldCustomerIdsMap[$this->_processedRowsCount];
        }

        return false;
    }

    /**
     * Retrieve Credit Id If Credit Present In Database
     *
     * @return bool|int
     */
    protected function getCreditIdFromMap()
    {
        $creditIdsMap = $this->storage->getCreditIdsMap();
        if (isset($creditIdsMap[$this->_processedRowsCount])) {
            return $creditIdsMap[$this->_processedRowsCount];
        }

        return false;
    }
}
