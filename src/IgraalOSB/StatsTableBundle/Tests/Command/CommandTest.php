<?php

namespace IgraalOSB\StatsTableBundle\Tests\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @group trait
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    private $tempfile;


    protected function setUp()
    {
        $this->tempfile = tempnam(sys_get_temp_dir(), 'tmp');
    }

    protected function tearDown()
    {
        file_exists($this->tempfile) && unlink($this->tempfile);
    }

    /**
     * @return Command
     */
    public function getCommand()
    {
        return new TraitTestCommand();
    }

    public function testDefinition()
    {
        $command = $this->getCommand();
        $definition = $command->getDefinition();

        $this->assertTrue($definition->hasOption('xls'));
        $this->assertTrue($definition->hasOption('csv'));
        $this->assertTrue($definition->hasOption('html'));
        $this->assertFalse($definition->hasOption('unknown'));
    }

    public function testCSV()
    {
        $command = $this->getCommand();
        $input = new StringInput('--csv=-');
        $output = new BufferedOutput();

        $command->run($input, $output);
        $contents = $output->fetch();
        $this->assertEquals("Zero,One\n0,1", trim($contents));
    }

    public function testXLS()
    {
        $command = $this->getCommand();
        $input = new StringInput('--xls='.$this->tempfile);
        $output = new BufferedOutput();

        $command->run($input, $output);
        $contents = $output->fetch();

        $this->assertEquals('', trim($contents));
        $xlsContent = file_get_contents($this->tempfile);
        $this->assertGreaterThan(2, strlen($xlsContent));
        $this->assertEquals('PK', substr($xlsContent, 0, 2));
    }

    public function testHTML()
    {
        $command = $this->getCommand();
        $input = new StringInput('--html=-');
        $output = new BufferedOutput();

        $command->run($input, $output);
        $contents = $output->fetch();
        $this->assertContains('<table>', $contents);
    }
}
