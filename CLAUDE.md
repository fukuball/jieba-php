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

### Memory Requirements
All operations require significant memory allocation: `ini_set('memory_limit', '1024M');`

## Architecture Overview

This is a PHP port of the Python jieba Chinese text segmentation library. The core architecture consists of:

### Core Classes (src/class/)
- **Jieba**: Main segmentation engine with three modes (accurate, full, search)
  - Supports custom word addition with `addWord($word, $freq, $tag)`
  - Enhanced input validation and security measures
  - Memory management improvements
- **Finalseg**: HMM-based final segmentation for unknown words using Viterbi algorithm
- **JiebaAnalyse**: TF-IDF keyword extraction functionality
- **Posseg**: Part-of-speech tagging with HMM model
  - **Custom POS tag support**: Add custom tags with `addWordTag($word, $tag)`
  - **Input validation**: Secure tag validation with length limits and character restrictions
  - **Memory cleanup**: `removeWordTag($word)` for tag cleanup

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
- Custom dictionaries can extend language support

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

### Test Coverage
- 58+ tests with 259+ assertions
- PSR2 coding standard compliance
- Edge case coverage for mixed character types
- Security vulnerability testing

## Best Practices & Guidelines

### Custom Word Addition
```php
// Always initialize first
Jieba::init();
Finalseg::init();
Posseg::init();

// Add words with proper error handling
try {
    Jieba::addWord('自定義詞', 100, 'custom_tag');
} catch (InvalidArgumentException $e) {
    error_log("Invalid tag: " . $e->getMessage());
}
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
- Cache initialization results when possible

### Error Handling Patterns
- **Dictionary Loading**: Log warnings for invalid entries, continue processing
- **Direct API Calls**: Throw exceptions for immediate feedback
- **Batch Operations**: Use try-catch blocks for individual items
- **Production**: Always handle `InvalidArgumentException` for tag operations