[jieba-php](http://www.fukuball.com/jieba-php/)
=========
[![Build Status](https://travis-ci.org/fukuball/jieba-php.svg?branch=master)](https://travis-ci.org/fukuball/jieba-php)
[![codecov.io](http://codecov.io/github/fukuball/jieba-php/coverage.svg?branch=master)](http://codecov.io/github/fukuball/jieba-php?branch=master)
[![Latest Stable Version](https://poser.pugx.org/fukuball/jieba-php/v/stable.png)](https://packagist.org/packages/fukuball/jieba-php)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/9360ebe8fc2d47d8a64f49f57d2f016f)](https://www.codacy.com/app/fukuball/jieba-php)
[![Made with Love](https://img.shields.io/badge/made%20with-%e2%9d%a4-ff69b4.svg)](http://www.fukuball.com)

"結巴"中文分詞：做最好的 PHP 中文分詞、中文斷詞組件，目前翻譯版本為 jieba-0.26 版本，未來再慢慢往上升級，效能也需要再改善，請有興趣的開發者一起加入開發！若想使用 Python 版本請前往 [fxsjy/jieba](https://github.com/fxsjy/jieba)

現在已經可以支援繁體中文！只要將字典切換為 big 模式即可！

"Jieba" (Chinese for "to stutter") Chinese text segmentation: built to be the best PHP Chinese word segmentation module.

_Scroll down for English documentation._

線上展示
========
* 網站網址：[http://jieba-php.fukuball.com](http://jieba-php.fukuball.com)
* 網站原始碼：[https://github.com/fukuball/jieba-php.fukuball.com](https://github.com/fukuball/jieba-php.fukuball.com)

Feature
========
* 支持三種分詞模式：
* 1）默認精確模式，試圖將句子最精確地切開，適合文本分析；
* 2）全模式，把句子中所有的可以成詞的詞語都掃描出來，但是不能解決歧義。（需要充足的字典）
* 3) 搜尋引擎模式，在精確模式的基礎上，對長詞再次切分，提高召回率，適合用於搜尋引擎分詞。

* 支持繁體斷詞
* 支持自定義詞典

Usage
========
* 自動安裝：使用 composer 安裝後，透過 autoload 引用

代碼示例

```php
composer require fukuball/jieba-php:dev-master
```

代碼示例

```php
require_once "/path/to/your/vendor/autoload.php";
```

* 手動安裝：將 jieba-php 放置適當目錄後，透過 require_once 引用

代碼示例

```php
require_once "/path/to/your/vendor/multi-array/MultiArray.php";
require_once "/path/to/your/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once "/path/to/your/class/Jieba.php";
require_once "/path/to/your/class/Finalseg.php";
```

Algorithm
========
* 基於 Trie 樹結構實現高效的詞圖掃描，生成句子中漢字所有可能成詞情況所構成的有向無環圖（DAG)
* 採用了動態規劃查找最大概率路徑, 找出基於詞頻的最大切分組合
* 對於未登錄詞，採用了基於漢字成詞能力的 HMM 模型，使用了 Viterbi 算法
* BEMS 的解釋 [https://github.com/fxsjy/jieba/issues/7](https://github.com/fxsjy/jieba/issues/7)

Interface
========
* 組件只提供 jieba.cut 方法用於分詞
* cut 方法接受兩個輸入參數: 1) 第一個參數為需要分詞的字符串 2）cut_all 參數用來控制分詞模式
* 待分詞的字符串可以是 utf-8 字符串
* jieba.cut 返回的結構是一個可迭代的 array

功能 1)：分词
============
* `cut` 方法接受想個輸入參數： 1) 第一個參數為需要分詞的字符串 2）cut_all 參數用來控制分詞模式
* `cutForSearch` 方法接受一個參數：需要分詞的字符串，該方法適合用於搜索引擎構建倒排索引的分詞，粒度比較細
* 注意：待分詞的字符串是 utf-8 字符串
* `cut` 以及 `cutForSearch` 返回的結構是一個可迭代的 array

代碼示例 (Tutorial)

```php
ini_set('memory_limit', '1024M');

require_once "/path/to/your/vendor/multi-array/MultiArray.php";
require_once "/path/to/your/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once "/path/to/your/class/Jieba.php";
require_once "/path/to/your/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init();
Finalseg::init();

$seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = Jieba::cut("我来到北京清华大学", true);
var_dump($seg_list); #全模式

$seg_list = Jieba::cut("我来到北京清华大学", false);
var_dump($seg_list); #默認精確模式

$seg_list = Jieba::cut("他来到了网易杭研大厦");
var_dump($seg_list);

$seg_list = Jieba::cutForSearch("小明硕士毕业于中国科学院计算所，后在日本京都大学深造"); #搜索引擎模式
var_dump($seg_list);
```

Output:

```php
array(7) {
  [0]=>
  string(12) "怜香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "对象"
  [6]=>
  string(3) "啊"
}

Full Mode:
array(15) {
  [0]=>
  string(3) "我"
  [1]=>
  string(3) "来"
  [2]=>
  string(6) "来到"
  [3]=>
  string(3) "到"
  [4]=>
  string(3) "北"
  [5]=>
  string(6) "北京"
  [6]=>
  string(3) "京"
  [7]=>
  string(3) "清"
  [8]=>
  string(6) "清华"
  [9]=>
  string(12) "清华大学"
  [10]=>
  string(3) "华"
  [11]=>
  string(6) "华大"
  [12]=>
  string(3) "大"
  [13]=>
  string(6) "大学"
  [14]=>
  string(3) "学"
}

Default Mode:
array(4) {
  [0]=>
  string(3) "我"
  [1]=>
  string(6) "来到"
  [2]=>
  string(6) "北京"
  [3]=>
  string(12) "清华大学"
}
array(6) {
  [0]=>
  string(3) "他"
  [1]=>
  string(6) "来到"
  [2]=>
  string(3) "了"
  [3]=>
  string(6) "网易"
  [4]=>
  string(6) "杭研"
  [5]=>
  string(6) "大厦"
}
(此處，“杭研“並沒有在詞典中，但是也被 Viterbi 算法識別出來了)

Search Engine Mode:
array(18) {
  [0]=>
  string(6) "小明"
  [1]=>
  string(6) "硕士"
  [2]=>
  string(6) "毕业"
  [3]=>
  string(3) "于"
  [4]=>
  string(6) "中国"
  [5]=>
  string(6) "科学"
  [6]=>
  string(6) "学院"
  [7]=>
  string(9) "科学院"
  [8]=>
  string(15) "中国科学院"
  [9]=>
  string(6) "计算"
  [10]=>
  string(9) "计算所"
  [11]=>
  string(3) "后"
  [12]=>
  string(3) "在"
  [13]=>
  string(6) "日本"
  [14]=>
  string(6) "京都"
  [15]=>
  string(6) "大学"
  [16]=>
  string(18) "日本京都大学"
  [17]=>
  string(6) "深造"
}
```

功能 2)：添加自定義詞典
====================

* 開發者可以指定自己自定義的詞典，以便包含 jieba 詞庫裡沒有的詞。雖然 jieba 有新詞識別能力，但是自行添加新詞可以保證更高的正確率
* 用法： Jieba::loadUserDict(file_name) # file_name 為自定義詞典的絕對路徑
* 詞典格式和 dict.txt 一樣，一個詞佔一行；每一行分為三部分，一部分為詞語，一部分為詞頻，一部分為詞性，用空格隔開
* 範例：

  云计算 5 n
  李小福 2 n
  创新办 3 n

  之前： 李小福 / 是 / 创新 / 办 / 主任 / 也 / 是 / 云 / 计算 / 方面 / 的 / 专家 /
  加載自定義詞庫後：　李小福 / 是 / 创新办 / 主任 / 也 / 是 / 云计算 / 方面 / 的 / 专家 /

說明："通过用户自定义词典来增强歧义纠错能力" --- https://github.com/fxsjy/jieba/issues/14

* 自定義詞典：https://github.com/fukuball/jieba-php/blob/master/src/dict/user_dict.txt

功能 3)：關鍵詞提取
==============
* JiebaAnalyse::extractTags($content, $top_k)
* content 為待提取的文本
* top_k 為返回幾個 TF/IDF 權重最大的關鍵詞，默認值為 20
* 可使用 setStopWords 增加自定義 stop words

代碼示例 (關鍵詞提取)

```php
ini_set('memory_limit', '600M');

require_once "/path/to/your/vendor/multi-array/MultiArray.php";
require_once "/path/to/your/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once "/path/to/your/class/Jieba.php";
require_once "/path/to/your/class/Finalseg.php";
require_once "/path/to/your/class/JiebaAnalyse.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;
Jieba::init(array('mode'=>'test','dict'=>'small'));
Finalseg::init();
JiebaAnalyse::init();

$top_k = 10;
$content = file_get_contents("/path/to/your/dict/lyric.txt", "r");

$tags = JiebaAnalyse::extractTags($content, $top_k);

var_dump($tags);

JiebaAnalyse::setStopWords('/path/to/your/dict/stop_words.txt');

$tags = JiebaAnalyse::extractTags($content, $top_k);

var_dump($tags);
```

Output:
```php
array(10) {
  '沒有' =>
  double(1.0592831964595)
  '所謂' =>
  double(0.90795702553671)
  '是否' =>
  double(0.66385043195443)
  '一般' =>
  double(0.54607060161899)
  '雖然' =>
  double(0.30265234184557)
  '來說' =>
  double(0.30265234184557)
  '肌迫' =>
  double(0.30265234184557)
  '退縮' =>
  double(0.30265234184557)
  '矯作' =>
  double(0.30265234184557)
  '怯懦' =>
  double(0.24364586159392)
}
array(10) {
  '所謂' =>
  double(1.1569129841516)
  '一般' =>
  double(0.69579963754677)
  '矯作' =>
  double(0.38563766138387)
  '來說' =>
  double(0.38563766138387)
  '退縮' =>
  double(0.38563766138387)
  '雖然' =>
  double(0.38563766138387)
  '肌迫' =>
  double(0.38563766138387)
  '怯懦' =>
  double(0.31045198493419)
  '隨便說說' =>
  double(0.19281883069194)
  '一場' =>
  double(0.19281883069194)
}
```

功能 4)：詞性分詞
==============
* 詞性說明：[https://gist.github.com/luw2007/6016931](https://gist.github.com/luw2007/6016931)

代碼示例 (Tutorial)

```php
ini_set('memory_limit', '600M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
require_once dirname(dirname(__FILE__))."/class/Posseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;
Jieba::init();
Finalseg::init();
Posseg::init();

$seg_list = Posseg::cut("这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。");
var_dump($seg_list);
```

Output:
```php
array(21) {
  [0]=>
  array(2) {
    ["word"]=>
    string(3) "这"
    ["tag"]=>
    string(1) "r"
  }
  [1]=>
  array(2) {
    ["word"]=>
    string(3) "是"
    ["tag"]=>
    string(1) "v"
  }
  [2]=>
  array(2) {
    ["word"]=>
    string(6) "一个"
    ["tag"]=>
    string(1) "m"
  }
  [3]=>
  array(2) {
    ["word"]=>
    string(18) "伸手不见五指"
    ["tag"]=>
    string(1) "i"
  }
  [4]=>
  array(2) {
    ["word"]=>
    string(3) "的"
    ["tag"]=>
    string(2) "uj"
  }
  [5]=>
  array(2) {
    ["word"]=>
    string(6) "黑夜"
    ["tag"]=>
    string(1) "n"
  }
  [6]=>
  array(2) {
    ["word"]=>
    string(3) "。"
    ["tag"]=>
    string(1) "x"
  }
  [7]=>
  array(2) {
    ["word"]=>
    string(3) "我"
    ["tag"]=>
    string(1) "r"
  }
  [8]=>
  array(2) {
    ["word"]=>
    string(3) "叫"
    ["tag"]=>
    string(1) "v"
  }
  [9]=>
  array(2) {
    ["word"]=>
    string(9) "孙悟空"
    ["tag"]=>
    string(2) "nr"
  }
  [10]=>
  array(2) {
    ["word"]=>
    string(3) "，"
    ["tag"]=>
    string(1) "x"
  }
  [11]=>
  array(2) {
    ["word"]=>
    string(3) "我"
    ["tag"]=>
    string(1) "r"
  }
  [12]=>
  array(2) {
    ["word"]=>
    string(3) "爱"
    ["tag"]=>
    string(1) "v"
  }
  [13]=>
  array(2) {
    ["word"]=>
    string(6) "北京"
    ["tag"]=>
    string(2) "ns"
  }
  [14]=>
  array(2) {
    ["word"]=>
    string(3) "，"
    ["tag"]=>
    string(1) "x"
  }
  [15]=>
  array(2) {
    ["word"]=>
    string(3) "我"
    ["tag"]=>
    string(1) "r"
  }
  [16]=>
  array(2) {
    ["word"]=>
    string(3) "爱"
    ["tag"]=>
    string(1) "v"
  }
  [17]=>
  array(2) {
    ["word"]=>
    string(6) "Python"
    ["tag"]=>
    string(3) "eng"
  }
  [18]=>
  array(2) {
    ["word"]=>
    string(3) "和"
    ["tag"]=>
    string(1) "c"
  }
  [19]=>
  array(2) {
    ["word"]=>
    string(3) "C++"
    ["tag"]=>
    string(3) "eng"
  }
  [20]=>
  array(2) {
    ["word"]=>
    string(3) "。"
    ["tag"]=>
    string(1) "x"
  }
}
```

功能 5)：切換成繁體字典
==============

代碼示例 (Tutorial)

```php
ini_set('memory_limit', '1024M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init(array('mode'=>'default','dict'=>'big'));
Finalseg::init();

$seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = Jieba::cut("憐香惜玉也得要看對象啊！");
var_dump($seg_list);
```

Output:

```php
array(7) {
  [0]=>
  string(12) "怜香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "对象"
  [6]=>
  string(3) "啊"
}
array(7) {
  [0]=>
  string(12) "憐香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "對象"
  [6]=>
  string(3) "啊"
}
```

功能 5)：切換成繁體字典
==============

代碼示例 (Tutorial)

```php
ini_set('memory_limit', '1024M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init(array('mode'=>'default','dict'=>'big'));
Finalseg::init();

$seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = Jieba::cut("憐香惜玉也得要看對象啊！");
var_dump($seg_list);
```

Output:

```php
array(7) {
  [0]=>
  string(12) "怜香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "对象"
  [6]=>
  string(3) "啊"
}
array(7) {
  [0]=>
  string(12) "憐香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "對象"
  [6]=>
  string(3) "啊"
}
```

功能 6)：保留日语或者朝鲜语原文不进行过滤
==============

代碼示例 (Tutorial)

```php
ini_set('memory_limit', '1024M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init(array('cjk'=>'all'));
Finalseg::init();

$seg_list = Jieba::cut("한국어 또는 조선말은 제주특별자치도를 제외한 한반도 및 그 부속 도서와 한민족 거주 지역에서 쓰이는 언어로");
var_dump($seg_list);

$seg_list = Jieba::cut("日本語は、主に日本国内や日本人同士の間で使われている言語である。");
var_dump($seg_list);

// 加载日语词库可以对日语进行简单的分词
Jieba::loadUserDict("/path/to/your/japanese/dict.txt");
$seg_list = Jieba::cut("日本語は、主に日本国内や日本人同士の間で使われている言語である。");
var_dump($seg_list);
```

Output:

```php
array(15) {
  [0]=>
  string(9) "한국어"
  [1]=>
  string(6) "또는"
  [2]=>
  string(12) "조선말은"
  [3]=>
  string(24) "제주특별자치도를"
  [4]=>
  string(9) "제외한"
  [5]=>
  string(9) "한반도"
  [6]=>
  string(3) "및"
  [7]=>
  string(3) "그"
  [8]=>
  string(6) "부속"
  [9]=>
  string(9) "도서와"
  [10]=>
  string(9) "한민족"
  [11]=>
  string(6) "거주"
  [12]=>
  string(12) "지역에서"
  [13]=>
  string(9) "쓰이는"
  [14]=>
  string(9) "언어로"
}
array(21) {
  [0]=>
  string(6) "日本"
  [1]=>
  string(3) "語"
  [2]=>
  string(3) "は"
  [3]=>
  string(3) "主"
  [4]=>
  string(3) "に"
  [5]=>
  string(6) "日本"
  [6]=>
  string(6) "国内"
  [7]=>
  string(3) "や"
  [8]=>
  string(6) "日本"
  [9]=>
  string(3) "人"
  [10]=>
  string(6) "同士"
  [11]=>
  string(3) "の"
  [12]=>
  string(3) "間"
  [13]=>
  string(3) "で"
  [14]=>
  string(3) "使"
  [15]=>
  string(3) "わ"
  [16]=>
  string(6) "れて"
  [17]=>
  string(6) "いる"
  [18]=>
  string(6) "言語"
  [19]=>
  string(3) "で"
  [20]=>
  string(6) "ある"
}
array(17) {
  [0]=>
  string(9) "日本語"
  [1]=>
  string(3) "は"
  [2]=>
  string(6) "主に"
  [3]=>
  string(9) "日本国"
  [4]=>
  string(3) "内"
  [5]=>
  string(3) "や"
  [6]=>
  string(9) "日本人"
  [7]=>
  string(6) "同士"
  [8]=>
  string(3) "の"
  [9]=>
  string(3) "間"
  [10]=>
  string(3) "で"
  [11]=>
  string(3) "使"
  [12]=>
  string(3) "わ"
  [13]=>
  string(6) "れて"
  [14]=>
  string(6) "いる"
  [15]=>
  string(6) "言語"
  [16]=>
  string(9) "である"
}
```

功能 7)：返回詞語在原文的起止位置
==============

代碼示例 (Tutorial)

```php
ini_set('memory_limit', '1024M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init(array('mode'=>'test','dict'=>'big'));
Finalseg::init();

$seg_list = Jieba::tokenize("永和服装饰品有限公司");
var_dump($seg_list);
```

Output:

```php
array(4) {
  [0] =>
  array(3) {
    'word' =>
    string(6) "永和"
    'start' =>
    int(0)
    'end' =>
    int(2)
  }
  [1] =>
  array(3) {
    'word' =>
    string(6) "服装"
    'start' =>
    int(2)
    'end' =>
    int(4)
  }
  [2] =>
  array(3) {
    'word' =>
    string(6) "饰品"
    'start' =>
    int(4)
    'end' =>
    int(6)
  }
  [3] =>
  array(3) {
    'word' =>
    string(12) "有限公司"
    'start' =>
    int(6)
    'end' =>
    int(10)
  }
}
```

其他詞典
========
1) 佔用內容較小的詞典
https://github.com/fukuball/jieba-php/blob/master/src/dict/dict.small.txt

2) 支持繁體斷詞的詞典
https://github.com/fukuball/jieba-php/blob/master/src/dict/dict.big.txt

