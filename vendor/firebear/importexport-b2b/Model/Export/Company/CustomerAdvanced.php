<?php
/**
 * @copyright: Copyright © 2019 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExportB2b\Model\Export\Company;

use Firebear\ImportExportB2b\Model\Export\AbstractAdapter;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;

class CustomerAdvanced extends AbstractAdapter
{
    protected $tableName = 'company_advanced_customer_entity';
    protected $retrieveField = CompanyCustomerInterface::CUSTOMER_ID;
    protected $relationField = CompanyInterface::SUPER_USER_ID;

    protected function _getHeaderColumns()
    {
        return [
            CompanyCustomerInterface::JOB_TITLE
        ];
    }
}
