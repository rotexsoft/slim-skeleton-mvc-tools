<?php
use \SlimMvcTools\Utils;

/**
 * @author Rotimi Adegbamigbe
 */
class UtilsTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void {
        
        parent::setUp();
    }

    public function testThatGetThrowableAsStrWorksAsExpected() {
        
        $e1 = new \BadMethodCallException('Descendant Thrown', 911);
        $e2 = new \BadFunctionCallException('Ancestor Thrown', 777, $e1);
        $e3 = new \LogicException('Base Thrown', 187, $e2);
        $e4 = new \Exception('Base Thrown', 987, $e3);
        
        $ex_as_str = Utils::getThrowableAsStr($e4);
        $this->assertStringContainsString(PHP_EOL, $ex_as_str);
        $this->assertStringContainsString('987', $ex_as_str);
        $this->assertStringContainsString('187', $ex_as_str);
        $this->assertStringContainsString('LogicException', $ex_as_str);
        $this->assertStringContainsString('Base Thrown', $ex_as_str);
        $this->assertStringContainsString('777', $ex_as_str);
        $this->assertStringContainsString('BadFunctionCallException', $ex_as_str);
        $this->assertStringContainsString('Ancestor Thrown', $ex_as_str);
        $this->assertStringContainsString('911', $ex_as_str);
        $this->assertStringContainsString('BadMethodCallException', $ex_as_str);
        $this->assertStringContainsString('Descendant Thrown', $ex_as_str);
        
        $ex_as_str2 = Utils::getThrowableAsStr($e4, '<br>');
        $this->assertStringContainsString('<br>', $ex_as_str2);
        $this->assertStringContainsString('987', $ex_as_str2);
        $this->assertStringContainsString('187', $ex_as_str2);
        $this->assertStringContainsString('LogicException', $ex_as_str2);
        $this->assertStringContainsString('Base Thrown', $ex_as_str2);
        $this->assertStringContainsString('777', $ex_as_str2);
        $this->assertStringContainsString('BadFunctionCallException', $ex_as_str2);
        $this->assertStringContainsString('Ancestor Thrown', $ex_as_str2);
        $this->assertStringContainsString('911', $ex_as_str2);
        $this->assertStringContainsString('BadMethodCallException', $ex_as_str2);
        $this->assertStringContainsString('Descendant Thrown', $ex_as_str2);
    }
}