常見問題
========
1) 模型的數據是如何生成的？ https://github.com/fxsjy/jieba/issues/7
2) 這個庫的授權是？ https://github.com/fxsjy/jieba/issues/2


jieba-php English Document
=========

Online Demo
========
* Demo Site Url：[http://jieba-php.fukuball.com](http://jieba-php.fukuball.com)
* Demo Site Repo：[https://github.com/fukuball/jieba-php.fukuball.com](https://github.com/fukuball/jieba-php.fukuball.com)

Feature
========
* Support three types of segmentation mode:
* 1) Accurate Mode, attempt to cut the sentence into the most accurate segmentation, which is suitable for text analysis;
* 2) Full Mode, break the words of the sentence into words scanned
* 3) Search Engine Mode, based on the Accurate Mode, with an attempt to cut the long words into several short words, which can enhance the recall rate

Usage
========
* Installation: Use composer to install jieba-php, then require the autoload file to use jieba-php.

Algorithm
========
* Based on the Trie tree structure to achieve efficient word graph scanning; sentences using Chinese characters constitute a directed acyclic graph (DAG).
* Employs memory search to calculate the maximum probability path, in order to identify the maximum tangential points based on word frequency combination.
* For unknown words, the character position HMM-based model is used, using the Viterbi algorithm.
* The meaning of BEMS [https://github.com/fxsjy/jieba/issues/7](https://github.com/fxsjy/jieba/issues/7).

Interface
========
* The `cut` method accepts two parameters: 1) first parameter is the string to segmentation 2）the second parameter `cut_all` to control segmentation mode.
* The string to segmentation may use utf-8 string.
* `cutForSearch` accpets only on parameter: the string that requires segmentation, and it will cut the sentence into short words
* `cut` and `cutForSearch` return an segmented array.

