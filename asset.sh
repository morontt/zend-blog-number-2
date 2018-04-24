#!/bin/bash

currentdate=`date '+%Y%m%d'`

touch ./www/css/temp.css

cat ./www/css/all.css > ./www/css/temp.css
echo "" >> ./www/css/temp.css
cat ./www/css/main.css >> ./www/css/temp.css

java -jar ./bin/yuicompressor.jar ./www/css/temp.css -o ./www/css/main_$currentdate.min.css --charset utf-8

rm ./www/css/temp.css

sed -i 's/main_\([0-9]\{8\}\)\.min\.css/main_'$currentdate'\.min\.css/' ./application/layouts/scripts/layout.phtml

touch ./www/js/temp.js

cat ./www/js/syntaxhighlighter/shMain_20130218.js > ./www/js/temp.js
echo "" >> ./www/js/temp.js
cat ./www/js/jquery-1.8.3.js >> ./www/js/temp.js
echo "" >> ./www/js/temp.js
cat ./www/js/footer.js >> ./www/js/temp.js

uglifyjs ./www/js/temp.js -o ./www/js/main_$currentdate.min.js

rm ./www/js/temp.js
