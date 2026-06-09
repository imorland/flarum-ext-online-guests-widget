<?php

/*
 * This file is part of ianm/online-guests.
 *
 * Copyright (c) IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\OnlineGuests\Listener;

use Flarum\Foundation\Event\ApplicationBooted;
use FoF\Redis\Session\RedisSessionHandler;
use IanM\OnlineGuests\Middleware\TrackGuestSession;
use Illuminate\Contracts\Container\Container;
use SessionHandlerInterface;

class AddRedisMiddleware
{
    public function __construct(protected Container $container) {}

    public function handle(ApplicationBooted $event): void
    {
        $handler = $this->container->make(SessionHandlerInterface::class);

        if ($handler instanceof RedisSessionHandler) {
            $this->container->singleton(TrackGuestSession::class, fn () => new TrackGuestSession($handler));

            $this->container->extend('flarum.forum.middleware', function (array $middleware) {
                $middleware[] = TrackGuestSession::class;
                return $middleware;
            });
            $this->container->extend('flarum.api.middleware', function (array $middleware) {
                $middleware[] = TrackGuestSession::class;
                return $middleware;
            });
        }
    }
}
