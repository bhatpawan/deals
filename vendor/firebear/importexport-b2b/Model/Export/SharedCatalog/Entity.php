<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\SharedCatalog;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

class Entity extends AbstractAdapter
{
    protected $tableName = 'shared_catalog';
    protected $retrieveField = SharedCatalogInterface::SHARED_CATALOG_ID;

    protected function _getHeaderColumns()
    {
        return [
            SharedCatalogInterface::NAME,
            SharedCatalogInterface::DESCRIPTION,
            SharedCatalogInterface::TYPE
        ];
    }

    protected function getSelectForField($columnName)
    {
        if ($columnName == SharedCatalogInterface::TYPE) {
            return [
                SharedCatalogInterface::TYPE_PUBLIC => __('Public'),
                SharedCatalogInterface::TYPE_CUSTOM => __('Custom')
            ];
        }

        return parent::getSelectForField($columnName);
    }
}
