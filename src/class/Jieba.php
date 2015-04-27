<?php
/**
 * Jieba.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  GIT: <fukuball/iloveck101>
 * @link     https://github.com/fukuball/iloveck101
 */

/**
 * Jieba
 *
 * @category PHP
 * @package  /class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  Release: <0.0.1>
 * @link     https://github.com/fukuball/iloveck101
 */
class Jieba
{

    public static $total = 0.0;
    public static $trie = array();
    public static $FREQ = array();
    public static $min_freq = 0.0;
    public static $route = array();

    /**
     * Static method init
     *
     * @param array $options # other options
     *
     * @return void
     */
    public static function init($options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        echo "Building Trie...\n";

        $t1 = microtime(true);
        self::$trie = Jieba::gen_trie(dirname(dirname(__FILE__))."/dict/dict2.txt");
        foreach (self::$FREQ as $key=>$value) {
            self::$FREQ[$key] = ($value/self::$total);
        }
        self::$min_freq = min(self::$FREQ);

        echo "loading model cost ".(microtime(true) - $t1)." seconds.\n";
        echo "Trie has been built succesfully.\n";

    }// end function init

    /**
     * Static method calc
     *
     * @param string $sentence # input sentence
     * @param array  $DAG      # DAG
     * @param array  $options  # other options
     *
     * @return array self::$route
     */
    public static function calc($sentence, $DAG, $options=array())
    {

        $N = mb_strlen($sentence, 'UTF-8');
        self::$route = array();
        self::$route[$N] = array($N => 1.0);

        $previous_key = $N;
        for ($i=($N-1); $i>=0; $i--) {
            $w_c = mb_substr($sentence, $i, (($DAG[$i][0]+1)-$i), 'UTF-8');
            $previous_freq = self::$route[$i+1][$previous_key];
            if (isset(self::$FREQ[$w_c])) {
                $current_freq = (float) $previous_freq*self::$FREQ[$w_c]/self::$min_freq;
            } else {
                $current_freq = (float) $previous_freq*self::$min_freq;
            }
            self::$route[$i] = array($DAG[$i][0] => $current_freq);
            $previous_key = $DAG[$i][0];
        }

        return self::$route;

    }// end function calc

    /**
     * Static method gen_trie
     *
     * @param string $f_name  # input f_name
     * @param array  $options # other options
     *
     * @return array self::$trie
     */
    public static function gen_trie($f_name, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        self::$trie = new Tebru\MultiArray(array());

        $content = fopen($f_name, "r");

        while (($line = fgets($content)) !== false) {
            $explode_line = explode(" ", trim($line));
            $word = $explode_line[0];
            $freq = $explode_line[1];
            $freq = (float) $freq;
            self::$FREQ[$word] = $freq;
            self::$total += $freq;
            $l = mb_strlen($word, 'UTF-8');

            $word_c = array();
            for ($i=0; $i<$l; $i++) {
                $c = mb_substr($word, $i, 1, 'UTF-8');
                array_push($word_c, $c);
            }
            $word_c_key = implode('.', $word_c);
            self::$trie->set($word_c_key, array("end"=>""));
        }

        fclose($content);

        return self::$trie;

    }// end function gen_trie

