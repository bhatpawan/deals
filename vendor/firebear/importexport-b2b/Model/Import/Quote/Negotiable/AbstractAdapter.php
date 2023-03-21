<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import\Quote\Negotiable;

use Firebear\ImportExportB2b\Model\Import\Quote\AbstractAdapter as BaseAbstractAdapter;
use Magento\Framework\DB\Select;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote;
use Magento\Quote\Api\Data\CartInterface;

abstract class AbstractAdapter extends BaseAbstractAdapter
{
    /**
     * Quote Table
     *
     * @var string
     */
    protected $_quoteTable = 'quote';

    /**
     * Negotiable Quote Table
     *
     * @var string
     */
    protected $_negotiableQuoteTable = NegotiableQuote::NEGOTIABLE_QUOTE_TABLE;

    /**
     * Retrieve Quote Table Name
     *
     * @return string
     */
    protected function getQuoteTable()
    {
        return $this->_resource->getTableName(
            $this->_quoteTable
        );
    }

    /**
     * Retrieve Negotiable Quote Table Name
     *
     * @return string
     */
    protected function getNegotiableQuoteTable()
    {
        return $this->_resource->getTableName(
            $this->_negotiableQuoteTable
        );
    }

    /**
     * Retrieve Quote By Identifier
     *
     * @param int $id
     * @param array $fields
     * @return bool|array
     */
    protected function getQuote($id, $fields = [])
    {
        $select = $this->_connection->select()
            ->from($this->getQuoteTable(), $fields ?: Select::SQL_WILDCARD)
            ->where(CartInterface::KEY_ENTITY_ID.' = ?', $id);

        return $this->_connection->fetchRow($select);
    }

    /**
     * Retrieve Negotiable Quote By Identifier
     *
     * @param int $id
     * @param array $fields
     * @return bool|array
     */
    protected function getNegotiableQuote($id, $fields = [])
    {
        $select = $this->_connection->select()
            ->from($this->getNegotiableQuoteTable(), $fields ?: Select::SQL_WILDCARD)
            ->where(NegotiableQuoteInterface::QUOTE_ID.' = ?', $id);

        return $this->_connection->fetchRow($select);
    }
}
