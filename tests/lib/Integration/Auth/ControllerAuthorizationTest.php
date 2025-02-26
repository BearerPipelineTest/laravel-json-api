<?php
/*
 * Copyright 2022 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CloudCreativity\LaravelJsonApi\Tests\Integration\Auth;

use CloudCreativity\LaravelJsonApi\Tests\Integration\TestCase;
use DummyApp\Post;

class ControllerAuthorizationTest extends TestCase
{

    /**
     * @var array
     */
    private array $data;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $post = factory(Post::class)->create();

        $this->data = [
            'type' => 'comments',
            'attributes' => [
                'content' => '...'
            ],
            'relationships' => [
                'commentable' => [
                    'data' => [
                        'type' => 'posts',
                        'id' => (string) $post->getRouteKey(),
                    ],
                ],
            ],
        ];
    }

    public function testCreateUnauthenticated()
    {
        $response = $this
            ->jsonApi()
            ->withData($this->data)
            ->post('/api/v1/comments');

        $response->assertStatus(401)->assertJson([
            'errors' => [
                [
                    'title' => 'Unauthenticated',
                    'status' => '401',
                ],
            ],
        ]);
    }

    public function testCreateUnauthorized()
    {
        $response = $this
            ->actingAsUser('admin')
            ->jsonApi()
            ->withData($this->data)
            ->post('/api/v1/comments');

        $response->assertStatus(403)->assertJson([
            'errors' => [
                [
                    'title' => 'Unauthorized',
                    'status' => '403',
                ],
            ],
        ]);
    }

    public function testCreateAllowed()
    {
        $response = $this
            ->actingAsUser()
            ->jsonApi()
            ->withData($this->data)
            ->post('/api/v1/comments');

        $response->assertStatus(201);
    }
}
