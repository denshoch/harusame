Harusame
========

Wrap sequence of numbers(default is two-digit) and sequence of EXCLAMATION (QUESTION) MARKs with `span.tcy` in (see [Tate-Chu-Yoko](https://www.w3.org/TR/jlreq/#handling_of_tatechuyoko)).

Wrap each characters of upright glyph orientation with `span.upright` and sideways glyph orientation with `span.sideways`(see [UTR50](http://www.unicode.org/reports/tr50/)).

Expected CSS;

```css
.tcy {
	text-combine-upright: all;
}
.upright {
	text-orientation: upright;
}
.sideways {
	text-orientation: sideways;
}
```

Install
--------

```
composer install
```

Usage
------

```php
$harusame = new Denshoch\Harusame();
$harusame.transform('平成20年!?');
// => 平成<span class="tcy">20</span>年<span class="tcy">!?</span>


$harusame.transform('<html><head><title>平成20年!?</title></head><body>平成20年!?</body></html>');
// You can pass HTML string. Only text nodes within the body tag are transformed.
// => <html><head><title>平成20年!?</title></head><body>平成<span class="tcy">20</span>年<span class="tcy">!?</span></body></html>

$harusame.transform('⓵☂÷∴');
// => <span class="upright">⓵</span><span class="upright">☂</span><span class="sideways">÷</span><span class="sideways">∴</span>
```

### Options

```php
$options = array("tcyDigit" => 3);
$harusame = new Denshoch\Harusame($options);
$harusame.transform('10円玉と100円玉がある。');
// => <span class="tcy">10</span>円玉と<span class="tcy">100</span>円玉がある。
// or
$harusame = new Denshoch\Harusame();
$harusame.tcyDigit = 3;
$harusame.transform('10円玉と100円玉がある。');
// => <span class="tcy">10</span>円玉と<span class="tcy">100</span>円玉がある。
```

| key | type | inital | description |
| --- | ---  | ---    | ---         |
| autoTcy | boolean | true | Add `.tcy` class or not. |
| tcyDigit | integer | 2   | max digits of number to add `.tcy` class. |
| autoTextOrientation | boolean | true | Add `.upright` and `.sideways` class or not. |

Test
-----

```
$ verdor/bin/phpunit
```
