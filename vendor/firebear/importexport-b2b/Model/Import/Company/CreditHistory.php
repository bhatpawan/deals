<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Company;

use Magento\Authorization\Model\UserContextInterface;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @method UserContextInterface getDataUserContext
 * @method SerializerInterface getDataSerializer
 */
class CreditHistory extends AbstractAdapter
{
    /**
     * Column Names
     */
    const COLUMN_ENTITY_ID = HistoryInterface::HISTORY_ID;
    const COLUMN_COMPANY_CREDIT_ID = HistoryInterface::COMPANY_CREDIT_ID;
    const COLUMN_USER_ID = HistoryInterface::USER_ID;
    const COLUMN_USER_TYPE = HistoryInterface::USER_TYPE;
    const COLUMN_CURRENCY_CREDIT = HistoryInterface::CURRENCY_CREDIT;
    const COLUMN_CURRENCY_OPERATION = HistoryInterface::CURRENCY_OPERATION;
    const COLUMN_RATE = HistoryInterface::RATE;
    const COLUMN_BALANCE = HistoryInterface::BALANCE;
    const COLUMN_CREDIT_LIMIT = HistoryInterface::CREDIT_LIMIT;
    const COLUMN_AVAILABLE_CREDIT = HistoryInterface::AVAILABLE_CREDIT;
    const COLUMN_TYPE = HistoryInterface::TYPE;
    const COLUMN_COMMENT = HistoryInterface::COMMENT;

    public function getAllFields()
    {
        return [
            CreditLimitInterface::CREDIT_COMMENT
        ];
    }

    /**
     * Get Credit By Identifier
     *
     * @param $creditId
     * @return array|bool
     */
    protected function getCreditById($creditId)
    {
        $column = static::COLUMN_COMPANY_CREDIT_ID;
        $foreignKeys = $this->_connection->getForeignKeys(
            $this->getMainTable()
        );

        foreach ($foreignKeys as $key) {
            if ($key['COLUMN_NAME'] == $column) {
                $queryColumn = $key['REF_COLUMN_NAME'];
                $queryTable = $this->_resource->getTableName($key['REF_TABLE_NAME']);

                $bind = [':'.$queryColumn => $creditId];
                $select = $this->_connection->select()
                    ->from($queryTable)
                    ->where($queryColumn.' = :'.$queryColumn);

                return $this->_connection->fetchRow($select, $bind);
            }
        }

        return false;
    }

    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        $creditId = $this->getCreditIdFromMap();
        if ($creditId) {
            $credit = $this->getCreditById($creditId);

            $comment = [];
            if (!empty($rowData[CreditLimitInterface::CREDIT_COMMENT])) {
                $comment['custom'] = $rowData[CreditLimitInterface::CREDIT_COMMENT];
            }

            $availableCredit = $credit[CreditLimitInterface::BALANCE] + $credit[CreditLimitInterface::CREDIT_LIMIT];
            $userContext = $this->getDataUserContext();

            $toCreate[] = [
                static::COLUMN_ENTITY_ID => $this->_getNextEntityId(),
                static::COLUMN_COMPANY_CREDIT_ID => $creditId,
                static::COLUMN_USER_ID => $userContext->getUserId(),
                static::COLUMN_USER_TYPE => $userContext->getUserType() ?: UserContextInterface::USER_TYPE_ADMIN,
                static::COLUMN_CURRENCY_CREDIT => $credit[CreditLimitInterface::CURRENCY_CODE],
                static::COLUMN_CURRENCY_OPERATION => $credit[CreditLimitInterface::CURRENCY_CODE],
                static::COLUMN_BALANCE => $credit[CreditLimitInterface::BALANCE],
                static::COLUMN_CREDIT_LIMIT => $credit[CreditLimitInterface::CREDIT_LIMIT] ?: 0,
                static::COLUMN_AVAILABLE_CREDIT => $availableCredit,
                static::COLUMN_TYPE => HistoryInterface::TYPE_UPDATED,
                static::COLUMN_COMMENT => $this->getDataSerializer()->serialize($comment)
            ];
        }

        return [
            static::ENTITIES_TO_CREATE_KEY => $toCreate,
            static::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    /**
     * @return bool
     */
    public function rowsCanBeSkipped() :bool
    {
        return false;
    }
}
