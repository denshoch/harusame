<?php
namespace Denshoch;

use DOMDocument;
use DOMXpath;

class Harusame
{
	protected static $autoTcy = true;
	protected static $tcyDigit = 2;
    protected static $autoTextOrientation = true;

    public function __construct(array $options=null)
    {
        self::$autoTcy = true;
        self::$tcyDigit = 2;
        self::$autoTextOrientation = true;

        // !== null is faster than is_null()
		if ($options !== null){

		    if(array_key_exists("autoTcy", $options)){
		  	    if (is_bool($options["autoTcy"])) {
		  		    self::$autoTcy = $options["autoTcy"];
		  	    } else {
		  		    trigger_error("autoTcy should be boolean.");
		  	    }
		    }

		    if(array_key_exists("tcyDigit", $options)){
		  	    if (is_int($options["tcyDigit"])) {
		  	    	if ($options["tcyDigit"] < 2) {
                        trigger_error("tcyDigit should be 2 or greater.", E_USER_ERROR);
		  	    	} else {
		  	    		self::$tcyDigit = $options["tcyDigit"];
		  	    	}
		  	    } else {
		  		    trigger_error("tcyDigit should be int.");
		  	    }
		    }

		    if(array_key_exists("autoTextOrientation", $options)){
		  	    if (is_bool($options["autoTextOrientation"])) {
		  		    self::$autoTextOrientation = $options["autoTextOrientation"];
		  	    } else {
		  		    trigger_error("autoTextOrientation should be boolean.");
		  	    }
		    }
		}
    }

    /**
     * transform text
     * 
     * @param string $text Input text to transform.
     * @return string transformed text.
     */
    public static function transform(string $text):string
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = false;
        $text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');

        @$dom->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $isHtml = ($dom->firstChild->tagName === 'html');

        if (!$isHtml) {
            $text = "<harusame>$text</harusame>";
            @$dom->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        }

        $xpath = new DOMXpath($dom);
        if ($isHtml) {
            $nodes = $xpath->query("//body//text()");
        } else {
            $nodes = $xpath->query("//text()");
        }
        
        foreach($nodes as $node) {
            if (self::checkParentNode($node)) continue;
            if (preg_match('/\s+/', $node->nodeValue)) continue;

            $str = self::filter($node->textContent);
            $str = mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8');

            $tmpDom = new DOMDocument('1.0', 'utf-8');
            $tmpDom->formatOutput = false;
            $fragment = $node->ownerDocument->createDocumentFragment();
            
            @$tmpDom->loadHTML("<harusame>$str</harusame>", LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            foreach($tmpDom->firstChild->childNodes as $child){
                $child = $dom->importNode($child, true);
                $fragment->appendChild($child);
            }
            unset($tmpDom);
            $node->parentNode->replaceChild($fragment, $node);
        }

        if ($isHtml) {
            return rtrim(mb_convert_encoding($dom->saveHTML(), 'UTF-8', 'HTML-ENTITIES'), "\n");
        }
        return self::innerHTML($dom->firstChild);
    }

    /**
     * checkParentNode
     * 
     * @param DOMNode $node
     * @return boolean
     */
    public static function checkParentNode(\DOMNode &$node):bool
    {
        // === null is faster then is_null()
        if ($node->parentNode === null) return false;

        if ($node->nodeType === 1)
        {
            if(!empty($classStr = $node->getAttribute('class')))
            {
                $arr = [];
                foreach(preg_split('/\s/', $classStr) as $class) {
                    $arr[$class] = true;
                }
                // isset() is faster than in_array()
                if (
                    isset($arr['tcy']) || 
                    isset($arr['upright']) || 
                    isset($arr['sideways'])
                ) {
                    return true;
                }
            }
        }

        return self::checkParentNode($node->parentNode);
    }

    /**
     * innerHTML
     * 
     * @param DOMNode $node
     * @return string Inner HTML.
     */
    protected static function innerHTML(\DOMNode $node):string
    {
        $str = "";
        foreach($node->childNodes as $child)
        {
            $str .= $node->ownerDocument->saveHTML($child);
        }
        return $str;
    }