Function 1) Segmentation
============

Example (Tutorial)

```php
ini_set('memory_limit', '1024M');

require_once "/path/to/your/vendor/multi-array/MultiArray.php";
require_once "/path/to/your/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once "/path/to/your/class/Jieba.php";
require_once "/path/to/your/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init();
Finalseg::init();

$seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

seg_list = jieba.cut("我来到北京清华大学", true)
var_dump($seg_list); #全模式

seg_list = jieba.cut("我来到北京清华大学", false)
var_dump($seg_list); #默認精確模式

seg_list = jieba.cut("他来到了网易杭研大厦")
var_dump($seg_list);

seg_list = jieba.cut_for_search("小明硕士毕业于中国科学院计算所，后在日本京都大学深造") #搜索引擎模式
var_dump($seg_list);
```

Output:

```php
array(7) {
  [0]=>
  string(12) "怜香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "对象"
  [6]=>
  string(3) "啊"
}

Full Mode:
array(15) {
  [0]=>
  string(3) "我"
  [1]=>
  string(3) "来"
  [2]=>
  string(6) "来到"
  [3]=>
  string(3) "到"
  [4]=>
  string(3) "北"
  [5]=>
  string(6) "北京"
  [6]=>
  string(3) "京"
  [7]=>
  string(3) "清"
  [8]=>
  string(6) "清华"
  [9]=>
  string(12) "清华大学"
  [10]=>
  string(3) "华"
  [11]=>
  string(6) "华大"
  [12]=>
  string(3) "大"
  [13]=>
  string(6) "大学"
  [14]=>
  string(3) "学"
}

Default Mode:
array(4) {
  [0]=>
  string(3) "我"
  [1]=>
  string(6) "来到"
  [2]=>
  string(6) "北京"
  [3]=>
  string(12) "清华大学"
}
array(6) {
  [0]=>
  string(3) "他"
  [1]=>
  string(6) "来到"
  [2]=>
  string(3) "了"
  [3]=>
  string(6) "网易"
  [4]=>
  string(6) "杭研"
  [5]=>
  string(6) "大厦"
}
(此處，“杭研“並沒有在詞典中，但是也被 Viterbi 算法識別出來了)

Search Engine Mode:
array(18) {
  [0]=>
  string(6) "小明"
  [1]=>
  string(6) "硕士"
  [2]=>
  string(6) "毕业"
  [3]=>
  string(3) "于"
  [4]=>
  string(6) "中国"
  [5]=>
  string(6) "科学"
  [6]=>
  string(6) "学院"
  [7]=>
  string(9) "科学院"
  [8]=>
  string(15) "中国科学院"
  [9]=>
  string(6) "计算"
  [10]=>
  string(9) "计算所"
  [11]=>
  string(3) "后"
  [12]=>
  string(3) "在"
  [13]=>
  string(6) "日本"
  [14]=>
  string(6) "京都"
  [15]=>
  string(6) "大学"
  [16]=>
  string(18) "日本京都大学"
  [17]=>
  string(6) "深造"
}
```

