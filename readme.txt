Add following lines to your .htaccess file if storage is set to "database" and you are using apache as your web server.

# Get merged js and css files from database using get.php if they do not exist in filesystem
RewriteCond %{REQUEST_URI} ^/media/css/.*\.css$ [OR]
RewriteCond %{REQUEST_URI} ^/media/js/.*\.js$
# never rewrite for existing files
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* ../get.php [L]

# If you enable the Add timestamps to asset files feature, also add these lines to your .htaccess file
RewriteCond %{REQUEST_URI} ^/skin/
RewriteRule (.*)\.(\d{10})\.(gif|png|jpg)$ $1.$3 [L,NC]

If you use NGINX, add the following lines to your nginx config within the server block for your site 
if you use database as the file storage location:
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


# If you enable the Add timestamps to asset files feature, also add these lines to your nginx config file
# they should NOT be added to any particular location block.
rewrite "^/skin/(.*)\.(\d{10})\.(gif|png|jpg)$" /skin/$1.$3 last;