    /**
     * filter
     * 
     * @param string $text
     * @return string Transformed text.
     */
    private static function filter(string $text):string
    {
        // URLの正規表現断片 http://qiita.com/mpyw/items/1e422848030fcde0f29a
        $urlRegFlagment = 'https?+:(?://(?:(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]' .
            '|[!$&-,:;=])*+@)?+(?:\[(?:(?:[0-9a-f]{1,4}:){6}(?:' .
            '[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2' .
            '[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25' .
            '[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?' .
            ':\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|::(?:[0-9a-f' .
            ']{1,4}:){5}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1' .
            '-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{' .
            '2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\\' .
            'd|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])' .
            ')|(?:[0-9a-f]{1,4})?+::(?:[0-9a-f]{1,4}:){4}(?:[0-' .
            '9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2[0-' .
            '4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-' .
            '5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d' .
            '|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:[0-9a-f]{' .
            '1,4}:)?+[0-9a-f]{1,4})?+::(?:[0-9a-f]{1,4}:){3}(?:' .
            '[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2' .
            '[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25' .
            '[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?' .
            ':\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:[0-9a-' .
            'f]{1,4}:){0,2}[0-9a-f]{1,4})?+::(?:[0-9a-f]{1,4}:)' .
            '{2}(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\\' .
            'd{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4' .
            ']\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5' .
            '])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:' .
            '[0-9a-f]{1,4}:){0,3}[0-9a-f]{1,4})?+::[0-9a-f]{1,4' .
            '}:(?:[0-9a-f]{1,4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d' .
            '{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]' .
            '\d|25[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]' .
            ')\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:[' .
            '0-9a-f]{1,4}:){0,4}[0-9a-f]{1,4})?+::(?:[0-9a-f]{1' .
            ',4}:[0-9a-f]{1,4}|(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25' .
            '[0-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?' .
            ':\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\\' .
            'd|1\d{2}|2[0-4]\d|25[0-5]))|(?:(?:[0-9a-f]{1,4}:){' .
            '0,5}[0-9a-f]{1,4})?+::[0-9a-f]{1,4}|(?:(?:[0-9a-f]' .
            '{1,4}:){0,6}[0-9a-f]{1,4})?+::|v[0-9a-f]++\.[!$&-.' .
            '0-;=_a-z~]++)\]|(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0' .
            '-5])\.(?:\d|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\\' .
            'd|[1-9]\d|1\d{2}|2[0-4]\d|25[0-5])\.(?:\d|[1-9]\d|' .
            '1\d{2}|2[0-4]\d|25[0-5])|(?:[-.0-9_a-z~]|%[0-9a-f]' .
            '[0-9a-f]|[!$&-,;=])*+)(?::\d*+)?+(?:/(?:[-.0-9_a-z' .
            '~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])*+)*+|/(?:(?:[-.0' .
            '-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])++(?:/(?:[-' .
            '.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])*+)*+)?+|' .
            '(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])++(?' .
            ':/(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&-,:;=@])*+' .
            ')*+)?+(?:\?+(?:[-.0-9_a-z~]|%[0-9a-f][0-9a-f]|[!$&' .
            '-,/:;=?+@])*+)?+(?:#(?:[-.0-9_a-z~]|%[0-9a-f][0-9a' .
            '-f]|[!$&-,/:;=?+@])*+)?+'
        ;
        // メールアドレスの正規表現断片 http://qiita.com/tabo_purify/items/aa279cd28abdeba0873c
        // (?:) でキャプチャを避けてグループ化した
        $mailRegFlagment = "[0-9a-z_./?-]+@(?:[0-9a-z-]+\.)+[0-9a-z-]+";
        // 文字参照の正規表現断片
        $charRefRegFlagment = "&#?[a-z0-9]{2,8};";

        // 除外する正規表現パターン組み立て
        $fileterReg = "`($urlRegFlagment|$mailRegFlagment|$charRefRegFlagment)`i";

        $text_array= preg_split($fileterReg, $text, null, PREG_SPLIT_DELIM_CAPTURE);

        $return_text = "";
        foreach ($text_array as $text_array_item) {
            if ( preg_match($fileterReg, $text_array_item) == false ) {
                if(true === self::$autoTcy) {
                    $text_array_item = self::setTcy($text_array_item); 
                }
                if(true === self::$autoTextOrientation) {
                    $text_array_item = self::setTextOrientation($text_array_item); 
                }
            }
            $return_text .= $text_array_item;
        }

        return $return_text;
    }

    /**
     * setTcy
     * 
     * @param string $text
     * @return string Transformted text.
     */
    private static function setTcy(string $text):string
    {
    	$emoReg = "/(^|[^!?])([!?]{2})(?![!?])/u";
    	$text = preg_replace($emoReg, '\1<span class="tcy">\2</span>', $text);

    	$digitReg = "/(^|[^0-9])([0-9]{2," . self::$tcyDigit . "})(?![0-9])/u";
        $text = preg_replace($digitReg, '\1<span class="tcy">\2</span>', $text);
    	return $text;
    }

