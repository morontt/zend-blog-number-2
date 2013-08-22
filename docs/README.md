README
======

This directory should be used to place project specfic documentation including
but not limited to project notes, generated API/phpdoc documentation, or
manual files generated or hand written.  Ideally, this directory would remain
in your development environment only and should not be deployed with your
application to it's final production location.


Setting Up Your VHOST
=====================

The following is a sample VHOST you might want to consider for your project.

<VirtualHost *:80>
    DocumentRoot "/path/to/project-dir/httpdocs/www"
    ServerName zendblog-2.loc

    SetEnv APPLICATION_ENV "development"
    ErrorLog "/path/to/project-dir/logs/error.txt"
    CustomLog "/path/to/project-dir/logs/access.txt" common

    <Directory "/path/to/project-dir/httpdocs/www">
      DirectoryIndex index.php index.html
      Options FollowSymLinks
      Options -Indexes
      AllowOverride All
      Order deny,allow
      Allow from all
    </Directory>

</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "/path/to/project-dir/httpdocs/img"
    ServerName img.zendblog-2.loc

    SetEnv APPLICATION_ENV "development"
    ErrorLog "/path/to/project-dir/logs/error.txt"
    CustomLog "/path/to/project-dir/logs/access.txt" common

    <Directory "/path/to/project-dir/httpdocs/img">
      DirectoryIndex index.php index.html
      Options FollowSymLinks
      AllowOverride All
      Order deny,allow
      Allow from all
    </Directory>

</VirtualHost>

YUI Compressor
==============

java -jar bin/yuicompressor.jar www/css/main.css -o www/css/main.min.css --charset utf-8
java -jar bin/yuicompressor.jar www/css/all.css -o www/css/all.min.css --charset utf-8

Cronjobs
========

php cronjobs/grabberAvatar.php  --env development
php cronjobs/spoolsend.php
php cronjobs/trackingArchive.php  --env development