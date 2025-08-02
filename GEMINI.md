# GEMINI.md: jieba-php

## Project Overview

This project, `fukuball/jieba-php`, is a PHP library for Chinese text segmentation. It is a direct port of the popular Python "Jieba" library. Its primary purpose is to provide robust tools for processing Chinese language text within a PHP environment.

**Core Features:**

*   **Word Segmentation (Tokenization):** The main feature is cutting Chinese sentences into a list of words. It supports multiple modes, including default mode, search engine mode, and full mode.
*   **Part-of-Speech (POS) Tagging:** The library can identify and tag the part of speech for each segmented word (e.g., noun, verb).
*   **Keyword Extraction:** It implements the TF-IDF (Term Frequency-Inverse Document Frequency) algorithm to extract the most significant keywords from a piece of text.
*   **Customization:** Users can add their own words to a user dictionary to improve segmentation accuracy for specific domains. It also supports custom stop words.

**Architecture:**

The library is architected around a set of static classes:

*   `Jieba`: The core class for word segmentation. It uses a Trie data structure built from dictionary files (`dict.txt`) to perform efficient lookups. It caches the Trie and frequency data in `.json` and `.cache` files respectively to speed up initialization on subsequent runs.
*   `JiebaAnalyse`: Handles keyword extraction using TF-IDF. It relies on an IDF dictionary (`idf.txt`).
*   `Posseg`: Manages Part-of-Speech tagging.
*   `Finalseg`: Helps resolve ambiguities for words not found in the dictionary.

The project relies on dictionary and model files located in `src/dict/` and `src/model/`.

## Building and Running

This is a PHP library managed with Composer.

**1. Install Dependencies:**

To install all required development and production dependencies, run:

```bash
composer install
```

**2. Running Tests:**

The project uses PHPUnit for testing. The test suite can be run with the following command, as configured in `phpunit.xml` and the GitHub Actions workflow:

```bash
vendor/bin/phpunit --configuration phpunit.xml
```

**3. Code Style Check:**

The project adheres to the PSR-2 coding standard. To check for compliance, run `phpcs`:

```bash
vendor/bin/phpcs --standard=PSR2 --extensions=php --ignore="*/test/*" ./src/class
```

## Development Conventions

*   **Dependency Management:** All PHP dependencies are managed through `composer.json`.
*   **Coding Standard:** The codebase follows the `PSR-2` standard.
*   **Testing:** Unit tests are located in the `/test` directory. All new functionality should be accompanied by corresponding tests.
*   **Continuous Integration:** GitHub Actions (`.github/workflows/php-tests.yml`) is configured to automatically run tests and code style checks on pushes and pull requests to the `master` branch across multiple PHP versions.
*   **Autoloading:** The project uses PSR-4 autoloading for its classes, as defined in `composer.json`.