Function 2) Add a custom dictionary
====================

* Developers can specify their own custom dictionary to include in the jieba thesaurus. jieba has the ability to identify new words, but adding your own new words can ensure a higher rate of correct segmentation.
* Usage: `Jieba::loadUserDict(file_name)` # file_name is a custom dictionary path.
* The dictionary format is the same as that of `dict.txt`: one word per line; each line is divided into two parts, the first is the word itself, the other is the word frequency, separated by a space.
* Example:

  云计算 5
  李小福 2
  创新办 3

  之前： 李小福 / 是 / 创新 / 办 / 主任 / 也 / 是 / 云 / 计算 / 方面 / 的 / 专家 /
  加載自定義詞庫後：　李小福 / 是 / 创新办 / 主任 / 也 / 是 / 云计算 / 方面 / 的 / 专家 /

Function 3) Keyword Extraction
==============
* JiebaAnalyse::extractTags($content, $top_k)
* content: the text to be extracted
* top_k: to return several TF/IDF weights for the biggest keywords, the default value is 20

Example (keyword extraction)

```php
ini_set('memory_limit', '600M');

require_once "/path/to/your/vendor/multi-array/MultiArray.php";
require_once "/path/to/your/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once "/path/to/your/class/Jieba.php";
require_once "/path/to/your/class/Finalseg.php";
require_once "/path/to/your/class/JiebaAnalyse.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;
Jieba::init(array('mode'=>'test','dict'=>'small'));
Finalseg::init();
JiebaAnalyse::init();

$top_k = 10;
$content = file_get_contents("/path/to/your/dict/lyric.txt", "r");

$tags = JiebaAnalyse::extractTags($content, $top_k);

var_dump($tags);
```

