<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Source\Import\Behavior;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Source\Import\AbstractBehavior;

/**
 * Abstract B2b Behavior Import
 *
 */
abstract class AbstractB2bBehavior extends AbstractBehavior
{
    /**
     * Get Array Of Possible Values
     *
     * @return array
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_ADD_UPDATE => __('Add/Update'),
            Import::BEHAVIOR_REPLACE => __('Replace'),
            Import::BEHAVIOR_DELETE => __('Delete')
        ];
    }
}
