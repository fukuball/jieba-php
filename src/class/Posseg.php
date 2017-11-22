<?php
/**
 * Posseg.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  GIT: <fukuball/jieba-php>
 * @link     https://github.com/fukuball/jieba-php
 */

namespace Fukuball\Jieba;

/**
 * Posseg
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  Release: <0.16>
 * @link     https://github.com/fukuball/jieba-php
 */
class Posseg
{
    public static $prob_start = array();
    public static $prob_trans = array();
    public static $prob_emit = array();
    public static $char_state = array();
    public static $word_tag = array();
    public static $pos_tag_readable = array();

    /**
     * Static method init
     *
     * @param array $options # other options
     *
     * @return void
     */
    public static function init($options = array())
    {
        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        self::$prob_start = self::loadModel(dirname(dirname(__FILE__)).'/model/pos/prob_start.json');
        self::$prob_trans = self::loadModel(dirname(dirname(__FILE__)).'/model/pos/prob_trans.json');
        self::$prob_emit = self::loadModel(dirname(dirname(__FILE__)).'/model/pos/prob_emit.json');
        self::$char_state = self::loadModel(dirname(dirname(__FILE__)).'/model/pos/char_state.json');

        if (Jieba::$dictname!="") {
            $content = fopen(dirname(dirname(__FILE__))."/dict/".Jieba::$dictname, "r");
            while (($line = fgets($content)) !== false) {
                $explode_line = explode(" ", trim($line));
                $word = $explode_line[0];
                $freq = $explode_line[1];
                $tag = $explode_line[2];
                self::$word_tag[$word] = $tag;
            }
            fclose($content);
        }


        if (sizeof(Jieba::$user_dictname)!=0) {
            for ($i = 0; $i<sizeof(Jieba::$user_dictname); $i++) {
                $content = fopen(Jieba::$user_dictname[$i], "r");
                while (($line = fgets($content)) !== false) {
                    $explode_line = explode(" ", trim($line));
                    $word = $explode_line[0];
                    $freq = $explode_line[1];
                    $tag = $explode_line[2];
                    self::$word_tag[$word] = $tag;
                }
                fclose($content);
            }
        }

        $content = fopen(dirname(dirname(__FILE__))."/dict/pos_tag_readable.txt", "r");

        while (($line = fgets($content)) !== false) {
            $explode_line = explode(" ", trim($line));
            $tag = $explode_line[0];
            $meaning = $explode_line[1];
            self::$pos_tag_readable[$tag] = $meaning;
        }
        fclose($content);
    }// end function init

    /**
     * Static method loadModel
     *
     * @param string $f_name # input f_name
     * @param array $options # other options
     *
     * @return void
     */
    public static function loadModel($f_name, $options = array())
    {
        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        return json_decode(file_get_contents($f_name), true);
    }// end function loadModel

    /**
     * Static method getTopStates
     *
     * @param array $t_state_v # input t_state_v
     * @param int   $top_k     # input top_k
     * @param array $options   # other options
     *
     * @return array $top_states
     */
    public static function getTopStates($t_state_v, $top_k = 4, $options = array())
    {
        arsort($t_state_v);

        $top_states = array_slice($t_state_v, 0, $top_k);

        return $top_states;
    }// end function getTopStates

