<?php
require_once 'vendor/autoload.php';

class HarusameTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->harusame = new Denshoch\Harusame;
    }

    public function testTcy2()
    {
        $source =    '12ああああ34ああ457あああ89';
        $excpected = '<span class="tcy">12</span>ああああ<span class="tcy">34</span>ああ457あああ<span class="tcy">89</span>';
        $this->is_same($source, $excpected);

        $source =    '!!ああああ!!!ああ!?あああ??';
        $excpected = '<span class="tcy">!!</span>ああああ!!!ああ<span class="tcy">!?</span>あああ<span class="tcy">??</span>';
        $this->is_same($source, $excpected);
    }

    public function testTcyCheckParent()
    {
        $source =    '<span class="tcy">12</span>ああああ<span class="tcy">34</span>ああ457あああ<span class="tcy">89</span>';
        $excpected = '<span class="tcy">12</span>ああああ<span class="tcy">34</span>ああ457あああ<span class="tcy">89</span>';
        $this->is_same($source, $excpected);

        $source =    '<span class="tcy">!!</span>ああああ!!!ああ<span class="tcy">!?</span>あああ<span class="tcy">??</span>';
        $excpected = '<span class="tcy">!!</span>ああああ!!!ああ<span class="tcy">!?</span>あああ<span class="tcy">??</span>';
        $this->is_same($source, $excpected);
    }

    public function testTcy3()
    {
    	$this->harusame->tcyDigit = 3;
        $source =    '12ああああ34ああ457あああ89';
        $excpected = '<span class="tcy">12</span>ああああ<span class="tcy">34</span>ああ<span class="tcy">457</span>あああ<span class="tcy">89</span>';
        $this->is_same($source, $excpected);

        $source =    '!!ああああ!!!ああ!?あああ??';
        $excpected = '<span class="tcy">!!</span>ああああ!!!ああ<span class="tcy">!?</span>あああ<span class="tcy">??</span>';
        $this->is_same($source, $excpected);
    }

    public function testTcyFalse()
    {
    	$this->harusame->autoTcy = false;
        $source =    '12ああああ34ああ457あああ89';
        $excpected = '12ああああ34ああ457あああ89';
        $this->is_same($source, $excpected);

        $source =    '!!ああああ!!!ああ!?あああ??';
        $excpected = '!!ああああ!!!ああ!?あああ??';
        $this->is_same($source, $excpected);
    }


    public function testHTML()
    {
        $source =    '<html><head><title>12ああああ34ああ457あああ89</title><body>12ああああ34ああ457あああ89</body></html>';
        $excpected = '<html><head><title>12ああああ34ああ457あああ89</title><body><span class="tcy">12</span>ああああ<span class="tcy">34</span>ああ457あああ<span class="tcy">89</span></body></html>';
        $this->is_same($source, $excpected);
    }

    private function is_same($source, $excpected)
    {
        $actual = $this->harusame->transform($source);
        $this->assertSame($excpected, $actual);
    }
}