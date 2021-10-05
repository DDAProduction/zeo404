# Zeo404

## Installation
1) Add `"ddaproduction/zeo404": "*"` to u `core/custom/composer.json` files

2) Run `composer update` in `core/` folder
3) Run `php artisan migrate`
4) Run `php artisan vendor:publish` And select **Provider: DDAProduction\Zeo404\Zeo404ServiceProvider**
5) Edit file `custom/config/domain.php` change variables: 
   1) **current_site** - your domain
   2) **sitemap_url** - link to u sitemap.xml
   3) **ignored_blanks** - array with links what will ignore
6) Run `php artisan self:parse`
7) Wait...
8) Go to manager and check **modules->zeo404**

## Cronjob
Just add `php artisan self:parse` to cronjob 