    /**
     * Static method viterbi
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $viterbi
     */
    public static function viterbi($sentence, $options = array())
    {
        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $obs = $sentence;
        $states = self::$char_state;
        $V = array();
        $V[0] = array();
        $mem_path = array();
        $mem_path[0] = array();
        $all_states = array_keys(self::$prob_trans);

        $c = mb_substr($obs, 0, 1, 'UTF-8');

        if (isset($states[$c]) && !empty($states[$c])) {
            $c_states = $states[$c];
        } else {
            $c_states = $all_states;
        }

        foreach ($c_states as $key => $state) {
            $y = $state;
            $c = mb_substr($obs, 0, 1, 'UTF-8');
            $prob_emit = 0.0;
            if (isset(self::$prob_emit[$y][$c])) {
                $prob_emit = self::$prob_emit[$y][$c];
            } else {
                $prob_emit = MIN_FLOAT;
            }
            $V[0][$y] = self::$prob_start[$y] + $prob_emit;
            $mem_path[0][$y] = '';
        }

        for ($t=1; $t<mb_strlen($obs, 'UTF-8'); $t++) {
            $c = mb_substr($obs, $t, 1, 'UTF-8');
            $V[$t] = array();
            $mem_path[$t] = array();

            $prev_states = array_keys(self::getTopStates($V[$t-1]));

            $prev_mem_path = array_keys($mem_path[$t-1]);

            $prev_states = array();

            foreach ($prev_mem_path as $mem_path_state) {
                if (count(self::$prob_trans[$mem_path_state])>0) {
                    array_push($prev_states, $mem_path_state);
                }
            }

            $prev_states_expect_next = array();

            foreach ($prev_states as $prev_state) {
                $prev_states_expect_next
                    = array_unique(
                        array_merge(
                            $prev_states_expect_next,
                            array_keys(self::$prob_trans[$prev_state])
                        )
                    );
            }

            $obs_states = array();

            if (isset($states[$c])) {
                $obs_states = $states[$c];
            } else {
                $obs_states = $all_states;
            }

            $obs_states = array_intersect($obs_states, $prev_states_expect_next);

            if (count($obs_states)==0) {
                $obs_states = $all_states;
            }


            foreach ($obs_states as $y) {
                $temp_prob_array = array();
                foreach ($prev_states as $y0) {
                    $prob_trans = 0.0;
                    if (isset(self::$prob_trans[$y0][$y])) {
                        $prob_trans = self::$prob_trans[$y0][$y];
                    } else {
                        $prob_trans = MIN_FLOAT;
                    }
                    $prob_emit = 0.0;
                    if (isset(self::$prob_emit[$y][$c])) {
                        $prob_emit = self::$prob_emit[$y][$c];
                    } else {
                        $prob_emit = MIN_FLOAT;
                    }
                    $temp_prob_array[$y0] = $V[$t-1][$y0] + $prob_trans + $prob_emit;
                }
                arsort($temp_prob_array);
                $max_prob = reset($temp_prob_array);
                $max_key = key($temp_prob_array);
                $V[$t][$y] = $max_prob;
                $mem_path[$t][$y] = $max_key;
            }
        }

        $last = array();
        $mem_path_end_keys = array_keys(end($mem_path));

        foreach ($mem_path_end_keys as $y) {
            $end_array = end($V);
            $last[$y] = $end_array[$y];
        }

        arsort($last);
        $return_prob = reset($last);
        $return_prob_key = key($last);

        $obs_length = mb_strlen($obs, 'UTF-8');

        $route = array();
        for ($t=0; $t<$obs_length; $t++) {
            array_push($route, 'None');
        }

        $i = $obs_length-1;

        while ($i >= 0) {
            $route[$i] = $return_prob_key;
            $return_prob_key = $mem_path[$i][$return_prob_key];
            $i-=1;
        }

        return array("prob"=>$return_prob, "pos_list"=>$route);
    }// end function viterbi