Output:
```php
array(10) {
  ["是否"]=>
  float(1.2196321889395)
  ["一般"]=>
  float(1.0032459890209)
  ["肌迫"]=>
  float(0.64654314660465)
  ["怯懦"]=>
  float(0.44762844339349)
  ["藉口"]=>
  float(0.32327157330233)
  ["逼不得已"]=>
  float(0.32327157330233)
  ["不安全感"]=>
  float(0.26548304656279)
  ["同感"]=>
  float(0.23929673812326)
  ["有把握"]=>
  float(0.21043366018744)
  ["空洞"]=>
  float(0.20598261709442)
}

```
Function 4) Word Segmentation and Tagging
==============
* Word Tagging Meaning：[https://gist.github.com/luw2007/6016931](https://gist.github.com/luw2007/6016931)

Example (word tagging)

```php
ini_set('memory_limit', '600M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
require_once dirname(dirname(__FILE__))."/class/Posseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;
Jieba::init();
Finalseg::init();
Posseg::init();

$seg_list = Posseg::cut("这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。");
var_dump($seg_list);
```

Output:
```php
array(21) {
  [0]=>
  array(2) {
    ["word"]=>
    string(3) "这"
    ["tag"]=>
    string(1) "r"
  }
  [1]=>
  array(2) {
    ["word"]=>
    string(3) "是"
    ["tag"]=>
    string(1) "v"
  }
  [2]=>
  array(2) {
    ["word"]=>
    string(6) "一个"
    ["tag"]=>
    string(1) "m"
  }
  [3]=>
  array(2) {
    ["word"]=>
    string(18) "伸手不见五指"
    ["tag"]=>
    string(1) "i"
  }
  [4]=>
  array(2) {
    ["word"]=>
    string(3) "的"
    ["tag"]=>
    string(2) "uj"
  }
  [5]=>
  array(2) {
    ["word"]=>
    string(6) "黑夜"
    ["tag"]=>
    string(1) "n"
  }
  [6]=>
  array(2) {
    ["word"]=>
    string(3) "。"
    ["tag"]=>
    string(1) "w"
  }
  [7]=>
  array(2) {
    ["word"]=>
    string(3) "我"
    ["tag"]=>
    string(1) "r"
  }
  [8]=>
  array(2) {
    ["word"]=>
    string(3) "叫"
    ["tag"]=>
    string(1) "v"
  }
  [9]=>
  array(2) {
    ["word"]=>
    string(9) "孙悟空"
    ["tag"]=>
    string(2) "nr"
  }
  [10]=>
  array(2) {
    ["word"]=>
    string(3) "，"
    ["tag"]=>
    string(1) "w"
  }
  [11]=>
  array(2) {
    ["word"]=>
    string(3) "我"
    ["tag"]=>
    string(1) "r"
  }
  [12]=>
  array(2) {
    ["word"]=>
    string(3) "爱"
    ["tag"]=>
    string(1) "v"
  }
  [13]=>
  array(2) {
    ["word"]=>
    string(6) "北京"
    ["tag"]=>
    string(2) "ns"
  }
  [14]=>
  array(2) {
    ["word"]=>
    string(3) "，"
    ["tag"]=>
    string(1) "w"
  }
  [15]=>
  array(2) {
    ["word"]=>
    string(3) "我"
    ["tag"]=>
    string(1) "r"
  }
  [16]=>
  array(2) {
    ["word"]=>
    string(3) "爱"
    ["tag"]=>
    string(1) "v"
  }
  [17]=>
  array(2) {
    ["word"]=>
    string(6) "Python"
    ["tag"]=>
    string(3) "eng"
  }
  [18]=>
  array(2) {
    ["word"]=>
    string(3) "和"
    ["tag"]=>
    string(1) "c"
  }
  [19]=>
  array(2) {
    ["word"]=>
    string(3) "C++"
    ["tag"]=>
    string(3) "eng"
  }
  [20]=>
  array(2) {
    ["word"]=>
    string(3) "。"
    ["tag"]=>
    string(1) "w"
  }
}
```

Function 5)：Use Traditional Chinese
==============