    /**
     * setTextOrientation
     * 
     * @param string $text
     * @return string Transformted text.
     */
    private static function setTextOrientation(string $text):string
    {
        //　横転処理
        $sidewaysReg = "/(÷|&#xf7;|&#247;|∴|&#x2234;|&#8756;|≠|&#x2260;|&#8800;|≦|&#x2266;|&#8806;|≧|&#x2267;|&#8807;|∧|&#x2227;|&#8743;|∨|&#x2228;|&#8744;|＜|&#xff1c;|&#65308;|＞|&#xff1e;|&#65310;|‐|&#x2010;|&#8208;|－|&#xff0d;|&#65293;)/u";

        $text = preg_replace($sidewaysReg, '<span class="sideways">\1</span>', $text);
        
        // 正立処理
        $uprightReg = "/(Α|&#x391;|&#913;|Β|&#x392;|&#914;|Γ|&#x393;|&#915;|Δ|&#x394;|&#916;|Ε|&#x395;|&#917;|Ζ|&#x396;|&#918;|Η|&#x397;|&#919;|Θ|&#x398;|&#920;|Ι|&#x399;|&#921;|Κ|&#x39a;|&#922;|Λ|&#x39b;|&#923;|Μ|&#x39c;|&#924;|Ν|&#x39d;|&#925;|Ξ|&#x39e;|&#926;|Ο|&#x39f;|&#927;|Π|&#x3a0;|&#928;|Ρ|&#x3a1;|&#929;|Σ|&#x3a3;|&#931;|Τ|&#x3a4;|&#932;|Υ|&#x3a5;|&#933;|Φ|&#x3a6;|&#934;|Χ|&#x3a7;|&#935;|Ψ|&#x3a8;|&#936;|Ω|&#x3a9;|&#937;|α|&#x3b1;|&#945;|β|&#x3b2;|&#946;|γ|&#x3b3;|&#947;|δ|&#x3b4;|&#948;|ε|&#x3b5;|&#949;|ζ|&#x3b6;|&#950;|η|&#x3b7;|&#951;|θ|&#x3b8;|&#952;|ι|&#x3b9;|&#953;|κ|&#x3ba;|&#954;|λ|&#x3bb;|&#955;|μ|&#x3bc;|&#956;|ν|&#x3bd;|&#957;|ξ|&#x3be;|&#958;|ο|&#x3bf;|&#959;|π|&#x3c0;|&#960;|ρ|&#x3c1;|&#961;|ς|&#x3c2;|&#962;|σ|&#x3c3;|&#963;|τ|&#x3c4;|&#964;|υ|&#x3c5;|&#965;|φ|&#x3c6;|&#966;|χ|&#x3c7;|&#967;|ψ|&#x3c8;|&#968;|ω|&#x3c9;|&#969;|А|&#x410;|&#1040;|Б|&#x411;|&#1041;|В|&#x412;|&#1042;|Г|&#x413;|&#1043;|Д|&#x414;|&#1044;|Е|&#x415;|&#1045;|Ё|&#x401;|&#1025;|Ж|&#x416;|&#1046;|З|&#x417;|&#1047;|И|&#x418;|&#1048;|Й|&#x419;|&#1049;|К|&#x41a;|&#1050;|Л|&#x41b;|&#1051;|М|&#x41c;|&#1052;|Н|&#x41d;|&#1053;|О|&#x41e;|&#1054;|П|&#x41f;|&#1055;|Р|&#x420;|&#1056;|С|&#x421;|&#1057;|Т|&#x422;|&#1058;|У|&#x423;|&#1059;|Ф|&#x424;|&#1060;|Х|&#x425;|&#1061;|Ц|&#x426;|&#1062;|Ч|&#x427;|&#1063;|Ш|&#x428;|&#1064;|Щ|&#x429;|&#1065;|Ъ|&#x42a;|&#1066;|Ы|&#x42b;|&#1067;|Ь|&#x42c;|&#1068;|Э|&#x42d;|&#1069;|Ю|&#x42e;|&#1070;|Я|&#x42f;|&#1071;|а|&#x430;|&#1072;|б|&#x431;|&#1073;|в|&#x432;|&#1074;|г|&#x433;|&#1075;|д|&#x434;|&#1076;|е|&#x435;|&#1077;|ё|&#x451;|&#1105;|ж|&#x436;|&#1078;|з|&#x437;|&#1079;|и|&#x438;|&#1080;|й|&#x439;|&#1081;|к|&#x43a;|&#1082;|л|&#x43b;|&#1083;|м|&#x43c;|&#1084;|н|&#x43d;|&#1085;|о|&#x43e;|&#1086;|п|&#x43f;|&#1087;|р|&#x440;|&#1088;|с|&#x441;|&#1089;|т|&#x442;|&#1090;|у|&#x443;|&#1091;|ф|&#x444;|&#1092;|х|&#x445;|&#1093;|ц|&#x446;|&#1094;|ч|&#x447;|&#1095;|ш|&#x448;|&#1096;|щ|&#x449;|&#1097;|ъ|&#x44a;|&#1098;|ы|&#x44b;|&#1099;|ь|&#x44c;|&#1100;|э|&#x44d;|&#1101;|ю|&#x44e;|&#1102;|я|&#x44f;|&#1103;|¨|&#xa8;|&#168;|Ⅰ|&#x2160;|&#8544;|Ⅱ|&#x2161;|&#8545;|Ⅲ|&#x2162;|&#8546;|Ⅳ|&#x2163;|&#8547;|Ⅴ|&#x2164;|&#8548;|Ⅵ|&#x2165;|&#8549;|Ⅶ|&#x2166;|&#8550;|Ⅷ|&#x2167;|&#8551;|Ⅸ|&#x2168;|&#8552;|Ⅹ|&#x2169;|&#8553;|Ⅺ|&#x216a;|&#8554;|ⅰ|&#x2170;|&#8560;|ⅱ|&#x2171;|&#8561;|ⅲ|&#x2172;|&#8562;|ⅳ|&#x2173;|&#8563;|ⅴ|&#x2174;|&#8564;|ⅵ|&#x2175;|&#8565;|ⅶ|&#x2176;|&#8566;|ⅷ|&#x2177;|&#8567;|ⅸ|&#x2178;|&#8568;|ⅹ|&#x2179;|&#8569;|ⅺ|&#x217a;|&#8570;|ⅻ|&#x217b;|&#8571;|♀|&#x2640;|&#9792;|♂|&#x2642;|&#9794;|∀|&#x2200;|&#8704;|∃|&#x2203;|&#8707;|∠|&#x2220;|&#8736;|⊥|&#x22a5;|&#8869;|⌒|&#x2312;|&#8978;|∂|&#x2202;|&#8706;|∇|&#x2207;|&#8711;|√|&#x221a;|&#8730;|∽|&#x223d;|&#8765;|∝|&#x221d;|&#8733;|∫|&#x222b;|&#8747;|∬|&#x222c;|&#8748;|∞|&#x221e;|&#8734;|①|&#x2460;|&#9312;|②|&#x2461;|&#9313;|③|&#x2462;|&#9314;|④|&#x2463;|&#9315;|⑤|&#x2464;|&#9316;|⑥|&#x2465;|&#9317;|⑦|&#x2466;|&#9318;|⑧|&#x2467;|&#9319;|⑨|&#x2468;|&#9320;|⑩|&#x2469;|&#9321;|⑪|&#x246a;|&#9322;|⑫|&#x246b;|&#9323;|⑬|&#x246c;|&#9324;|⑭|&#x246d;|&#9325;|⑮|&#x246e;|&#9326;|⑯|&#x246f;|&#9327;|⑰|&#x2470;|&#9328;|⑱|&#x2471;|&#9329;|⑲|&#x2472;|&#9330;|⑳|&#x2473;|&#9331;|㉑|&#x3251;|&#12881;|㉒|&#x3252;|&#12882;|㉓|&#x3253;|&#12883;|㉔|&#x3254;|&#12884;|㉕|&#x3255;|&#12885;|㉖|&#x3256;|&#12886;|㉗|&#x3257;|&#12887;|㉘|&#x3258;|&#12888;|㉙|&#x3259;|&#12889;|㉚|&#x325a;|&#12890;|㉛|&#x325b;|&#12891;|㉜|&#x325c;|&#12892;|㉝|&#x325d;|&#12893;|㉞|&#x325e;|&#12894;|㉟|&#x325f;|&#12895;|㊱|&#x32b1;|&#12977;|㊲|&#x32b2;|&#12978;|㊳|&#x32b3;|&#12979;|㊴|&#x32b4;|&#12980;|㊵|&#x32b5;|&#12981;|㊶|&#x32b6;|&#12982;|㊷|&#x32b7;|&#12983;|㊸|&#x32b8;|&#12984;|㊹|&#x32b9;|&#12985;|㊺|&#x32ba;|&#12986;|㊻|&#x32bb;|&#12987;|㊼|&#x32bc;|&#12988;|㊽|&#x32bd;|&#12989;|㊾|&#x32be;|&#12990;|㊿|&#x32bf;|&#12991;|❶|&#x2776;|&#10102;|❷|&#x2777;|&#10103;|❸|&#x2778;|&#10104;|❹|&#x2779;|&#10105;|❺|&#x277a;|&#10106;|❻|&#x277b;|&#10107;|❼|&#x277c;|&#10108;|❽|&#x277d;|&#10109;|❾|&#x277e;|&#10110;|❿|&#x277f;|&#10111;|⓫|&#x24eb;|&#9451;|⓬|&#x24ec;|&#9452;|⓭|&#x24ed;|&#9453;|⓮|&#x24ee;|&#9454;|⓯|&#x24ef;|&#9455;|⓰|&#x24f0;|&#9456;|⓱|&#x24f1;|&#9457;|⓲|&#x24f2;|&#9458;|⓳|&#x24f3;|&#9459;|⓴|&#x24f4;|&#9460;|⓵|&#x24f5;|&#9461;|⓶|&#x24f6;|&#9462;|⓷|&#x24f7;|&#9463;|⓸|&#x24f8;|&#9464;|⓹|&#x24f9;|&#9465;|⓺|&#x24fa;|&#9466;|⓻|&#x24fb;|&#9467;|⓼|&#x24fc;|&#9468;|⓽|&#x24fd;|&#9469;|⓾|&#x24fe;|&#9470;|▱|&#x25b1;|&#9649;|▲|&#x25b2;|&#9650;|△|&#x25b3;|&#9651;|▼|&#x25bc;|&#9660;|▽|&#x25bd;|&#9661;|☀|&#x2600;|&#9728;|☁|&#x2601;|&#9729;|☂|&#x2602;|&#9730;|☃|&#x2603;|&#9731;|★|&#x2605;|&#9733;|☆|&#x2606;|&#9734;|☎|&#x260e;|&#9742;|☖|&#x2616;|&#9750;|☗|&#x2617;|&#9751;|♠|&#x2660;|&#9824;|♡|&#x2661;|&#9825;|♢|&#x2662;|&#9826;|♣|&#x2663;|&#9827;|♤|&#x2664;|&#9828;|♥|&#x2665;|&#9829;|♦|&#x2666;|&#9830;|♧|&#x2667;|&#9831;|♨|&#x2668;|&#9832;|♩|&#x2669;|&#9833;|♪|&#x266a;|&#9834;|♫|&#x266b;|&#9835;|♬|&#x266c;|&#9836;|♭|&#x266d;|&#9837;|♮|&#x266e;|&#9838;|♯|&#x266f;|&#9839;|✓|&#x2713;|&#10003;|〒|&#x3012;|&#12306;|〠|&#x3020;|&#12320;|¶|&#xb6;|&#182;|†|&#x2020;|&#8224;|‡|&#x2021;|&#8225;|‼|&#x203c;|&#8252;|⁇|&#x2047;|&#8263;|⁈|&#x2048;|&#8264;|⁉|&#x2049;|&#8265;|№|&#x2116;|&#8470;|℡|&#x2121;|&#8481;|㏍|&#x33cd;|&#13261;|＃|&#xff03;|&#65283;|＄|&#xff04;|&#65284;|％|&#xff05;|&#65285;|＆|&#xff06;|&#65286;|＊|&#xff0a;|&#65290;|＠|&#xff20;|&#65312;|￥|&#xffe5;|&#65509;|¢|&#xa2;|&#162;|£|&#xa3;|&#163;|§|&#xa7;|&#167;|°|&#xb0;|&#176;|‰|&#x2030;|&#8240;|′|&#x2032;|&#8242;|″|&#x2033;|&#8243;|℃|&#x2103;|&#8451;|㎎|&#x338e;|&#13198;|㎏|&#x338f;|&#13199;|㎝|&#x339d;|&#13213;|㎞|&#x339e;|&#13214;|㎡|&#x33a1;|&#13217;|㏄|&#x33c4;|&#13252;|Å|&#x212b;|&#8491;|〳|&#x3033;|&#12339;|〴|&#x3034;|&#12340;|〵|&#x3035;|&#12341;|〻|&#x303b;|&#12347;|〼|&#x303c;|&#12348;|ゟ|&#x309f;|&#12447;|ヿ|&#x30ff;|&#12543;|⅓|&#x2153;|&#8531;|⅔|&#x2154;|&#8532;|⅕|&#x2155;|&#8533;|⇒|&#x21d2;|&#8658;|⇔|&#x21d4;|&#8660;)/u";

        $text = preg_replace($uprightReg, '<span class="upright">\1</span>', $text);

        return $text;
    }
}