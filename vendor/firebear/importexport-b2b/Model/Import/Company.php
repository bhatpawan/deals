<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Import;

class Company extends AbstractEntity
{
    protected function getAdaptersForImport()
    {
        // customer need to go first
        $adapters = $this->getChildren();
        if (isset($adapters['customer'])) {
            $adapters = ['customer' => $adapters['customer']] + $adapters;
        }

        return $adapters;
    }
}
