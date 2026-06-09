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

use Flarum\Settings\SettingsRepositoryInterface;
use FoF\ForumWidgets\SafeCacheRepositoryAdapter;
use FoF\Redis\Session\RedisSessionHandler;
use IanM\OnlineGuests\Middleware\TrackGuestSession;
use Illuminate\Session\FileSessionHandler;
use SessionHandlerInterface;
use Symfony\Component\Finder\Finder;

class GuestUserCount
{
    protected int $onlineDurationMinutes = 5;
    protected int $cacheDurationSeconds = 600;

    public function __construct(
        protected SessionHandlerInterface $sessionHandler,
        protected SafeCacheRepositoryAdapter $cache,
        protected SettingsRepositoryInterface $settings,
    ) {
    }

    public function getCount(): int
    {
        $this->onlineDurationMinutes = (int) $this->settings->get('ianm-online-guests.online-duration');
        $this->cacheDurationSeconds = (int) $this->settings->get('ianm-online-guests.cache-duration');

        return $this->cache->remember('ianm-online-guests', $this->cacheDurationSeconds, function () {
            return $this->getGuestCount();
        });
    }

    protected function getGuestCount(): int
    {
        if ($this->sessionHandler instanceof RedisSessionHandler) {
            return $this->fromRedis();
        }

        if ($this->sessionHandler instanceof FileSessionHandler) {
            return $this->fromFiles();
        }

        return 0;
    }

    private function fromRedis(): int
    {
        try {
            /** @var \FoF\Redis\Session\RedisSessionHandler $handler */
            $handler = $this->sessionHandler;
            /** @var \Illuminate\Cache\RedisStore $store */
            $store = $handler->getCache()->getStore();
            $connection = $store->connection();
            $cutoff = time() - (max(1, $this->onlineDurationMinutes) * 60);
            $connection->zremrangebyscore(TrackGuestSession::GUEST_SESSIONS_ZSET_KEY, '-inf', (string) $cutoff);

            return (int) $connection->zcard(TrackGuestSession::GUEST_SESSIONS_ZSET_KEY);
        } catch (\Exception) {
            return 0;
        }
    }

    private function fromFiles(): int
    {
        try {
            $reflection = new \ReflectionClass($this->sessionHandler);
            $pathProperty = $reflection->getProperty('path');
            $sessionFilesPath = $pathProperty->getValue($this->sessionHandler);
        } catch (\ReflectionException) {
            return 0;
        }

        $duration = max(1, $this->onlineDurationMinutes);

        $recentlyActiveSessionFiles = Finder::create()
            ->in($sessionFilesPath)
            ->files()
            ->ignoreDotFiles(true)
            ->date('>= now - '.$duration.' minutes');

        $guestCount = 0;
        foreach ($recentlyActiveSessionFiles as $file) {
            $contents = @file_get_contents($file->getRealPath());

            if ($contents === false) {
                continue;
            }

            $session = @unserialize($contents, ['allowed_classes' => false]);
            if (is_array($session) && ! isset($session['access_token'])) {
                $guestCount++;
            }
        }

        return $guestCount;
    }
}
