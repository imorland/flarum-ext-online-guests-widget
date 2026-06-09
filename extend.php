<?php

/*
 * This file is part of ianm/online-guests.
 *
 * Copyright (c) IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\OnlineGuests;

use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Extend;
use Flarum\Foundation\Event\ApplicationBooted;
use IanM\OnlineGuests\Listener\AddRedisMiddleware;
use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource;
use Flarum\Api\Schema;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    // @TODO: Replace with the new implementation https://docs.flarum.org/2.x/extend/api#extending-api-resources
    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(GuestUserCount::class),

    (new Extend\Settings())
        ->default('ianm-online-guests.online-duration', 5)
        ->default('ianm-online-guests.cache-duration', 600),

    (new Extend\Event())
        ->listen(ApplicationBooted::class, AddRedisMiddleware::class),
];
