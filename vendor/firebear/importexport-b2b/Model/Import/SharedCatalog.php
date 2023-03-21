<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import;

class SharedCatalog extends AbstractEntity
{
    protected function getAdaptersForImport()
    {
        // customer group need to go first
        $adapters = $this->getChildren();
        if (isset($adapters['customerGroup'])) {
            $adapters = ['customerGroup' => $adapters['customerGroup']] + $adapters;
        }

        return $adapters;
    }
}
