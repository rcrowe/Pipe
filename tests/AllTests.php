<?php

require_once 'simpletest/autorun.php';
require_once '../Pipe.php';

/**
 * Test suite runs all tests
 */
class AllTests extends TestSuite
{
    function __construct()
    {
        $this->TestSuite('All Tests');
        
        $this->addFile('PipeConfigTests.php');
        $this->addFile('PipeTests.php');
        $this->addFile('PipeConnectionTests.php');
        $this->addFile('PipeTableTests.php');
    }
}

?>