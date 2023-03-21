<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Source\Import\Behavior;

/**
 * Company Role Behavior Import
 *
 */
class CompanyRole extends AbstractB2bBehavior
{
    /**
     * Get Current Behaviour Group Code
     *
     * @return string
     */
    public function getCode()
    {
        return 'company_role';
    }
}
