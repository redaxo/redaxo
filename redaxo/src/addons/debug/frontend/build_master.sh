#!/bin/sh
rm -rf clockwork-app-master
rm -rf web
curl -Ls -o clockwork.zip https://github.com/underground-works/clockwork-app/archive/master.zip
unzip clockwork.zip
rm clockwork.zip
cd clockwork-app-master
npm ci


# replace default backend url with REDAXO api function
# part we need to replace can be found here https://github.com/underground-works/clockwork-app/blob/002c06260bda1c0e04ffd12f02a5076b1026ca8a/src/platform/standalone.js#L36
search="window.location.href.split('/').slice(0, -1).join('/')).path() + '/'"
replace="window.location.href.split('/').slice(0,-1).join('/')).path()+'/index.php?page=structure\&rex-api-call=debug\&request='"

sed -i "s*$search*$replace*g" src/platform/standalone.js


npm run build-web
mv dist/web ../
cd ..
rm -rf clockwork-app-master