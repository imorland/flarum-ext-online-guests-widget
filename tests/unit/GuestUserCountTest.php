<?php

/*
 * This file is part of ianm/online-guests.
 *
 * Copyright (c) IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\OnlineGuests\Tests\unit;

use FoF\ForumWidgets\SafeCacheRepositoryAdapter;
use FoF\Redis\Session\RedisSessionHandler;
use Flarum\Settings\SettingsRepositoryInterface;
use IanM\OnlineGuests\GuestUserCount;
use Illuminate\Cache\RedisStore;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\FileSessionHandler;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SessionHandlerInterface;

class GuestUserCountTest extends TestCase
{
    private string $sessionPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionPath = sys_get_temp_dir().'/ianm-online-guests-'.uniqid();
        mkdir($this->sessionPath);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->sessionPath.'/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->sessionPath);

        m::close();

        parent::tearDown();
    }

    /**
     * Build a GuestUserCount with the given session handler and call the
     * protected getGuestCount() directly. We test the counting logic in
     * isolation; the cache + permission flow is covered by integration tests
     * (it requires a booted container).
     */
    private function countFor(SessionHandlerInterface $handler, int $onlineDuration = 5): int
    {
        $count = new GuestUserCount(
            $handler,
            m::mock(SafeCacheRepositoryAdapter::class),
            m::mock(SettingsRepositoryInterface::class),
        );

        $reflection = new \ReflectionObject($count);

        $durationProp = $reflection->getProperty('onlineDurationMinutes');
        $durationProp->setValue($count, $onlineDuration);

        $method = $reflection->getMethod('getGuestCount');
        $method->setAccessible(true);

        return $method->invoke($count);
    }

    private function fileSessionHandler(): FileSessionHandler
    {
        return new FileSessionHandler(new Filesystem(), $this->sessionPath, 120);
    }

    /** Write a serialized session payload to a file with a fresh mtime. */
    private function writeSession(string $id, array $payload): void
    {
        file_put_contents($this->sessionPath.'/'.$id, serialize($payload));
    }

    /** @test */
    public function it_returns_zero_for_unknown_session_handler(): void
    {
        $handler = m::mock(SessionHandlerInterface::class);

        $this->assertSame(0, $this->countFor($handler));
    }

    /** @test */
    public function it_counts_recent_guest_sessions_from_files(): void
    {
        $this->writeSession('guest-a', ['_token' => 'x']);
        $this->writeSession('guest-b', ['foo' => 'bar']);

        $this->assertSame(2, $this->countFor($this->fileSessionHandler()));
    }

    /** @test */
    public function it_excludes_logged_in_sessions(): void
    {
        $this->writeSession('guest', ['foo' => 'bar']);
        $this->writeSession('member', ['access_token' => 'abc123']);

        $this->assertSame(1, $this->countFor($this->fileSessionHandler()));
    }

    /** @test */
    public function it_ignores_corrupt_session_files(): void
    {
        $this->writeSession('guest', ['foo' => 'bar']);
        // A file that does not unserialize to an array (unserialize returns false).
        file_put_contents($this->sessionPath.'/corrupt', 'this is not serialized php');

        $this->assertSame(1, $this->countFor($this->fileSessionHandler()));
    }

    /** @test */
    public function it_ignores_sessions_outside_the_online_window(): void
    {
        $this->writeSession('recent', ['foo' => 'bar']);

        $this->writeSession('stale', ['foo' => 'bar']);
        // Backdate well beyond the 5 minute window.
        touch($this->sessionPath.'/stale', time() - 3600);

        $this->assertSame(1, $this->countFor($this->fileSessionHandler(), 5));
    }

    /** @test */
    public function it_returns_zero_when_no_sessions_exist(): void
    {
        $this->assertSame(0, $this->countFor($this->fileSessionHandler()));
    }

    /** @test */
    public function it_dispatches_to_redis_for_a_redis_session_handler(): void
    {
        $connection = m::mock();
        $connection->shouldReceive('zremrangebyscore')->once();
        $connection->shouldReceive('zcard')->once()->andReturn(7);

        $store = m::mock(RedisStore::class);
        $store->shouldReceive('connection')->andReturn($connection);

        $cache = m::mock();
        $cache->shouldReceive('getStore')->andReturn($store);

        $handler = m::mock(RedisSessionHandler::class);
        $handler->shouldReceive('getCache')->andReturn($cache);

        $this->assertSame(7, $this->countFor($handler));
    }

    /** @test */
    public function it_returns_zero_when_redis_throws(): void
    {
        $handler = m::mock(RedisSessionHandler::class);
        $handler->shouldReceive('getCache')->andThrow(new \RuntimeException('redis down'));

        $this->assertSame(0, $this->countFor($handler));
    }
}