Example (Tutorial)

```php
ini_set('memory_limit', '1024M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init(array('mode'=>'default','dict'=>'big'));
Finalseg::init();

$seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = Jieba::cut("憐香惜玉也得要看對象啊！");
var_dump($seg_list);
```

Output:

```php
array(7) {
  [0]=>
  string(12) "怜香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "对象"
  [6]=>
  string(3) "啊"
}
array(7) {
  [0]=>
  string(12) "憐香惜玉"
  [1]=>
  string(3) "也"
  [2]=>
  string(3) "得"
  [3]=>
  string(3) "要"
  [4]=>
  string(3) "看"
  [5]=>
  string(6) "對象"
  [6]=>
  string(3) "啊"
}
```

Function 6)：Keeping Japanese or Korean original text
==============

Example (Tutorial)

```php
ini_set('memory_limit', '1024M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init(array('cjk'=>'all'));
Finalseg::init();

$seg_list = Jieba::cut("한국어 또는 조선말은 제주특별자치도를 제외한 한반도 및 그 부속 도서와 한민족 거주 지역에서 쓰이는 언어로");
var_dump($seg_list);

$seg_list = Jieba::cut("日本語は、主に日本国内や日本人同士の間で使われている言語である。");
var_dump($seg_list);

// Loading custom Japanese dictionary can do a simple word segmentation
Jieba::loadUserDict("/path/to/your/japanese/dict.txt");
$seg_list = Jieba::cut("日本語は、主に日本国内や日本人同士の間で使われている言語である。");
var_dump($seg_list);
```

