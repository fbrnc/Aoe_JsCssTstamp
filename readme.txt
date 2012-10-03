Add following lines to your .htaccess file if storage is set to "database"

# Get merged js and css files from database using get.php if they do not exist in filesystem
RewriteCond %{REQUEST_URI} ^/media/css/.*\.css$ [OR]
RewriteCond %{REQUEST_URI} ^/media/js/.*\.js$
# never rewrite for existing files
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule .* ../get.php [L]

# Adding timestamps to files
RewriteCond %{REQUEST_URI} ^/skin/
RewriteRule (.*)\.(\d{10})\.(gif|png|jpg)$ $1.$3 [L,NC]
