<?php

namespace Tests\Feature;

use App\Thread;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateReplyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();

        $this->signIn();
    }

    /** @test */
    function a_reply_requires_a_body_to_be_updated()
    {
        $thread = create(Thread::class);

        $reply = $thread->addReply([
            'user_id' => create(\App\User::class)->id,
            'body' => 'Here is a reply.'
        ]);

        $this->patch($reply->path(), [])->assertSessionHasErrors('body');
    }
    /** @test */
    function a_reply_requires_a_body_to_be_updated2()
    {
        $thread = create(Thread::class);

        $reply = $thread->addReply([
            'user_id' => create(\App\User::class)->id,
            'body' => 'Here is a reply.'
        ]);

        $this->patch($reply->path(), ['body' => 'foo bar'])->assertSessionMissing('body');
    }
}
