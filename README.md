
# Please use https://github.com/AOEpeople/Aoe_JsCssTstamp instead!

# AOE_JsCssTimestamp

## Overview

This module adds the last-modified timestamp to your JS and CSS merged files to enabled browser-based caching and speed up your server.


## Installation

Add following lines to your .htaccess file if storage is set to "database" and you are using apache as your web server.

Get merged js and css files from database using get.php if they do not exist in filesystem

```
RewriteCond %{REQUEST_URI} ^/media/css/.*\.css$ [OR]
RewriteCond %{REQUEST_URI} ^/media/js/.*\.js$
```

never rewrite for existing files

```
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* ../get.php [L]
```

### Add versions to files

If you enable one of the following in the configuration make sure to add this to your .htaccess file:

- Add version to assets (gif|png|jpg)
- Add version to assets (css)
- Add version to assets (js)

```
RewriteRule (.*)\.(\d{10})\.(gif|png|jpg)$ $1.$3 [L,NC]
RewriteRule (.*)\.(\d{10})\.(css)$ $1.$3 [L,NC]
RewriteRule (.*)\.(\d{10})\.(js)$ $1.$3 [L,NC]
```

If you use NGINX, add the following lines to your nginx config within the server block for your site
if you use database as the file storage location:

```
location ^~ /media/js/ {
    try_files $uri $uri/ @handlerjs;
}

location ^~ /media/css/ {
    try_files $uri $uri/ @handlercss;
}

location @handlerjs {
    rewrite /media/js/ /get.php;
}

location @handlercss {
    rewrite /media/css/ /get.php;
}
```

### If you enable the Add timestamps to asset files feature, also add these lines to your nginx config file
they should NOT be added to any particular location block.

```
rewrite "^/(.*)\.(\d{10})\.(gif|png|jpg)$" /$1.$3 last;
```

## Release notes

#### v 0.7.0

- Allowing to add timestamps to js and css files too now. Feature "inspired" by [Tymek's](https://github.com/tmotyl) [commit](https://github.com/macopedia/Aoe_JsCssTstamp/commit/5471779099fea1c259c49e89ae8308de4a8138e9).

#### v 0.6.0

- CDN support is removed. Use https://github.com/AOEpeople/Aoe_MergedJsCssCdn if you need CDN support.


