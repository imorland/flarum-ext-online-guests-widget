# Online Guests Widget

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/ianm/online-guests.svg)](https://packagist.org/packages/ianm/online-guests) [![Total Downloads](https://img.shields.io/packagist/dt/ianm/online-guests.svg)](https://packagist.org/packages/ianm/online-guests)

A [Flarum](http://flarum.org) extension. Guests Online widget

## Screenshot

![image](https://github.com/imorland/flarum-ext-online-guests-widget/assets/16573496/523a172e-ed62-49c9-89a6-bdb3dafdbcde)

## Installation

Currently, guests are only calculated when using the Flarum default session driver (`files`). I might consider adding support for `database`, `redis`, etc in future. Let me know if you're interested in sponsoring any of these additions.

This will also install [Forum Widgets Core](https://github.com/afrux/forum-widgets-core) as it relies on it.

Install with composer:

```sh
composer require ianm/online-guests:"*"
```

## Updating

```sh
composer update ianm/online-guests:"*"
php flarum migrate
php flarum cache:clear
```

## Links

- [Packagist](https://packagist.org/packages/ianm/online-guests)
- [GitHub](https://github.com/ianm/online-guests)
- [Discuss](https://discuss.flarum.org/d/PUT_DISCUSS_SLUG_HERE)
