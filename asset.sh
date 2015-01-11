#!/bin/bash

currentdate=`date '+%Y%m%d'`

touch ./www/css/temp.css

cat ./www/css/all.css > ./www/css/temp.css
echo "" >> ./www/css/temp.css
cat ./www/css/main.css >> ./www/css/temp.css

java -jar ./bin/yuicompressor.jar ./www/css/temp.css -o ./www/css/main_$currentdate.min.css --charset utf-8

rm ./www/css/temp.css
