#!/bin/sh

echo "Update redaxo/src/core/"
composer update -d redaxo/src/core/

echo "\nUpdate redaxo/src/addons/textile/"
composer update -d redaxo/src/addons/textile/

echo "\nUpdate redaxo/src/core/assets/jquery.min.js"
curl -# http://code.jquery.com/jquery-latest.min.js > redaxo/src/core/assets/jquery.min.js

echo "\nUpdate redaxo/src/core/assets/jquery-pjax.min.js"
curl -#d output_info=compiled_code -d compilation_level=SIMPLE_OPTIMIZATIONS -d code_url=https://github.com/defunkt/jquery-pjax/raw/master/jquery.pjax.js http://closure-compiler.appspot.com/compile > redaxo/src/core/assets/jquery-pjax.min.js

echo "redaxo/src/core/assets/jquery.min.js\nredaxo/src/core/assets/jquery-pjax.min.js" | php coding_standards.phar fix

cp redaxo/src/core/assets/jquery-pjax.min.js assets
cp redaxo/src/core/assets/jquery.min.js assets