    /**
     * Static method __cut_all
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cut_all($sentence, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $N = mb_strlen($sentence, 'UTF-8');
        $i = 0;
        $j = 0;
        $p = self::$trie;
        $word_c = array();

        while ($i < $N) {
            $c = mb_substr($sentence, $j, 1, 'UTF-8');
            if (count($word_c)==0) {
                $next_word_key = $c;
            } else {
                $next_word_key = implode('.', $word_c).'.'.$c;
            }

            if (self::$trie->exists($next_word_key)) {
                array_push($word_c, $c);
                $next_word_key_value = self::$trie->get($next_word_key);
                if (   $next_word_key_value == array("end"=>"")
                    || in_array(array("end"=>""), $next_word_key_value)
                ) {
                    array_push($words, mb_substr($sentence, $i, (($j+1)-$i), 'UTF-8'));
                }
                $j += 1;
                if ($j >= $N) {
                    $word_c = array();
                    $i += 1;
                    $j = $i;
                }
            } else {
                $word_c = array();
                $i += 1;
                $j = $i;
            }

        }

        return $words;

    }// end function __cut_all

    /**
     * Static method __cut_DAG
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cut_DAG($sentence, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $N = mb_strlen($sentence, 'UTF-8');
        $i = 0;
        $j = 0;
        $p = self::$trie;
        $DAG = array();
        $word_c = array();

        while ($i < $N) {
            $c = mb_substr($sentence, $j, 1, 'UTF-8');
            if (count($word_c)==0) {
                $next_word_key = $c;
            } else {
                $next_word_key = implode('.', $word_c).'.'.$c;
            }

            if (self::$trie->exists($next_word_key)) {
                array_push($word_c, $c);
                $next_word_key_value = self::$trie->get($next_word_key);
                if (   $next_word_key_value == array("end"=>"")
                    || in_array(array("end"=>""), $next_word_key_value)
                ) {
                    //exist and leaf node
                    if (!isset($DAG[$i])) {
                        $DAG[$i] = array();
                    }
                    array_push($DAG[$i], $j);
                }
                $j += 1;
                if ($j >= $N) {
                    $word_c = array();
                    $i += 1;
                    $j = $i;
                }
            } else {
                $word_c = array();
                $i += 1;
                $j = $i;
            }
        }

        for ($i=0; $i<$N; $i++) {
            if (!isset($DAG[$i])) {
                $DAG[$i] = array();
                array_push($DAG[$i], $i);
            }
        }

        self::calc($sentence, $DAG);

        $x = 0;
        $buf = '';

        while ($x < $N) {
            $current_route_keys = array_keys(self::$route[$x]);
            $y = $current_route_keys[0]+1;
            $l_word = mb_substr($sentence, $x, ($y-$x), 'UTF-8');

            if (($y-$x)==1) {
                $buf = $buf.$l_word;
            } else {

                if (mb_strlen($buf, 'UTF-8')>0) {
                    if (mb_strlen($buf, 'UTF-8')==1) {
                        array_push($words, $buf);
                        $buf = '';
                    } else {
                        $regognized = Finalseg::__cut($buf);
                        foreach ($regognized as $key=>$word) {
                            array_push($words, $word);
                        }
                        $buf = '';
                    }

                }
                array_push($words, $l_word);
            }
            $x = $y;
        }

        if (mb_strlen($buf, 'UTF-8')>0) {
            if (mb_strlen($buf, 'UTF-8')==1) {
                array_push($words, $buf);
            } else {
                $regognized = Finalseg::__cut($buf);
                foreach ($regognized as $key=>$word) {
                    array_push($words, $word);
                }
            }
        }

        return $words;

    }// end function __cut_DAG

    /**
     * Static method cut
     *
     * @param string  $sentence # input sentence
     * @param boolean $cut_all  # cut_all or not
     * @param array   $options  # other options
     *
     * @return array $seg_list
     */
    public static function cut($sentence, $cut_all=false, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $seg_list = array();

        $re_han_pattern = '([\x{4E00}-\x{9FA5}]+)';
        $re_skip_pattern = '([a-zA-Z0-9+#\n]+)';
        preg_match_all('/('.$re_han_pattern.'|'.$re_skip_pattern.')/u', $sentence, $matches, PREG_PATTERN_ORDER);
        $blocks = $matches[0];

        foreach ($blocks as $blk) {

            if (preg_match('/'.$re_han_pattern.'/u', $blk)) {

                if ($cut_all) {
                    $words = Jieba::__cut_all($blk);
                } else {
                    $words = Jieba::__cut_DAG($blk);
                }

                foreach ($words as $word) {
                    array_push($seg_list, $word);
                }

            } else {

                array_push($seg_list, $blk);

            }// end else (preg_match('/'.$re_han_pattern.'/u', $blk))


        }// end foreach ($blocks as $blk)

        return $seg_list;

    }// end function cut


}// end of class Jieba

Jieba::init();
?>