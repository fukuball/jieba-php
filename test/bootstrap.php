<?php
ini_set('memory_limit', '1024M');

require_once dirname(dirname(__FILE__))."/src/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/src/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/src/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/src/class/Finalseg.php";
require_once dirname(dirname(__FILE__))."/src/class/JiebaAnalyse.php";
require_once dirname(dirname(__FILE__))."/src/class/Posseg.php";
require_once dirname(dirname(__FILE__))."/src/class/JiebaMemory.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;
use Fukuball\Jieba\Posseg;
use Fukuball\Jieba\JiebaMemory;

// Only initialize for non-memory management tests
if (!isset($_ENV['SKIP_AUTO_INIT']) && !str_contains($_SERVER['argv'][1] ?? '', 'MemoryManagementTest')) {
    Jieba::init();
    Finalseg::init();
    JiebaAnalyse::init();
    Posseg::init();
}

function loader($class) {
    $file = $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('loader');
?>