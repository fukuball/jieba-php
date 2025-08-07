# Copilot Instructions for jieba-php

## Project Overview

- **jieba-php** is a PHP port of the Jieba Chinese text segmentation library, supporting simplified/traditional Chinese, Japanese, and Korean (CJK) text.
- The codebase is organized under `src/class/` for core classes, `src/dict/` for dictionaries, and `src/cmd/` for CLI/demo scripts.
- Major components: `Jieba` (main segmenter), `Finalseg` (HMM-based segmenter), `JiebaAnalyse` (TF-IDF keyword extraction), `Posseg` (POS tagging), and `JiebaMemory` (memory/cache management).

## Key Patterns & Conventions

- **Initialization**: Always call `Jieba::init()` and `Finalseg::init()` before segmentation. For keyword extraction or POS tagging, also call `JiebaAnalyse::init()` or `Posseg::init()`.
- **Segmentation**: Use `Jieba::cut($text, $cut_all = false, $options = [])` for main segmentation. Use `cutForSearch` for search engine mode, and `tokenize` for word positions.
- **Custom Dictionaries**: Load with `Jieba::loadUserDict($path)`. Format matches `dict.txt` (word [freq] [tag]).
- **Memory Management**: Use `JiebaMemory` for unified cache/memory control. Call `JiebaMemory::clearAllCaches()` or `destroyAll()` for large/batch jobs.
- **CJK Support**: Enable with `Jieba::init(['cjk'=>'all'])` for mixed-language text.

## Developer Workflows

- **Testing**: Run all tests with `vendor/bin/phpunit` or `phpunit` (see `test/`).
- **Compatibility**: `test/test_php84_compatibility.php` checks PHP 8.4+ compatibility and basic features.
- **Demos**: Run CLI demos in `src/cmd/` (e.g., `php src/cmd/demo_mixed_cjk.php`).
- **Composer**: Use Composer for dependency management (`composer install`).

## Project-Specific Details

- **Dictionary Switching**: Use `Jieba::init(['dict'=>'big'])` for traditional Chinese.
- **POS Tagging**: Use `Posseg::cut($text)` for word/tag arrays. For TF-IDF scores, pass `['with_scores'=>true]`.
- **Cache Monitoring**: Use `Jieba::getCacheStats()` or `JiebaMemory::getMemoryStats()` for memory/cache info.
- **File Structure**:
  - `src/class/`: Core PHP classes
  - `src/dict/`: Dictionaries (small, big, user, idf, stopwords)
  - `test/`: PHPUnit tests and compatibility scripts
  - `src/cmd/`: Example/demo scripts

## Integration Points

- **External dependencies**: Relies on `multi-array` (see `src/vendor/`), loaded via Composer.
- **No database or network integration** by default; all data is file-based.

## Examples

- See `README.md` for code samples and output formats.
- For custom workflows, reference `test/test_php84_compatibility.php` and demo scripts in `src/cmd/`.

---

For questions about project-specific conventions, see `README.md` or open an issue.
