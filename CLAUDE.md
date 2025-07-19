# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Commands

### Testing
- Run all tests: `./vendor/bin/phpunit`
- Run tests with coverage: `./vendor/bin/phpunit --coverage-clover=build/logs/clover.xml`
- Run specific test: `./vendor/bin/phpunit test/JiebaTest.php`
- Run custom POS tag tests: `./vendor/bin/phpunit test/CustomPosTagTest.php`
- Run security tests: `./vendor/bin/phpunit test/SecurityTest.php`
- Run user dictionary tests: `./vendor/bin/phpunit test/UserDictTest.php`
- Run memory management tests: `./vendor/bin/phpunit test/MemoryManagementTest.php`

### Code Quality
- Code style check: `./vendor/bin/phpcs`
- PSR2 standard check: `./vendor/bin/phpcs --standard=PSR2 --extensions=php --ignore="*/test/*" ./src/class`
- Code linting: `./vendor/bin/phpcs --standard=PSR2 src/`

### Demo Scripts
- Basic segmentation: `php src/cmd/demo.php`
- Keyword extraction: `php src/cmd/demo_extract_tags.php`
- Part-of-speech tagging: `php src/cmd/demo_posseg.php`
- Custom dictionary: `php src/cmd/demo_user_dict.php`
- Tokenization with positions: `php src/cmd/demo_tokenize.php`
- **Custom POS tagging**: `php src/cmd/demo_custom_pos_tag.php`
- **TF-IDF and POS integration**: `php src/cmd/demo_tf_idf_pos.php`
- **Mixed CJK language processing**: `php src/cmd/demo_mixed_cjk.php`

### Memory Requirements
All operations require significant memory allocation: `ini_set('memory_limit', '1024M');`

## Architecture Overview

This is a PHP port of the Python jieba Chinese text segmentation library. The core architecture consists of:

### Core Classes (src/class/)
- **Jieba**: Main segmentation engine with three modes (accurate, full, search)
  - Supports custom word addition with `addWord($word, $freq, $tag)`
  - Enhanced input validation and security measures
  - Memory management improvements
  - **NEW**: Support for `with_pos` and `with_scores` options in `cut()` method
- **Finalseg**: HMM-based final segmentation for unknown words using Viterbi algorithm
- **JiebaAnalyse**: TF-IDF keyword extraction functionality
  - **NEW**: Modular TF calculation with `calculateTF($words)`
  - **NEW**: Flexible TF-IDF calculation with `calculateTFIDF($tf_values, $detailed)`
- **Posseg**: Part-of-speech tagging with HMM model
  - **Custom POS tag support**: Add custom tags with `addWordTag($word, $tag)`
  - **Input validation**: Secure tag validation with length limits and character restrictions
  - **Memory cleanup**: `removeWordTag($word)` for tag cleanup
  - **NEW**: Support for `with_scores` option in `cut()` method
- **JiebaMemory**: NEW unified memory management utility
  - **Memory management**: `destroyAll()`, `initAll()`, `clearAllCaches()`
  - **Statistics**: `getMemoryStats()`, `getAllCacheStats()`, `getInitializationStatus()`
  - **Convenience**: `isAllInitialized()` for checking all classes

### Dictionary System (src/dict/)
- **dict.txt**: Default dictionary with word frequencies
- **dict.big.txt**: Traditional Chinese dictionary
- **dict.small.txt**: Compact dictionary for memory-constrained environments
- **user_dict.txt**: Custom user dictionary
- **idf.txt/idf.big.txt**: IDF values for keyword extraction
- **stop_words.txt**: Stop words for analysis
- **pos_tag_readable.txt**: Part-of-speech tag descriptions

