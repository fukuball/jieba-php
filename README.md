jieba-php
========
"結巴"中文分詞（PHP 版本翻譯中）：做最好的 PHP 的中文分詞組件
目前翻譯版本為 jieba-0.16 版本，未來再慢慢往上升級，請有興趣的開發者一起加入開發！

Feature
========
* 支持兩種分詞模式：
* 1）默認模式，試圖將句子最精確地切開，適合文本分析；
* 2）全模式，把句子中所有的可以成詞的詞語都掃描出來，適合搜索引擎。

Usage
========
待補充

Algorithm
========
* 基於 Trie 樹結構實現高效的詞圖掃描，生成句子中漢字構成的有向無環圖（DAG)
* 採用了記憶化搜索實現最大概率路徑的計算, 找出基於詞頻的最大切分組合
* 對於未登錄詞，採用了基於漢字位置概率的模型，使用了 Viterbi 算法

Interface
========
* 組件只提供 jieba.cut 方法用於分詞
* cut 方法接受兩個輸入參數: 1) 第一個參數為需要分詞的字符串 2）cut_all 參數用來控制分詞模式
* 待分詞的字符串可以是 gbk 字符串、utf-8 字符串或者 unicode
* jieba.cut 返回的結構是一個可迭代的 generator，可以使用 for 循環來獲得分詞後得到的每一個詞語(unicode)，也可以用 list(jieba.cut(...)) 轉化為 list


代碼示例 (Tutorial)

    #encoding=utf-8
    import jieba

    seg_list = jieba.cut("我来到北京清华大学",cut_all=True)
    print "Full Mode:", "/ ".join(seg_list) #全模式

    seg_list = jieba.cut("我来到北京清华大学",cut_all=False)
    print "Default Mode:", "/ ".join(seg_list) #默認模式

    seg_list = jieba.cut("他来到了网易杭研大厦")
    print ", ".join(seg_list)

Output:

    Full Mode: 我/ 来/ 来到/ 到/ 北/ 北京/ 京/ 清/ 清华/ 清华大学/ 华/ 华大/ 大/ 大学/ 学

    Default Mode: 我/ 来到/ 北京/ 清华大学

    他, 来到, 了, 网易, 杭研, 大厦    (此處，“杭研“並沒有在詞典中，但是也被 Viterbi 算法識別出來了)

Performance
=========
待測試

線上展示
=========
待上線

