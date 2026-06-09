# Online Guests Widget

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/ianm/online-guests.svg)](https://packagist.org/packages/ianm/online-guests) [![Total Downloads](https://img.shields.io/packagist/dt/ianm/online-guests.svg)](https://packagist.org/packages/ianm/online-guests)

A [Flarum](http://flarum.org) extension that adds a forum widget showing how many guests (logged-out visitors) are currently browsing your forum.

## Screenshot

![image](https://github.com/imorland/flarum-ext-online-guests-widget/assets/16573496/523a172e-ed62-49c9-89a6-bdb3dafdbcde)

## How it works

The widget counts active guest sessions — that is, sessions that are **not** associated with a logged-in user. The count is determined by inspecting Flarum's session storage, so the supported methods depend on your configured session driver (see [Supported session drivers](#supported-session-drivers) below).

To avoid recalculating on every page load, the result is cached for a configurable period (default: 10 minutes). The widget is built on top of [Forum Widgets Core](https://github.com/FriendsOfFlarum/forum-widgets-core), which is installed automatically as a dependency and lets admins position, reorder, and toggle widgets from the dashboard.

## Supported session drivers

| Driver  | Supported | Notes |
| ------- | --------- | ----- |
| `file`  | ✅        | The Flarum default. Guest sessions are counted by scanning the session files on disk. |
| `redis` | ✅        | Requires [`fof/redis`](https://github.com/FriendsOfFlarum/redis). Guest session IDs are tracked in a sorted set and counted in real time. |
| other   | ❌        | Any other driver returns a count of `0`. Sponsorship of additional drivers (e.g. `database`) is welcome. |

### Redis sessions

When you use [`fof/redis`](https://github.com/FriendsOfFlarum/redis) with the Redis session handler, this extension automatically registers a middleware that records the session ID of each guest request in a Redis sorted set, scored by request time. The widget then prunes entries older than the configured *Online duration* and counts what remains — no disk scanning required.

`fof/redis` is listed as a Composer `suggest`, so it is **not** installed by default. To enable Redis support, install and configure it separately:

```sh
composer require fof/redis:"*"
```

Then enable Redis sessions in the FoF Redis extension settings. No further configuration is needed in this extension — it detects the active session handler automatically.

## Settings & permissions

From the admin dashboard you can configure:

- **Online duration** — how recently a session must have been active (in minutes) to count as "online". Default: `5`.
- **Cache duration** — how long (in seconds) the calculated guest count is cached before being recalculated. Default: `600`.

A **View online guests** permission controls which groups can see the widget. Only actors with this permission receive the guest count.

## Installation

Install with composer:

```sh
composer require ianm/online-guests:"*"
```

This will also install [Forum Widgets Core](https://github.com/FriendsOfFlarum/forum-widgets-core), which the widget relies on.

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
