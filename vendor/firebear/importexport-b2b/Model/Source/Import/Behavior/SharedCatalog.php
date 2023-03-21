<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Source\Import\Behavior;

/**
 * Shared Catalog Behavior Import
 *
 */
class SharedCatalog extends AbstractB2bBehavior
{
    /**
     * Get Current Behaviour Group Code
     *
     * @return string
     */
    public function getCode()
    {
        return 'shared_catalog';
    }
}
