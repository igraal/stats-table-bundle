<?php

namespace IgraalOSB\StatsTableBundle\Command;

use IgraalOSL\StatsTable\Dumper\CSV\CSVDumper;
use IgraalOSL\StatsTable\Dumper\Excel\ExcelDumper;
use IgraalOSL\StatsTable\Dumper\HTML\HTMLDumper;
use IgraalOSL\StatsTable\Dumper\JSON\JSONDumper;
use IgraalOSL\StatsTable\StatsTable;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

trait StatsTableCommandTraits
{
    public function stConfigure()
    {
        $this
            ->addOption('xls', null, InputOption::VALUE_REQUIRED, 'Dump to Excel file')
            ->addOption('csv', null, InputOption::VALUE_REQUIRED, 'Dump to CSV file')
            ->addOption('html', null, InputOption::VALUE_REQUIRED, 'Dump to HTML file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $statsTable = $this->doExecute($input, $output);
        if (!($statsTable instanceof StatsTable)) {
            throw new RuntimeException('StatsTable expected');
        }

        $dumper = null;
        if ($filename = $input->getOption('xls')) {
            $excelDumperOptions = array();
            if (method_exists($this, 'getExcelDumperOptions')) {
                $excelDumperOptions = $this->getExcelDumperOptions();
            }

            $dumper = new ExcelDumper($excelDumperOptions);
        } elseif ($filename = $input->getOption('csv')) {
            $csvDumperOptions = array();
            if (method_exists($this, 'getCSVDumperOptions')) {
                $csvDumperOptions = $this->getCSVDumperOptions();
            }

            $dumper = new CSVDumper($csvDumperOptions);
        } elseif ($filename = $input->getOption('html')) {
            $htmlDumperOptions = array();
            if (method_exists($this, 'getHTMLDumperOptions')) {
                $htmlDumperOptions = $this->getHTMLDumperOptions();
            }

            $dumper = new HTMLDumper($htmlDumperOptions);
        }

        if (null !== $dumper) {
            $contents = $dumper->dump($statsTable);
            if ($filename == '-') {
                $output->write($contents);
            } else {
                file_put_contents($filename, $contents);
            }
        } else {
            $tableHelper = new TableHelper();
            $tableHelper->setHeaders($statsTable->getHeaders());
            // Dump from CSV
            $dumper = new CSVDumper();
            $dumper->enableHeaders(false);
            $dumper->enableAggregation(false);

            $data = $dumper->dump($statsTable);
            $fp = fopen('php://temp', 'rw');
            fwrite($fp, $data);
            fseek($fp, 0, SEEK_SET);
            while ($line = fgetcsv($fp)) {
                $tableHelper->addRow($line);
            }

            if ($statsTable->getAggregations()) {
                $tableHelper->addRow($statsTable->getAggregations());
            }
            $tableHelper->render($output);
        }
    }

    abstract protected function doExecute(InputInterface $input, OutputInterface $output);
}
