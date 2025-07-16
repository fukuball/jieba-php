# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Commands

### Testing
- Run all tests: `./vendor/bin/phpunit`
- Run tests with coverage: `./vendor/bin/phpunit --coverage-clover=build/logs/clover.xml`
- Run specific test: `./vendor/bin/phpunit test/JiebaTest.php`

### Code Quality
- Code style check: `./vendor/bin/phpcs`
- Code linting: `./vendor/bin/phpcs --standard=PSR2 src/`

### Demo Scripts
- Basic segmentation: `php src/cmd/demo.php`
- Keyword extraction: `php src/cmd/demo_extract_tags.php`
- Part-of-speech tagging: `php src/cmd/demo_posseg.php`
- Custom dictionary: `php src/cmd/demo_user_dict.php`
- Tokenization with positions: `php src/cmd/demo_tokenize.php`

### Memory Requirements
All operations require significant memory allocation: `ini_set('memory_limit', '1024M');`

## Architecture Overview

This is a PHP port of the Python jieba Chinese text segmentation library. The core architecture consists of:

### Core Classes (src/class/)
- **Jieba**: Main segmentation engine with three modes (accurate, full, search)
- **Finalseg**: HMM-based final segmentation for unknown words using Viterbi algorithm
- **JiebaAnalyse**: TF-IDF keyword extraction functionality
- **Posseg**: Part-of-speech tagging with HMM model
- **JiebaCache**: Caching mechanism for performance optimization

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