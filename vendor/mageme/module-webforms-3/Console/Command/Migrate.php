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

namespace MageMe\WebForms\Console\Command;

use MageMe\WebForms\Helper\ConvertVersionHelper;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends Command
{
    /**
     * @var ConvertVersionHelper
     */
    protected $convertVersionHelper;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @var State
     */
    protected $state;

    /**
     * Migrate constructor.
     * @param State $state
     * @param ModuleDataSetupInterface $setup
     * @param ConvertVersionHelper $convertVersionHelper
     * @param string|null $name
     */
    public function __construct(
        State                    $state,
        ModuleDataSetupInterface $setup,
        ConvertVersionHelper     $convertVersionHelper,
        string                   $name = null)
    {
        parent::__construct($name);
        $this->setup                = $setup;
        $this->convertVersionHelper = $convertVersionHelper;
        $this->state                = $state;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('webforms:migrate');
        $this->setDescription('Migrate forms and submissions from WebForms v1 and v2.');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws LocalizedException
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_FRONTEND);
        $output->writeln('<info>Started data migration.</info>');
        try {
            $this->convertVersionHelper->convertDataToV3($this->setup);
        } catch (CouldNotSaveException | LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
        $output->writeln('<info>Data migration has finished.</info>');
    }
}
