<?php

/*
 * This file is part of ianm/online-guests.
 *
 * Copyright (c) IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\OnlineGuests\Middleware;

use FoF\Redis\Session\RedisSessionHandler;
use Illuminate\Cache\RedisStore;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TrackGuestSession implements MiddlewareInterface
{
    public const GUEST_SESSIONS_ZSET_KEY = 'ianm-online-guests';

    public function __construct(protected RedisSessionHandler $sessionHandler) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');

        if ($session && ! $session->get('access_token')) {
            /** @var RedisStore $store */
            $store = $this->sessionHandler->getCache()->getStore();
            $store->connection()->zadd(self::GUEST_SESSIONS_ZSET_KEY, time(), $session->getId());
        }

        return $handler->handle($request);
    }
}
