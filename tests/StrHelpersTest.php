<?php
declare(strict_types=1);

/**
 * Description of StrHelpersTest
 *
 * @author rotimi
 */
class StrHelpersTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void {
        
        parent::setUp();
    }

    public function testThat_mb_str_starts_with_WorksAsExpected() {
            
        self::assertSame(true, mb_str_starts_with('Âbbcd', ''));
        self::assertSame(true, mb_str_starts_with('Âbbcd', 'Âbb'));
        self::assertSame(true, mb_str_starts_with('Âbbcd', 'Âb'));
        self::assertSame(true, mb_str_starts_with('Âbbcd', 'Â'));
        self::assertSame(false, mb_str_starts_with('Âbc', 'Âbb'));
    }

    public function testThat_mb_str_ends_with_WorksAsExpected() {
        
        self::assertSame(true, mb_str_ends_with('abbcđ', ''));
        self::assertSame(true, mb_str_ends_with('abbcđ', 'bcđ'));
        self::assertSame(true, mb_str_ends_with('abbcđ', 'cđ'));
        self::assertSame(true, mb_str_ends_with('abbcđ', 'đ'));
        self::assertSame(false, mb_str_ends_with('abc', 'abđ'));
    }

    public function testThat_mb_str_contains_WorksAsExpected() {
        
        self::assertSame(true, mb_str_contains('Âbbcd', ''));
        self::assertSame(true, mb_str_contains('Âbbcd', 'Âbb'));
        self::assertSame(true, mb_str_contains('Âbbcd', 'bbc'));
        self::assertSame(true, mb_str_contains('Âbbcd', 'bcd'));
        self::assertSame(false, mb_str_contains('Âbcss', 'Âbb'));
    }
    
    public function testThatDashesToCamelWorksAsExpected() {
        
        self::assertEquals('fooBarBaz', \SlimMvcTools\Functions\Str\dashesToCamel('foo-bar-baz'));
    }
    
    public function testThatDashesToStudlyWorksAsExpected() {
        
        self::assertEquals('FooBarBaz', \SlimMvcTools\Functions\Str\dashesToStudly('foo-bar-baz'));
    }
    
    public function testThatUnderToCamelWorksAsExpected() {
        
        self::assertEquals('fooBarBaz', \SlimMvcTools\Functions\Str\underToCamel('foo_bar_baz'));
    }
    
    public function testThatUnderToStudlyWorksAsExpected() {
        
        self::assertEquals('FooBarBaz', \SlimMvcTools\Functions\Str\underToStudly('foo_bar_baz'));
    }
    
    public function testThatToDashesWorksAsExpected() {
        
        self::assertEquals('foo-bar-baz', \SlimMvcTools\Functions\Str\toDashes('FooBar_ Baz'));
        self::assertEquals('foo-bar-baz', \SlimMvcTools\Functions\Str\toDashes('Foo Bar_ Baz'));
        self::assertEquals('foo-bar-baz', \SlimMvcTools\Functions\Str\toDashes('ÂFooBar_ Bazđ'));
        self::assertEquals('foo-bar-baz', \SlimMvcTools\Functions\Str\toDashes('ÂFooÂBar_ BaÂzđ'));
    }
    
    public function testThatCamelToDashesWorksAsExpected() {
        
        self::assertEquals('camel-caps-word', \SlimMvcTools\Functions\Str\camelToDashes('camelCapsWord'));
        self::assertEquals('camel-caps-word', \SlimMvcTools\Functions\Str\camelToDashes('CamelCapsWord'));
    }
    
    public function testThatCamelToUnderWorksAsExpected() {
        
        self::assertEquals('Camel_Caps_Word', \SlimMvcTools\Functions\Str\camelToUnder('camelCapsWord'));
        self::assertEquals('Camel_Caps_Word', \SlimMvcTools\Functions\Str\camelToUnder('CamelCapsWord'));
    }
    
    public function testThat_color_4_console_WorksAsExpected() {
        
        if(PHP_OS !== 'Linux') {
            
            self::assertEquals('boo', \SlimMvcTools\Functions\Str\color_4_console('boo'));
            
        } else {
            // we are on linux
            // default black background & white foreground
            self::assertEquals("\033[1;37m\033[40mboo\033[0m", \SlimMvcTools\Functions\Str\color_4_console('boo'));
            
            // green background & light_blue foreground
            self::assertEquals("\033[1;34m\033[42mboo\033[0m", \SlimMvcTools\Functions\Str\color_4_console('boo', 'light_blue', 'green'));
        }
    }
}