Output:

```php
array(15) {
  [0]=>
  string(9) "한국어"
  [1]=>
  string(6) "또는"
  [2]=>
  string(12) "조선말은"
  [3]=>
  string(24) "제주특별자치도를"
  [4]=>
  string(9) "제외한"
  [5]=>
  string(9) "한반도"
  [6]=>
  string(3) "및"
  [7]=>
  string(3) "그"
  [8]=>
  string(6) "부속"
  [9]=>
  string(9) "도서와"
  [10]=>
  string(9) "한민족"
  [11]=>
  string(6) "거주"
  [12]=>
  string(12) "지역에서"
  [13]=>
  string(9) "쓰이는"
  [14]=>
  string(9) "언어로"
}
array(21) {
  [0]=>
  string(6) "日本"
  [1]=>
  string(3) "語"
  [2]=>
  string(3) "は"
  [3]=>
  string(3) "主"
  [4]=>
  string(3) "に"
  [5]=>
  string(6) "日本"
  [6]=>
  string(6) "国内"
  [7]=>
  string(3) "や"
  [8]=>
  string(6) "日本"
  [9]=>
  string(3) "人"
  [10]=>
  string(6) "同士"
  [11]=>
  string(3) "の"
  [12]=>
  string(3) "間"
  [13]=>
  string(3) "で"
  [14]=>
  string(3) "使"
  [15]=>
  string(3) "わ"
  [16]=>
  string(6) "れて"
  [17]=>
  string(6) "いる"
  [18]=>
  string(6) "言語"
  [19]=>
  string(3) "で"
  [20]=>
  string(6) "ある"
}
array(17) {
  [0]=>
  string(9) "日本語"
  [1]=>
  string(3) "は"
  [2]=>
  string(6) "主に"
  [3]=>
  string(9) "日本国"
  [4]=>
  string(3) "内"
  [5]=>
  string(3) "や"
  [6]=>
  string(9) "日本人"
  [7]=>
  string(6) "同士"
  [8]=>
  string(3) "の"
  [9]=>
  string(3) "間"
  [10]=>
  string(3) "で"
  [11]=>
  string(3) "使"
  [12]=>
  string(3) "わ"
  [13]=>
  string(6) "れて"
  [14]=>
  string(6) "いる"
  [15]=>
  string(6) "言語"
  [16]=>
  string(9) "である"
}
```

