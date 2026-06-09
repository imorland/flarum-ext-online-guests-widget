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

use Flarum\Api\Context;
use Flarum\Api\Resource\ForumResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\Foundation\Event\ApplicationBooted;
use IanM\OnlineGuests\Listener\AddRedisMiddleware;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ApiResource(ForumResource::class))
        ->fields(fn () => [
            Schema\Integer::make('onlineGuests')
                ->visible(fn ($forum, Context $context) => $context->getActor()->hasPermission('viewOnlineGuests'))
                ->get(fn ($forum, Context $context) => resolve(GuestUserCount::class)->getCount()),
        ]),

    (new Extend\Settings())
        ->default('ianm-online-guests.online-duration', 5)
        ->default('ianm-online-guests.cache-duration', 600),

    (new Extend\Event())
        ->listen(ApplicationBooted::class, AddRedisMiddleware::class),
];
