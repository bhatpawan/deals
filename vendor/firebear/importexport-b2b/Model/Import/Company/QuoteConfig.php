<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface;

class QuoteConfig extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = CompanyQuoteConfigInterface::COMPANY_ID;
    const COLUMN_IS_QUOTE_ENABLED = CompanyQuoteConfigInterface::IS_QUOTE_ENABLED;

    public function getAllFields()
    {
        return [
            CompanyQuoteConfigInterface::IS_QUOTE_ENABLED
        ];
    }

    protected function getExistEntityId(array $rowData)
    {
        $select = $this->_connection->select()
            ->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
            ->where(static::COLUMN_ENTITY_ID.' = ?', $this->getCompanyIdFromMap());

        return $this->_connection->fetchOne($select);
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $companyId = $this->getCompanyIdFromMap();
        if ($companyId) {
            $newEntity = !$this->getExistEntityId($rowData);

            $entityRow = [
                static::COLUMN_ENTITY_ID => $companyId
            ];

            $columnIsQuoteEnabled = static::COLUMN_IS_QUOTE_ENABLED;
            if ((isset($rowData[$columnIsQuoteEnabled]) || $newEntity) && empty($rowData[$columnIsQuoteEnabled])) {
                $entityRow[$columnIsQuoteEnabled] = 0;
            } elseif (!empty($rowData[$columnIsQuoteEnabled])) {
                $entityRow[$columnIsQuoteEnabled] = $this->convertToBool($rowData[$columnIsQuoteEnabled]);
            }

            /* prepare data */
            $entityRow = $this->_prepareEntityRow($entityRow, $rowData);
            if ($newEntity) {
                $toCreate[] = $entityRow;
            } else {
                $toUpdate[] = $entityRow;
            }
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    /**
     * @return bool
     */
    public function rowsCanBeSkipped(): bool
    {
        return false;
    }
}
