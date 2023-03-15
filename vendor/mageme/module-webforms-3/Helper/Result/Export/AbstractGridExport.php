<?php
/**
 * MageMe
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageMe.com license that is
 * available through the world-wide-web at this URL:
 * https://mageme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to a newer
 * version in the future.
 *
 * Copyright (c) MageMe (https://mageme.com)
 **/

namespace MageMe\WebForms\Helper\Result\Export;


use DateTime;
use DateTimeZone;
use Exception;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use MageMe\WebForms\Api\Utility\ExportValueConverterInterface;
use MageMe\WebForms\Model\Result;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;

abstract class AbstractGridExport
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var ResultRepositoryInterface
     */
    protected $resultRepository;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $dateFormat = 'M j, Y h:i:s A';

    /**
     * ConvertResultGrid constructor.
     * @param ResolverInterface $localeResolver
     * @param TimezoneInterface $timezone
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param ResultRepositoryInterface $resultRepository
     * @throws FileSystemException
     */
    public function __construct(
        ResolverInterface         $localeResolver,
        TimezoneInterface         $timezone,
        Filesystem                $filesystem,
        Filter                    $filter,
        MetadataProvider          $metadataProvider,
        ResultRepositoryInterface $resultRepository
    )
    {
        $this->filter           = $filter;
        $this->directory        = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->resultRepository = $resultRepository;
        $this->timezone         = $timezone;
        $this->locale           = $localeResolver->getLocale();
    }

    /**
     * Get Customer name by result id
     * (Fix for customer column)
     *
     * @param int $id
     * @return string
     */
    protected function getCustomerName(int $id): string
    {
        try {

            /** @var Result $result */
            $result = $this->resultRepository->getById($id);
            return $result->getCustomerName();
        } catch (Exception $exception) {
            return '';
        }
    }

    /**
     * Replace newline characters with spaces
     *
     * @param array $arr
     * @return array
     */
    protected function replaceNewlineCharacters(array $arr): array
    {
        for ($i = 0; $i < count($arr); ++$i) {
            if (is_string($arr[$i])) {
                $arr[$i] = str_replace(["\r", "\n"], ' ', $arr[$i]);
            }
        }
        return $arr;
    }


    /**
     * Convert data in result
     *
     * @param UiComponentInterface $component
     * @param DocumentInterface|DataObject $item
     * @param array $fields
     * @throws Exception
     */
    protected function convertData(UiComponentInterface $component, DocumentInterface $item, array $fields)
    {
        $columns = $this->getColumns($component);
        foreach ($fields as $fieldName) {
            $fieldValue = $item->getData($fieldName);

            if($fieldName == 'customer'){
                $fieldValue = $item->getData();
            }

            if (is_array($fieldValue)) {
                $item->setData($fieldName, json_encode($fieldValue));
            }

            if ($columns[$fieldName] instanceof ExportValueConverterInterface) {
                $item->setData($fieldName, $columns[$fieldName]->convertExportValue($fieldValue));
            }
        }
    }

    /**
     * Return grid columns
     *
     * @param UiComponentInterface $component
     * @return array
     * @throws Exception
     */
    protected function getColumns(UiComponentInterface $component): array
    {
        $columns          = [];
        $columnsComponent = $this->getColumnsComponent($component);
        foreach ($columnsComponent->getChildComponents() as $column) {
            if ($column->getData('config/label') && $column->getData('config/dataType') !== 'actions') {
                $columns[$column->getName()] = $column;
            }
        }
        return $columns;
    }

    /**
     * Returns Columns component
     *
     * @param UiComponentInterface $component
     *
     * @return UiComponentInterface
     * @throws Exception
     */
    protected function getColumnsComponent(UiComponentInterface $component): UiComponentInterface
    {
        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof Columns) {
                return $childComponent;
            }
        }
        throw new Exception('No columns found');
    }

    /**
     * Convert date in result
     *
     * @param string $fieldValue
     * @return string
     * @throws Exception
     */
    protected function convertDate(string $fieldValue): string
    {
        $convertedDate = $this->timezone->date(
            new DateTime($fieldValue, new DateTimeZone('UTC')),
            $this->locale,
            true
        );
        return $convertedDate->format($this->dateFormat);
    }
}
