Harusame
========

Harusame is a library designed to control the orientation of characters in vertical writing. It allows for the proper display of text in Japanese vertical format, ensuring that characters are displayed upright or sideways as needed. This is particularly useful for applications that require text to be formatted according to traditional Japanese typesetting standards.

Wrap sequence of numbers (default is two-digit) and sequence of EXCLAMATION (QUESTION) MARKs with `span.tcy` in (see [Tate-Chu-Yoko](https://www.w3.org/TR/jlreq/#handling_of_tatechuyoko)).

Wrap each character of upright glyph orientation with `span.upright` and sideways glyph orientation with `span.sideways` (see [UTR50](http://www.unicode.org/reports/tr50/)).

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

New static method `transformText`

```php
$result = Denshoch\Harusame::transformText('平成20年!?');
// => 平成<span class="tcy">20</span>年<span class="tcy">!?</span>

// With options
$options = ['tcyDigit' => 3];
$result = Denshoch\Harusame::transformText('10円玉と100円玉がある。', $options);
// => <span class="tcy">10</span>円玉と<span class="tcy">100</span>円玉がある。
```

Previous usage

```php
$harusame = new Denshoch\Harusame();
$harusame->transform('平成20年!?');
// => 平成<span class="tcy">20</span>年<span class="tcy">!?</span>

$harusame->transform('<html><head><title>平成20年!?</title></head><body>平成20年!?</body></html>');
// You can pass HTML string. Only text nodes within the body tag are transformed.
// => <html><head><title>平成20年!?</title></head><body>平成<span class="tcy">20</span>年<span class="tcy">!?</span></body></html>

$harusame->transform('⓵☂÷∴');
// => <span class="upright">⓵</span><span class="upright">☂</span><span class="sideways">÷</span><span class="sideways">∴</span>
```

### Options

```php
$options = array("tcyDigit" => 3);
$harusame = new Denshoch\Harusame($options);
$harusame->transform('10円玉と100円玉がある。');
// => <span class="tcy">10</span>円玉と<span class="tcy">100</span>円玉がある。
// or
$harusame = new Denshoch\Harusame();
$harusame->tcyDigit = 3;
$harusame->transform('10円玉と100円玉がある。');
// => <span class="tcy">10</span>円玉と<span class="tcy">100</span>円玉がある。
```

| Key                  | Type     | Initial | Description                             |
|----------------------|----------|---------|-----------------------------------------|
| tcyDigit             | integer  | 2       | Maximum number of digits to add `.tcy` class. If set to 0, no `.tcy` class will be added. |
| autoTextOrientation   | boolean  | true    | Add `.upright` and `.sideways` class or not. |

Test
-----

```
$ vendor/bin/phpunit
```
