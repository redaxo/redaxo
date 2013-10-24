#!/bin/sh

echo "Update redaxo/src/core/vendor"
composer update --no-dev -d redaxo/src/core/
composer dump-autoload --optimize -d redaxo/src/core
php -r "foreach (array('redaxo/src/core/vendor/autoload.php', 'redaxo/src/core/vendor/composer/autoload_real.php') as \$file) {\
    file_put_contents(\$file, preg_replace('/(?<=ComposerAutoloaderInit)[0-9a-f]{32}/', 'RedaxoCore', file_get_contents(\$file)));\
}"

echo "\nUpdate redaxo/src/addons/textile/vendor"
composer update --no-dev -d redaxo/src/addons/textile/

echo "\nUpdate redaxo/src/core/assets/jquery.min.js"
curl -# http://code.jquery.com/jquery-latest.min.js > redaxo/src/core/assets/jquery.min.js

echo "\nUpdate redaxo/src/core/assets/jquery-pjax.min.js"
curl -#d output_info=compiled_code -d compilation_level=SIMPLE_OPTIMIZATIONS -d code_url=https://github.com/defunkt/jquery-pjax/raw/master/jquery.pjax.js http://closure-compiler.appspot.com/compile > redaxo/src/core/assets/jquery-pjax.min.js

echo "redaxo/src/core/assets/jquery.min.js\nredaxo/src/core/assets/jquery-pjax.min.js" | php coding_standards.phar fix

cp redaxo/src/core/assets/jquery-pjax.min.js assets
cp redaxo/src/core/assets/jquery.min.js assets
