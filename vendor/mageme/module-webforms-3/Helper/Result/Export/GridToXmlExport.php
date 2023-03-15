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
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;

class GridToXmlExport extends AbstractGridExport
{
    /**
     * @var ExcelFactory
     */
    protected $excelFactory;

    /**
     * @var SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * ConvertResultGridToXml constructor.
     * @param SearchResultIteratorFactory $iteratorFactory
     * @param ExcelFactory $excelFactory
     * @param ResolverInterface $localeResolver
     * @param TimezoneInterface $timezone
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param ResultRepositoryInterface $resultRepository
     * @throws FileSystemException
     */
    public function __construct(
        SearchResultIteratorFactory $iteratorFactory,
        ExcelFactory                $excelFactory,
        ResolverInterface           $localeResolver,
        TimezoneInterface           $timezone,
        Filesystem                  $filesystem,
        Filter                      $filter,
        MetadataProvider            $metadataProvider,
        ResultRepositoryInterface   $resultRepository
    )
    {
        parent::__construct($localeResolver, $timezone, $filesystem, $filter, $metadataProvider, $resultRepository);
        $this->excelFactory    = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @return array
     * @throws LocalizedException
     */
    public function getRowData(DocumentInterface $document): array
    {
        $row = $this->metadataProvider->getRowData($document, $this->getFields(), $this->getOptions());
        return $this->replaceNewlineCharacters($row);
    }

    /**
     * Returns DB fields list
     *
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    protected function getFields(): array
    {
        if (!$this->fields) {
            $component    = $this->filter->getComponent();
            $this->fields = $this->metadataProvider->getFields($component);
        }
        return $this->fields;
    }

    /**
     * Returns Filters with options
     *
     * @return array
     * @throws LocalizedException
     */
    protected function getOptions(): array
    {
        if (!$this->options) {
            $this->options = $this->metadataProvider->getOptions();
        }
        return $this->options;
    }

    /**
     * Returns XML file
     *
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    public function getXmlFile(): array
    {
        $component = $this->filter->getComponent();
        $name      = md5((string)microtime());
        $file      = 'export/' . $component->getName() . $name . '.xml';
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $component->getContext()->getDataProvider()->setLimit(0, 0);
        $searchResult      = $component->getContext()->getDataProvider()->getSearchResult();
        $searchResultItems = $searchResult->getItems();
        $this->prepareItems($component, $searchResultItems);
        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItems]);
        $excel                = $this->excelFactory->create(
            [
                'iterator' => $searchResultIterator,
                'rowCallback' => [$this, 'getRowData'],
            ]
        );
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($this->metadataProvider->getHeaders($component));
        $excel->write($stream, $component->getName() . '.xml');

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * @param UiComponentInterface $component
     * @param array $items
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    protected function prepareItems(UiComponentInterface $component, array $items = [])
    {
        foreach ($items as $item) {
            $this->convertData($component, $item, $this->getFields());
        }
    }
}
