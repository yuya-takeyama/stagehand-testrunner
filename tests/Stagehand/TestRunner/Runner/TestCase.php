<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009-2010 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2009-2010 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2010 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
abstract class Stagehand_TestRunner_Runner_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Stagehand_TestRunner_Config
     */
    protected $config;
    protected $tmpDirectory;

    /**
     * @var Stagehand_TestRunner_Collector
     */
    protected $collector;

    /**
     * @var Stagehand_TestRunner_Runner
     */
    protected $runner;
    protected $runnerName;
    protected $output;
 
    public function setUp()
    {
        $this->tmpDirectory = dirname(__FILE__) . '/../../../../tmp';
        $this->config = new Stagehand_TestRunner_Config();
        $this->config->logsResultsInJUnitXML = true;
        $this->config->junitXMLFile =
            $this->tmpDirectory .
            '/' .
            get_class($this) .
            '.' .
            $this->getName(false) .
            '.xml';
        $collectorClass =
            'Stagehand_TestRunner_Collector_' . $this->runnerName . 'Collector';
        $this->collector = new $collectorClass($this->config);
    }

    public function tearDown()
    {
        $directoryScanner = new Stagehand_DirectoryScanner(array($this, 'removeJUnitXMLFile'));
        $directoryScanner->addExclude('^.*');
        $directoryScanner->addInclude('\.xml$');
        $directoryScanner->scan($this->tmpDirectory);
    }

    public function removeJUnitXMLFile($element)
    {
        unlink($element);
    }

    protected function assertTestCaseCount($count)
    {
        $testcases = $this->createXPath()->query('//testcase');
        $this->assertEquals($count, $testcases->length);
    }

    protected function assertTestCaseExists($method, $class)
    {
        $testcases = $this->createXPath()
                          ->query("//testcase[@name='$method'][@class='$class']");
        $this->assertEquals(1, $testcases->length);
    }

    protected function createXPath()
    {
        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        return new DOMXPath($junitXML);
    }

    protected function runTests($runnerClass = null)
    {
        if (is_null($runnerClass)) {
            $runnerClass =
                'Stagehand_TestRunner_Runner_' . $this->runnerName . 'Runner';
        }

        $this->runner = new $runnerClass($this->config);
        ob_start();
        $this->runner->run($this->collector->collect());
        $this->output = ob_get_contents();
        ob_end_clean();
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: utf-8
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
