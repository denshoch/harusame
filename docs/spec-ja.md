# Harusame プログラム仕様書

## 概要
Harusameは、特定のHTML要素に対して特別なクラスを追加するライブラリです。このライブラリは、縦中横（Tate-Chu-Yoko）やテキストの向きを調整するために使用されます。

## 主な機能
1. **テキスト変換**:
   - 数字のシーケンス（デフォルトは2桁）や感嘆符（疑問符）のシーケンスを`span.tcy`でラップします。
   - 各文字を`span.upright`または`span.sideways`でラップし、テキストの向きを調整します。
   - `tcyDigit`が0の場合、`.tcy`クラスは追加されません。

2. **HTMLの処理**:
   - HTML文字列を受け取り、ボディタグ内のテキストノードのみを変換します。
   - 不正なHTMLが入力された場合、元のテキストを返し、エラーメッセージをSTDERRに出力します。

## 使用方法
```php
$harusame = new Denshoch\Harusame();
$result = $harusame->transformText('テキスト例', ['tcyDigit' => 3]);
// => <span class="tcy">テキスト例</span>

// オプションを指定しない場合
$result = $harusame->transformText('テキスト例');
// => <span class="tcy">テキスト例</span>（デフォルトのtcyDigitが適用されます）
```

## オプション
- `tcyDigit`: integer（デフォルトは2） - `.tcy`クラスを追加する数字の最大桁数。0に設定すると、`.tcy`クラスは追加されません。
- `autoTextOrientation`: boolean（デフォルトはtrue） - `.upright`および`.sideways`クラスを追加するかどうか。

## エラーハンドリング
- `transform`メソッドは、無効なHTML入力に対してエラーハンドリングを行います。処理できない場合は、元のテキストを返し、エラーメッセージをSTDERRに出力します。

## テスト
- PHPUnitを使用してテストを実行します。テストは`tests`ディレクトリ内に配置されています。
