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

use Afrux\ForumWidgets\SafeCacheRepositoryAdapter;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Session\FileSessionHandler;
use SessionHandlerInterface;
use Symfony\Component\Finder\Finder;

class GuestUserCount
{
    protected $sessionHandler;
    protected $cache;
    protected $settings;
    protected $onlineDurationMinutes = 5;
    protected $cacheDurationSeconds = 60;

    public function __construct(SessionHandlerInterface $sessionHandler, SafeCacheRepositoryAdapter $cache, SettingsRepositoryInterface $settings)
    {
        $this->sessionHandler = $sessionHandler;
        $this->cache = $cache;
        $this->settings = $settings;
    }

    public function __invoke(ForumSerializer $serializer): array
    {
        if ($serializer->getActor()->hasPermission('viewOnlineGuests')) {
            $this->onlineDurationMinutes = (int) $this->settings->get('ianm-online-guests.online-duration');
            $this->cacheDurationSeconds = (int) $this->settings->get('ianm-online-guests.cache-duration');

            return [
                'onlineGuests' => $this->cache->remember('ianm-online-guests', $this->cacheDurationSeconds, function () {
                    return $this->getGuestCount();
                })
            ];
        }

        return [];
    }

    protected function getGuestCount(): int
    {
        if ($this->sessionHandler instanceof FileSessionHandler) {
            return $this->fromFiles();
        }

        // TODO: add Redis, Database, etc support.

        return 0;
    }

    private function fromFiles(): int
    {
        $reflection = new \ReflectionClass($this->sessionHandler);
        $pathProperty = $reflection->getProperty('path');
        $pathProperty->setAccessible(true);
        $sessionFilesPath = $pathProperty->getValue($this->sessionHandler);

        $recentlyActiveSessionFiles = Finder::create()
            ->in($sessionFilesPath)
            ->files()
            ->ignoreDotFiles(true)
            ->date('>= now - '.$this->onlineDurationMinutes.' minutes');

        $sessions = [];
        foreach ($recentlyActiveSessionFiles as $file) {
            $sessions[] = unserialize(file_get_contents($file->getRealPath()));
        }

        $guestSessions = array_filter($sessions, function ($session) {
            return ! isset($session['access_token']);
        });

        return count($guestSessions);
    }
}