### Model Files (src/model/)
- **prob_start.json**: HMM start probabilities
- **prob_trans.json**: HMM transition probabilities  
- **prob_emit.json**: HMM emission probabilities
- **pos/**: Part-of-speech specific HMM models

### Key Algorithms
1. **Trie-based DAG construction**: Efficient word graph scanning
2. **Dynamic programming**: Maximum probability path finding
3. **HMM + Viterbi**: Unknown word recognition
4. **TF-IDF**: Keyword extraction

### Initialization Pattern
All classes follow this pattern:
```php
Jieba::init($options);      // Load dictionary and build trie
Finalseg::init();           // Load HMM models
JiebaAnalyse::init();       // Load IDF data
Posseg::init();             // Load POS models

// NEW: Convenient initialization of all classes
JiebaMemory::initAll($options);  // Initialize all classes at once
```

### Dictionary Modes
- `'dict'=>'default'`: Standard dictionary
- `'dict'=>'big'`: Traditional Chinese support
- `'dict'=>'small'`: Memory-efficient mode
- `'dict'=>'test'`: Testing dictionary

### Memory Management
- Dictionary caching with .cache.json files
- Static class variables for model storage
- Large memory footprint requires 1GB+ allocation

### Multi-language Support
- Primary: Simplified/Traditional Chinese
- Secondary: Japanese, Korean (with `'cjk'=>'all'`)
- **ENHANCED**: Improved mixed-language text processing
- **NEW**: Better handling of complex mixed CJK scenarios
- Custom dictionaries can extend language support
- **NEW Demo**: `demo_mixed_cjk.php` for testing multi-language capabilities

## Enhanced TF-IDF and POS Integration Features

### NEW: Integrated TF-IDF Scoring
```php
// Jieba::cut() with POS tags
$pos_result = Jieba::cut($text, false, array('with_pos' => true));

// Jieba::cut() with TF-IDF scores
$scored_result = Jieba::cut($text, false, array('with_scores' => true));

// Jieba::cut() with both POS tags and TF-IDF scores
$full_result = Jieba::cut($text, false, array(
    'with_pos' => true,
    'with_scores' => true
));

// Posseg::cut() with TF-IDF scores
$posseg_scored = Posseg::cut($text, array('with_scores' => true));
```

### NEW: Modular TF-IDF Calculation
```php
// Calculate Term Frequency
$words = array('測試', '中文', '分詞', '測試');
$tf_values = JiebaAnalyse::calculateTF($words);

// Calculate TF-IDF (simple format)
$tfidf_simple = JiebaAnalyse::calculateTFIDF($tf_values, false);

// Calculate TF-IDF (detailed format with TF, IDF, TF-IDF)
$tfidf_detailed = JiebaAnalyse::calculateTFIDF($tf_values, true);
```

## Custom POS Tagging Features

### Adding Custom Tags
```php
// Method 1: Via Jieba::addWord()
Jieba::addWord('自定義詞', 100, 'custom_tag');

// Method 2: Direct tag addition
Posseg::addWordTag('詞彙', 'tag');
```

### Tag Management
```php
// Remove custom tag
Posseg::removeWordTag('詞彙');

// Tag validation (automatic)
// - Length limit: 50 characters
// - Allowed characters: alphanumeric, underscore, hyphen, Chinese characters
// - Security: Prevents injection attacks
```

### Mixed Character Type Support
- Supports words with mixed Chinese/numeric characters (e.g., "詞1", "ABC中文")
- Handles complex segmentation scenarios
- Prioritizes custom tags over HMM predictions

## Security Features

### Input Validation
- **POS Tag Validation**: Comprehensive security checks for all tag inputs
- **Length Limits**: Maximum 50 characters for POS tags
- **Character Restrictions**: Only safe characters allowed (no HTML, JavaScript, etc.)
- **Injection Prevention**: Blocks potential XSS, template injection, and script injection

### Error Handling
- **Consistent Error Handling**: Clear distinction between dictionary loading and direct API calls
- **Graceful Degradation**: Invalid tags in user dictionaries log warnings but don't stop processing
- **Exception Safety**: Proper exception handling with meaningful error messages

### Memory Management
- **Tag Cleanup**: Automatic cleanup when words are overridden
- **Memory Leak Prevention**: Old tags are properly removed
- **Efficient Processing**: Optimized for large dictionary operations

## Testing Framework

### Test Categories
- **Core Functionality**: Basic segmentation and POS tagging tests
- **Custom POS Tags**: Comprehensive testing of custom tagging features
- **Security**: Input validation and injection prevention tests
- **User Dictionaries**: Dictionary loading and processing tests
- **Memory Management**: Memory cleanup and leak prevention tests
- **NEW: TF-IDF Integration**: Enhanced TF-IDF and POS tagging features (`TfIdfPosTest.php`)
- **NEW: Mixed CJK Support**: Multi-language text processing tests (`MixedCJKTest.php`)

### Test Coverage
- 70+ tests with 300+ assertions
- PSR2 coding standard compliance
- Edge case coverage for mixed character types
- Security vulnerability testing
- **NEW**: Comprehensive TF-IDF integration testing
- **NEW**: Multi-language CJK text processing validation
- **NEW**: Backward compatibility verification

## Memory Management with JiebaMemory

### NEW: Unified Memory Management
```php
use Fukuball\Jieba\JiebaMemory;

// Initialize all classes at once
JiebaMemory::initAll($options);

// Check which classes are initialized
$status = JiebaMemory::getInitializationStatus();
if (!JiebaMemory::isAllInitialized()) {
    // Handle partial initialization
}

// Get comprehensive memory statistics
$stats = JiebaMemory::getMemoryStats();
echo "Current Memory: " . $stats['current_memory_usage_formatted'] . "\n";
echo "Peak Memory: " . $stats['peak_memory_usage_formatted'] . "\n";

// Clear all caches while keeping classes initialized
JiebaMemory::clearAllCaches();

// Destroy all classes and free memory
JiebaMemory::destroyAll();
```

### NEW: Cache Statistics Monitoring
```php
// Get detailed cache statistics for all classes
$cacheStats = JiebaMemory::getAllCacheStats();

// Monitor individual class cache usage
echo "Jieba DAG Cache: " . $cacheStats['jieba']['dag_cache_size'] . "\n";
echo "Posseg Word Tags: " . $cacheStats['posseg']['word_tag_size'] . "\n";
echo "JiebaAnalyse IDF: " . $cacheStats['jieba_analyse']['idf_freq_size'] . "\n";
```

## Best Practices & Guidelines

### Custom Word Addition
```php
// Always initialize first
Jieba::init();
Finalseg::init();
Posseg::init();
// OR use convenient initialization
JiebaMemory::initAll();

// Add words with proper error handling
try {
    Jieba::addWord('自定義詞', 100, 'custom_tag');
} catch (InvalidArgumentException $e) {
    error_log("Invalid tag: " . $e->getMessage());
}
```

### NEW: Enhanced Feature Usage
```php
// Use integrated TF-IDF and POS features
$result = Jieba::cut($text, false, array(
    'with_pos' => true,
    'with_scores' => true
));

// Automatic JiebaAnalyse initialization when needed
// No need to manually call JiebaAnalyse::init() for scoring features

// Use modular TF-IDF calculation for custom workflows
$tf_values = JiebaAnalyse::calculateTF($words);
$tfidf_scores = JiebaAnalyse::calculateTFIDF($tf_values, true);
```

### Security Considerations
- Always validate user input before adding custom tags
- Use safe characters only: alphanumeric, underscore, hyphen, Chinese characters
- Avoid dynamic tag generation from untrusted sources
- Monitor memory usage with large custom dictionaries

### Performance Optimization
- Load user dictionaries during initialization, not runtime
- Use appropriate dictionary modes ('small' for memory-constrained environments)
- Clear unused tags with `removeWordTag()` to prevent memory leaks
- **NEW**: Use `JiebaMemory::clearAllCaches()` for comprehensive cache management
- **NEW**: Monitor memory with `JiebaMemory::getMemoryStats()` and `getAllCacheStats()`
- Cache initialization results when possible

### Error Handling Patterns
- **Dictionary Loading**: Log warnings for invalid entries, continue processing
- **Direct API Calls**: Throw exceptions for immediate feedback
- **Batch Operations**: Use try-catch blocks for individual items
- **Production**: Always handle `InvalidArgumentException` for tag operations