    /**
     * Static method __cut
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cut($sentence, $options = array("HMM" => true))
    {
        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $viterbi_array = self::viterbi($sentence);

        $prob = $viterbi_array['prob'];
        $pos_list = $viterbi_array['pos_list'];

        $begin = 0;
        $next = 0;
        $len = mb_strlen($sentence, 'UTF-8');

        for ($i=0; $i<$len; $i++) {
            $char = mb_substr($sentence, $i, 1, 'UTF-8');
            eval('$pos_array = array'.$pos_list[$i].';');
            $pos = $pos_array[0];

            if ($pos=='B') {
                $begin = $i;
            } elseif ($pos=='E') {
                eval('$this_pos_array = array'.$pos_list[$i].';');
                $this_pos = $this_pos_array[1];
                $this_word_pair = array(
                    "word"=>mb_substr($sentence, $begin, (($i+1)-$begin), 'UTF-8'),
                    "tag"=>$this_pos
                );
                array_push($words, $this_word_pair);
                $next = $i+1;
            } elseif ($pos=='S') {
                eval('$this_pos_array = array'.$pos_list[$i].';');
                $this_pos = $this_pos_array[1];
                $this_word_pair = array(
                    "word"=>$char,
                    "tag"=>$this_pos
                );
                array_push($words, $this_word_pair);
                $next = $i+1;
            }
        }

        if ($next<$len) {
            eval('$this_pos_array = array'.$pos_list[$next].';');
            $this_pos = $this_pos_array[1];
            $this_word_pair = array(
                "word"=>mb_substr($sentence, $next, null, 'UTF-8'),
                "tag"=>$this_pos
            );
            array_push($words, $this_word_pair);
        }

        return $words;
    }// end function __cut

    /**
     * Static method __cutDetail
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cutDetail($sentence, $options = array())
    {
        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $re_han_pattern = '([\x{4E00}-\x{9FA5}]+)';
        $re_skip_pattern = '([a-zA-Z0-9+#&=\._\r\n]+)';
        $re_punctuation_pattern = '([\x{ff5e}\x{ff01}\x{ff08}\x{ff09}\x{300e}'.
                                    '\x{300c}\x{300d}\x{300f}\x{3001}\x{ff1a}\x{ff1b}'.
                                    '\x{ff0c}\x{ff1f}\x{3002}]+)';
        $re_eng_pattern = '[a-zA-Z0-9]+';
        $re_num_pattern = '[\.0-9]+';

        preg_match_all(
            '/('.$re_han_pattern.'|'.$re_skip_pattern.'|'.$re_punctuation_pattern.')/u',
            $sentence,
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        foreach ($blocks as $blk) {
            if (preg_match('/'.$re_han_pattern.'/u', $blk)) {
                $blk_words = self::__cut($blk);
                foreach ($blk_words as $blk_word) {
                    array_push($words, $blk_word);
                }
            } elseif (preg_match('/'.$re_skip_pattern.'/u', $blk)) {
                if (preg_match('/'.$re_num_pattern.'/u', $blk)) {
                    array_push($words, array("word"=>$blk, "tag"=>"m"));
                } elseif (preg_match('/'.$re_eng_pattern.'/u', $blk)) {
                    array_push($words, array("word"=>$blk, "tag"=>"eng"));
                }
            } elseif (preg_match('/'.$re_punctuation_pattern.'/u', $blk)) {
                array_push($words, array("word"=>$blk, "tag"=>"w"));
            }
        }

        return $words;
    }// end function __cutDetail

    /**
     * Static method __cutDAG
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cutDAG($sentence, $options = array())
    {
        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $N = mb_strlen($sentence, 'UTF-8');
        $DAG = Jieba::getDAG($sentence);

        Jieba::calc($sentence, $DAG);

        $x = 0;
        $buf = '';

        while ($x < $N) {
            $current_route_keys = array_keys(Jieba::$route[$x]);
            $y = $current_route_keys[0]+1;
            $l_word = mb_substr($sentence, $x, ($y-$x), 'UTF-8');

            if (($y-$x)==1) {
                $buf = $buf.$l_word;
            } else {
                if (mb_strlen($buf, 'UTF-8')>0) {
                    if (mb_strlen($buf, 'UTF-8')==1) {
                        if (isset(self::$word_tag[$buf])) {
                            $buf_tag = self::$word_tag[$buf];
                        } else {
                            $buf_tag = "x";
                        }
                        array_push(
                            $words,
                            array("word"=>$buf, "tag"=>$buf_tag)
                        );
                        $buf = '';
                    } else {
                        if (! isset(Jieba::$FREQ[$buf])) {
                            $regognized = self::__cutDetail($buf);
                            foreach ($regognized as $key => $word) {
                                array_push($words, $word);
                            }
                        } else {
                            $elem_array = preg_split('//u', $buf, -1, PREG_SPLIT_NO_EMPTY);
                            foreach ($elem_array as $word) {
                                if (isset(self::$word_tag[$word])) {
                                    $buf_tag = self::$word_tag[$word];
                                } else {
                                    $buf_tag = "x";
                                }
                                array_push(
                                    $words,
                                    array("word"=>$word, "tag"=>$buf_tag)
                                );
                            }
                        }
                        $buf = '';
                    }
                }

                if (isset(self::$word_tag[$l_word])) {
                    $buf_tag = self::$word_tag[$l_word];
                } else {
                    $buf_tag = "x";
                }
                array_push(
                    $words,
                    array("word"=>$l_word, "tag"=>$buf_tag)
                );
            }
            $x = $y;
        }

        if (mb_strlen($buf, 'UTF-8')>0) {
            if (mb_strlen($buf, 'UTF-8')==1) {
                if (isset(self::$word_tag[$buf])) {
                    $buf_tag = self::$word_tag[$buf];
                } else {
                    $buf_tag = "x";
                }
                array_push(
                    $words,
                    array("word"=>$buf, "tag"=>$buf_tag)
                );
            } else {
                if (! isset(Jieba::$FREQ[$buf])) {
                    $regognized = self::__cutDetail($buf);
                    foreach ($regognized as $key => $word) {
                        array_push($words, $word);
                    }
                } else {
                    $elem_array = preg_split('//u', $buf, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($elem_array as $word) {
                        if (isset(self::$word_tag[$word])) {
                            $buf_tag = self::$word_tag[$word];
                        } else {
                            $buf_tag = "x";
                        }
                        array_push(
                            $words,
                            array("word"=>$word, "tag"=>$buf_tag)
                        );
                    }
                }
            }
        }

        return $words;
    }// end function __cutDAG

    /**
     * Static method __cutDAGNoHMM
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cutDAGNoHMM($sentence, $options = array())
    {
        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $N = mb_strlen($sentence, 'UTF-8');
        $DAG = Jieba::getDAG($sentence);

        Jieba::calc($sentence, $DAG);

        $x = 0;
        $buf = '';

        $re_eng_pattern = '[a-zA-Z+#]+';

        while ($x < $N) {
            $current_route_keys = array_keys(Jieba::$route[$x]);
            $y = $current_route_keys[0]+1;
            $l_word = mb_substr($sentence, $x, ($y-$x), 'UTF-8');

            if (preg_match('/'.$re_eng_pattern.'/u', $l_word)) {
                $buf = $buf.$l_word;
                $x = $y;
            } else {
                if (mb_strlen($buf, 'UTF-8')>0) {
                    if (isset(self::$word_tag[$buf])) {
                        $buf_tag = self::$word_tag[$buf];
                    } else {
                        $buf_tag = "x";
                    }
                    array_push(
                        $words,
                        array("word"=>$buf, "tag"=>$buf_tag)
                    );
                    $buf = '';
                }
                if (isset(self::$word_tag[$l_word])) {
                    $buf_tag = self::$word_tag[$l_word];
                } else {
                    $buf_tag = "x";
                }
                array_push(
                    $words,
                    array("word"=>$l_word, "tag"=>$buf_tag)
                );
                $x = $y;
            }
        }

        if (mb_strlen($buf, 'UTF-8')>0) {
            if (isset(self::$word_tag[$buf])) {
                $buf_tag = self::$word_tag[$buf];
            } else {
                $buf_tag = "x";
            }
            array_push(
                $words,
                array("word"=>$buf, "tag"=>$buf_tag)
            );
        }

        return $words;
    }// end function __cutDAGNoHMM

    /**
     * Static method cut
     *
     * @param string  $sentence # input sentence
     * @param array   $options  # other options
     *
     * @return array $seg_list
     */
    public static function cut($sentence, $options = array("HMM" => true))
    {
        $defaults = array(
            'mode'=>'default'
        );

        @$options = array_merge($defaults, $options);

        $seg_list = array();

        $re_han_pattern = '([\x{4E00}-\x{9FA5}]+)';
        $re_skip_pattern = '([a-zA-Z0-9+#\r\n]+)';
        $re_punctuation_pattern = '([\x{ff5e}\x{ff01}\x{ff08}\x{ff09}\x{300e}'.
                                    '\x{300c}\x{300d}\x{300f}\x{3001}\x{ff1a}\x{ff1b}'.
                                    '\x{ff0c}\x{ff1f}\x{3002}]+)';
        $re_eng_pattern = '[a-zA-Z+#]+';
        $re_num_pattern = '[0-9]+';

        preg_match_all(
            '/('.$re_han_pattern.'|'.$re_skip_pattern.'|'.$re_punctuation_pattern.')/u',
            $sentence,
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        foreach ($blocks as $blk) {
            if (preg_match('/'.$re_han_pattern.'/u', $blk)) {
                if ($options['HMM']) {
                    $words = Posseg::__cutDAG($blk);
                } else {
                    $words = Posseg::__cutDAGNoHMM($blk);
                }

                foreach ($words as $word) {
                    array_push($seg_list, $word);
                }
            } elseif (preg_match('/'.$re_skip_pattern.'/u', $blk)) {
                if (preg_match('/'.$re_num_pattern.'/u', $blk)) {
                    array_push($seg_list, array("word"=>$blk, "tag"=>"m"));
                } elseif (preg_match('/'.$re_eng_pattern.'/u', $blk)) {
                    array_push($seg_list, array("word"=>$blk, "tag"=>"eng"));
                }
            } elseif (preg_match('/'.$re_punctuation_pattern.'/u', $blk)) {
                array_push($seg_list, array("word"=>$blk, "tag"=>"w"));
            }
        }

        return $seg_list;
    }// end function cut

    /**
     * Static method posTagReadable
     *
     * @param array $seg_list # input seg_list
     * @param array $options  # other options
     *
     * @return array $new_seg_list
     */
    public static function posTagReadable($seg_list, $options = array())
    {
        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $new_seg_list = array();

        foreach ($seg_list as $seg) {
            $seg['tag_readable'] = self::$pos_tag_readable[$seg['tag']];
            array_push($new_seg_list, $seg);
        }

        return $new_seg_list;
    }// end function posTagReadable
}