詞性說明
==============
```
a 形容词 (取英语形容词 adjective 的第 1 个字母。)
  ad 副形词 (直接作状语的形容词，形容词代码 a 和副词代码 d 并在一起。)
  ag 形容词性语素 (形容词性语素，形容词代码为 a，语素代码 ｇ 前面置以 a。)
  an 名形词 (具有名词功能的形容词，形容词代码 a 和名词代码 n 并在一起。)
b 区别词 (取汉字「别」的声母。)
c 连词 (取英语连词 conjunction 的第 1 个字母。)
d 副词 (取 adverb 的第 2 个字母，因其第 1 个字母已用于形容词。)
  df 副词*
  dg 副语素 (副词性语素，副词代码为 d，语素代码 ｇ 前面置以 d。)
e 叹词 (取英语叹词 exclamation 的第 1 个字母。)
eng 外语
f 方位词 (取汉字「方」的声母。)
g 语素 (绝大多数语素都能作为合成词的「词根」，取汉字「根」的声母。)
h 前接成分 (取英语 head 的第 1 个字母。)
i 成语 (取英语成语 idiom 的第 1 个字母。)
j 简称略语 (取汉字「简」的声母。)
k 后接成分
l 习用语 (习用语尚未成为成语，有点「临时性」，取「临」的声母。)
m 数词 (取英语 numeral 的第 3 个字母，n，u 已有他用。)
  mg 数语素
  mq 数词*
n 名词 (取英语名词 noun 的第 1 个字母。)
  ng 名语素 (名词性语素，名词代码为 n，语素代码 ｇ 前面置以 n。)
  nr 人名 (名词代码n和「人(ren)」的声母并在一起。)
  nrfg 名词*
  nrt 名词*
  ns 地名 (名词代码 n 和处所词代码 s 并在一起。)
  nt 机构团体 (「团」的声母为 t，名词代码 n 和 t 并在一起。)
  nz 其他专名 (「专」的声母的第 1 个字母为 z，名词代码 n 和 z 并在一起。)
o 拟声词 (取英语拟声词 onomatopoeia 的第 1 个字母。)
p 介词 (取英语介词 prepositional 的第 1 个字母。)
q 量词 (取英语 quantity 的第 1 个字母。)
r 代词 (取英语代词 pronoun的 第 2 个字母，因 p 已用于介词。)
  rg 代词语素
  rr 代词*
  rz 代词*
s 处所词 (取英语 space 的第 1 个字母。)
t 时间词 (取英语 time 的第 1 个字母。)
  tg 时语素 (时间词性语素，时间词代码为 t，在语素的代码 g 前面置以 t。)
u 助词 (取英语助词 auxiliary 的第 2 个字母，因 a 已用于形容词。)
  ud 助词*
  ug 助词*
  uj 助词*
  ul 助词*
  uv 助词*
  uz 助词*
v 动词 (取英语动词 verb 的第一个字母。)
  vd 副动词 (直接作状语的动词，动词和副词的代码并在一起。)
  vg 动语素
  vi 动词*
  vn 名动词 (指具有名词功能的动词，动词和名词的代码并在一起。)
  vq 动词*
w 标点符号
x 非语素字 (非语素字只是一个符号，字母 x 通常用于代表未知数、符号。)
y 语气词 (取汉字「语」的声母。)
z 状态词 (取汉字「状」的声母的前一个字母。)
  zg 状态词*
```

Donate
=========
If you find fuku-ml useful, please consider a donation. Thank you!

- bitcoin: 1BbihQU3CzSdyLSP9bvQq7Pi1z1jTdAaq9
- eth: 0x92DA3F837bf2F79D422bb8CEAC632208F94cdE33

License
=========
The MIT License (MIT)

Copyright (c) 2015 fukuball

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
