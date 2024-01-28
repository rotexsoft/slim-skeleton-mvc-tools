<?php

/**
 * This class tests .\src\scripts\smvc-create-controller
 *
 * @author Rotimi Adegbamigbe
 */
class CreateControllerScriptTest extends \PHPUnit\Framework\TestCase
{
    protected $ds; //directory separator

    protected $script_2_test;

    protected function setUp(): void {

        parent::setUp();
        $ds = DIRECTORY_SEPARATOR;
        $this->ds = DIRECTORY_SEPARATOR;
        $this->script_2_test = __DIR__."{$ds}..{$ds}src{$ds}scripts{$ds}smvc-create-controller";
    }

    public function testBecauseThereShouldBeAtLeastOneTestInThisClass() {

        self::assertTrue(true);
    }
}
