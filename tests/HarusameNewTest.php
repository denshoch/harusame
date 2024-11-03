<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Denshoch\Harusame;

class HarusameNewTest extends TestCase
{
    public function testTransformTextWithDefaultOptions()
    {
        $source = '12ああああ34ああ457あああ89';
        $expected = '<span class="tcy">12</span>ああああ<span class="tcy">34</span>ああ457あああ<span class="tcy">89</span>';
        
        $result = Harusame::transformText($source);
        $this->assertSame($expected, $result);
    }

    public function testTransformTextWithOptions()
    {
        $options = ['tcyDigit' => 3];
        $source = '12ああああ34ああ457あああ89';
        $expected = '<span class="tcy">12</span>ああああ<span class="tcy">34</span>ああ<span class="tcy">457</span>あああ<span class="tcy">89</span>';
        
        $result = Harusame::transformText($source, $options);
        $this->assertSame($expected, $result);
    }

    public function testTransformTextWithZeroTcyDigit()
    {
        $options = ['tcyDigit' => 0];
        $source = '12ああああ34ああ457あああ89';
        $expected = $source; // No .tcy class should be added
        
        $result = Harusame::transformText($source, $options);
        $this->assertSame($expected, $result);
    }

    public function testTransformTextWithHtmlInput()
    {
        $htmlSource = '<html><head><title>テスト</title></head><body>12ああああ34ああ457あああ89</body></html>';
        $expected = '<html><head><title>テスト</title></head><body><span class="tcy">12</span>ああああ<span class="tcy">34</span>ああ457あああ<span class="tcy">89</span></body></html>';
        
        $result = Harusame::transformText($htmlSource);
        $this->assertStringContainsString('<span class="tcy">12</span>', $result);
    }

    public function testTransformTextWithInvalidHtml()
    {
        $invalidHtml = "<abc>aaa</efg>";
        $expected = $invalidHtml; // Should return the original text
        
        $result = Harusame::transformText($invalidHtml);
        $this->assertSame($expected, $result);
    }
} 