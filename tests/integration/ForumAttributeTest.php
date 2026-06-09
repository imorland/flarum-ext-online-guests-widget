<?php

/*
 * This file is part of ianm/online-guests.
 *
 * Copyright (c) IanM.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace IanM\OnlineGuests\Tests\integration;

use Flarum\Group\Group;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Flarum\User\User;

class ForumAttributeTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-forum-widgets-core', 'ianm-online-guests');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
        ]);
    }

    private function grantPermissionTo(int $groupId): void
    {
        $this->prepareDatabase([
            'group_permission' => [
                ['group_id' => $groupId, 'permission' => 'viewOnlineGuests'],
            ],
        ]);
    }

    private function forumAttributes(?int $authenticatedAs = null): array
    {
        $options = [];

        if ($authenticatedAs !== null) {
            $options['authenticatedAs'] = $authenticatedAs;
        }

        $response = $this->send(
            $this->request('GET', '/api', $options)
        );

        $body = json_decode($response->getBody()->getContents(), true);

        return $body['data']['attributes'] ?? [];
    }

    #[Test]
    public function guest_without_permission_does_not_receive_the_attribute(): void
    {
        $attributes = $this->forumAttributes();

        $this->assertArrayNotHasKey('onlineGuests', $attributes);
    }

    #[Test]
    public function member_without_permission_does_not_receive_the_attribute(): void
    {
        $attributes = $this->forumAttributes(2);

        $this->assertArrayNotHasKey('onlineGuests', $attributes);
    }

    #[Test]
    public function member_with_permission_receives_an_integer_count(): void
    {
        $this->grantPermissionTo(Group::MEMBER_ID);

        $attributes = $this->forumAttributes(2);

        $this->assertArrayHasKey('onlineGuests', $attributes);
        $this->assertIsInt($attributes['onlineGuests']);
    }

    #[Test]
    public function guest_with_permission_receives_an_integer_count(): void
    {
        $this->grantPermissionTo(Group::GUEST_ID);

        $attributes = $this->forumAttributes();

        $this->assertArrayHasKey('onlineGuests', $attributes);
        $this->assertIsInt($attributes['onlineGuests']);
    }
}
