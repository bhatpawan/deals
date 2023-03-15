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


use Exception;
use MageMe\WebForms\Api\ResultRepositoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;

class GridToCsvExport extends AbstractGridExport
{
    /**
     * @var int|null
     */
    protected $pageSize = null;

    /**
     * ConvertResultGridToCsv constructor.
     * @param ResolverInterface $localeResolver
     * @param TimezoneInterface $timezone
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param ResultRepositoryInterface $resultRepository
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        ResolverInterface         $localeResolver,
        TimezoneInterface         $timezone,
        Filesystem                $filesystem,
        Filter                    $filter,
        MetadataProvider          $metadataProvider,
        ResultRepositoryInterface $resultRepository,
        int                       $pageSize = 200
    )
    {
        parent::__construct($localeResolver, $timezone, $filesystem, $filter, $metadataProvider, $resultRepository);
        $this->pageSize = $pageSize;
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    public function getCsvFile(): array
    {
        $component = $this->filter->getComponent();

        $name = md5((string)microtime());
        $file = 'export/' . $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields       = $this->metadataProvider->getFields($component);
        $options      = $this->metadataProvider->getOptions();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($this->metadataProvider->getHeaders($component));
        $i              = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount     = $dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
            $items = $dataProvider->getSearchResult()->getItems();
            foreach ($items as $item) {
                $this->convertData($component, $item, $fields);
                $row = $this->metadataProvider->getRowData($item, $fields, $options);
                $row    = $this->replaceNewlineCharacters($row);

                $stream->writeCsv($row);
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}
