# Magento  Tool: Balancer languages

Basic useful feature list:

 * Search the difference between the basic and custom translation file.
 * Adding custom fields to the base translation.


I'm no good at writing sample / filler text, so go write something yourself.

## How to use

 1. Copy base \*.csv translation files from language pack to **./language_base/** directory without subdirectory.
 2. Copy your custom \*.csv translation files to ** ./language_src/** directory.  
 3. Go to console and execute this command:

```bash
php -f run.php
```
 4. After you need copy \*.csv files from **./language_result/** directory to **./app/local/%YourLanguage%/** 
