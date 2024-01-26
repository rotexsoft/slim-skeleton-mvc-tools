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
        self::assertStringContainsString(PHP_EOL, $ex_as_str);
        self::assertStringContainsString('987', $ex_as_str);
        self::assertStringContainsString('187', $ex_as_str);
        self::assertStringContainsString('LogicException', $ex_as_str);
        self::assertStringContainsString('Base Thrown', $ex_as_str);
        self::assertStringContainsString('777', $ex_as_str);
        self::assertStringContainsString('BadFunctionCallException', $ex_as_str);
        self::assertStringContainsString('Ancestor Thrown', $ex_as_str);
        self::assertStringContainsString('911', $ex_as_str);
        self::assertStringContainsString('BadMethodCallException', $ex_as_str);
        self::assertStringContainsString('Descendant Thrown', $ex_as_str);
        
        $ex_as_str2 = Utils::getThrowableAsStr($e4, '<br>');
        self::assertStringContainsString('<br>', $ex_as_str2);
        self::assertStringContainsString('987', $ex_as_str2);
        self::assertStringContainsString('187', $ex_as_str2);
        self::assertStringContainsString('LogicException', $ex_as_str2);
        self::assertStringContainsString('Base Thrown', $ex_as_str2);
        self::assertStringContainsString('777', $ex_as_str2);
        self::assertStringContainsString('BadFunctionCallException', $ex_as_str2);
        self::assertStringContainsString('Ancestor Thrown', $ex_as_str2);
        self::assertStringContainsString('911', $ex_as_str2);
        self::assertStringContainsString('BadMethodCallException', $ex_as_str2);
        self::assertStringContainsString('Descendant Thrown', $ex_as_str2);
    }
}
