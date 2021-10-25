#### Install theme
Requirements: have npm

Install Node modules with NPM:

``npm install``

To build once

``npm run build``

Serves with browsersync.

``npm run serve``

Run without browsersync:

``npm run watch``


####To disable drupal caching:

Add the following to drupal/sites/default/services.yml
```yaml
services:
  cache.backend.null:
    class: Drupal\Core\Cache\NullBackendFactory
```

Add the following to drupal/sites/default/settings.php:

Should be enough for twig templating.
```php
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$settings['cache']['bins']['render'] = 'cache.backend.null';
```
Disables the page cache: has more impact, but could also be useful.
```php
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';
