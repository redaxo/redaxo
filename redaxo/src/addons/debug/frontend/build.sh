#!/bin/sh
rm -rf clockwork-app-redaxo-v5.0
rm -rf web
curl -Ls -o clockwork.zip https://github.com/redaxo/clockwork-app/archive/redaxo-v5.0.zip
unzip clockwork.zip
rm clockwork.zip
cd clockwork-app-redaxo-v5.0
npm ci

npm run build-web

# rewrite image path
search="src:\"img/"
replace="src:\"../assets/addons/debug/clockwork/img/"
sed -i "s*$search*$replace*g" dist/web/js/app.*.js

#cleanup
rm dist/web/manifest.json
rm -rf dist/web/img/whats-new
rm dist/web/precache*.js
rm dist/web/service-worker.js

mv dist/web ../
cd ..
rm -rf clockwork-app-redaxo-v5.0

# zip frontend
cd web
zip -r ../frontend.zip *
cd ..
rm -rf web
