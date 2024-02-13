<?php

namespace Drom\Tests\Integration;

use Drom\CommentsClient;
use Drom\Entity\Comment;
use Drom\Exception\CommentClientException;
use PHPUnit\Framework\TestCase;

class PostCommentTest extends TestCase
{
    private CommentsClient $commentsClient;

    protected function setUp(): void
    {
        $this->commentsClient = new CommentsClient(new HttpClientDouble());
    }

    public function testPostComment(): void
    {
        $comments = $this->commentsClient->get();
        self::assertEmpty($comments);

        $comment = new Comment(null, 'Foo', 'bar');
        $comment = $this->commentsClient->post($comment);

        $comments = $this->commentsClient->get();
        self::assertNotEmpty($comments);
        self::assertEquals($comment, $comments[0]);

        $comment = $comment->withName('Baz');
        $comment = $this->commentsClient->put($comment);
        $comments = $this->commentsClient->get();

        self::assertEquals('Baz', $comment->getName());
        self::assertEquals('Baz', $comments[0]->getName());
    }

    public function testUnsuccessModifyComment(): void
    {
        $comment = new Comment(null, 'Foo', 'bar');
        $comment = $this->commentsClient->post($comment);

        $comments = $this->commentsClient->get();
        self::assertNotEmpty($comments);
        self::assertEquals(1, $comments[0]->getId());

        $comment = $comment->withName('Baz')->withId(4);
        try {
            $this->commentsClient->put($comment);
        } catch (CommentClientException $exception) {
            self::assertEquals('Not Found', $exception->getMessage());
        }
    }

    public function testPostTwoIdenticalComments(): void
    {
        $comment1 = new Comment(null, 'Foo', 'bar');
        $comment1 = $this->commentsClient->post($comment1);
        $comment2 = $this->commentsClient->post($comment1);

        $comments = $this->commentsClient->get();
        self::assertCount(2, $comments);
        self::assertNotEquals($comment2->getId(), $comment1->getId());
        self::assertEquals($comment1->getName(), $comment2->getName());
        self::assertEquals($comment1->getText(), $comment2->getText());
    }
}
