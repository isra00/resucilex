Resucilex 2.0 by Israel Viana
=============================

Tech used:
 - PHP 8.2
 - Laravel 12
 - Stemming/POS tagging has been performed with TreeTagger 3.2.1, hunspell -s (myspell-es 1.11-14 for Spanish and hunspell-en-gb 5.1.0-1ubuntu2 for English) and manually checked.

The app is developed and deployed on Apache with mod_php, but you may find files and configs related to frankenphp and Docker/Docker Compose, as I'm testing them, but you can ignore them.

INSTALLATION
============

1. Set up a MySQL database and load resucilex.sql into it.
2. Place the resucilex folder in your web server, and set its document root to the public directory. Make sure the web server's user has read permissions on the whole directory, and write permissions on `/storage` and `/public/page-cache`.
3. Run `composer install` in the root folder.
5. Edit the `.htaccess` file in the public directory to match your server settings.
6. Make an .env file in the root folder following the example in `.env.example`.

CACHE
=====

To offload the web server and make pages load blazing fast, Resucilex implements a disk-based page cache:
 - When a page is requested, the Laravel app generates the page and stores its whole HTML in a file in the `page-cache` directory.
 - When the same page is requested again, Apache serves it directly by rewriting its URL to the cached file, so that no PHP is run at all.

To clear the cache, run

```
php artisan page-cache:clear
```

You may optionally pass a URL slug to the command, to only delete the cache for a specific page:

```
php artisan page-cache:clear {slug}
```

To clear everything under a given path, use the --recursive flag:

```
php artisan page-cache:clear {slug} --recursive
```

...or simply delete the contents of the `public/page-cache` directory.