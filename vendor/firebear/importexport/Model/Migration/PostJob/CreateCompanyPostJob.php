<?php
/**
 * @copyright: Copyright © 2020 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Migration\PostJob;

use Firebear\ImportExport\Model\Migration\Config;
use Firebear\ImportExport\Model\Migration\DbConnection;
use Firebear\ImportExport\Model\Migration\PostJobInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\Company as ModelCompany;
use Magento\Company\Model\CompanyRepository;
use Magento\Company\Model\Customer\Company;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

/**
 * @package Firebear\ImportExport\Model\Migration\PostJob
 */
class CreateCompanyPostJob implements PostJobInterface
{
    /**
     * @var DbConnection
     */
    protected $connector;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Company
     */
    protected $company;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ModelCompany
     */
    protected $modelCompany;

    /**
     * @var CompanyRepository
     */
    protected $companyRepo;

    /**
     * @param DbConnection $connector
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     * @param string $table
     * @param Manager $moduleManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        DbConnection $connector,
        Config $config,
        CustomerRepositoryInterface $customerRepository,
        string $table,
        Manager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->connector = $connector;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->table = $table;
        if ($moduleManager->isEnabled('Magento_Company')
            && class_exists(Company::class)
            && class_exists(ModelCompany::class)
            && class_exists(CompanyRepository::class)
        ) {
            $this->company = $objectManager->create(Company::class);
            $this->modelCompany = $objectManager->create(ModelCompany::class);
            $this->companyRepo = $objectManager->create(CompanyRepository::class);
        }
    }

    /**
     * @inheritdoc
     */
    public function job()
    {
        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->connector->getDestinationChannel()->truncateTable(
            $this->config->getM2Prefix() . 'company'
        );
        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 1;');

        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->connector->getDestinationChannel()->truncateTable(
            $this->config->getM2Prefix() . 'company_advanced_customer_entity'
        );
        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 1;');

        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->connector->getDestinationChannel()->truncateTable(
            $this->config->getM2Prefix() . 'company_credit'
        );
        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 1;');

        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->connector->getDestinationChannel()->truncateTable(
            $this->config->getM2Prefix() . 'company_payment'
        );
        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 1;');

        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->connector->getDestinationChannel()->truncateTable(
            $this->config->getM2Prefix() . 'company_roles'
        );
        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 1;');

        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->connector->getDestinationChannel()->truncateTable(
            $this->config->getM2Prefix() . 'company_structure'
        );
        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 1;');

        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->connector->getDestinationChannel()->truncateTable(
            $this->config->getM2Prefix() . 'company_permissions'
        );
        $this->connector->getDestinationChannel()->query('SET FOREIGN_KEY_CHECKS = 1;');

        $select = $this->connector->getDestinationChannel()
            ->select()
            ->from($this->config->getM2Prefix() . $this->table);

        $data = $this->connector->getDestinationChannel()->query($select)->fetchAll();
        if (class_exists(\Magento\Company\Api\Data\CompanyInterface::class)) {
            $companyInterface = \Magento\Company\Api\Data\CompanyInterface::class;
            foreach ($data as $item) {
                if (isset($item['company'])) {
                    $customerModel = $this->customerRepository->getById($item['parent_id']);
                    $companyArray = [
                        $companyInterface::CITY => $item[$companyInterface::CITY],
                        $companyInterface::COMPANY_EMAIL => $customerModel->getEmail(),
                        $companyInterface::COUNTRY_ID => $item[$companyInterface::COUNTRY_ID],
                        $companyInterface::CUSTOMER_GROUP_ID => 1,
                        $companyInterface::FIRSTNAME => $item[$companyInterface::FIRSTNAME],
                        $companyInterface::LASTNAME => $item[$companyInterface::LASTNAME],
                        $companyInterface::GENDER => $customerModel->getGender(),
                        $companyInterface::NAME => $item['company'],
                        $companyInterface::TELEPHONE => $item[$companyInterface::TELEPHONE],
                        $companyInterface::SALES_REPRESENTATIVE_ID => 1,
                        $companyInterface::POSTCODE => $item[$companyInterface::POSTCODE],
                        $companyInterface::VAT_TAX_ID => $customerModel->getTaxvat(),
                        $companyInterface::SUPER_USER_ID => $customerModel->getId(),
                        $companyInterface::REGION => $item[$companyInterface::REGION],
                        $companyInterface::REGION_ID => $item[$companyInterface::REGION_ID],
                        $companyInterface::MIDDLENAME => $item[$companyInterface::MIDDLENAME],
                        $companyInterface::STREET => $item[$companyInterface::STREET],
                        $companyInterface::PREFIX => $item[$companyInterface::PREFIX],
                        $companyInterface::STATUS => $item['is_active'],
                        $companyInterface::JOB_TITLE => 'default'
                    ];
                    $this->company->createCompany($customerModel, $companyArray);
                }
            }
        }
    }
}
