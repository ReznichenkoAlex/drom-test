<?php

namespace Drom\Tests\Unit;

use Drom\CommentsClient;
use Drom\Entity\Comment;
use Drom\Exception\CommentClientException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

class CommentsClientTest extends TestCase
{
    private CommentsClient $commentsClient;
    private MockObject $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->commentsClient = new CommentsClient($this->httpClient);
    }

    /**
     * @dataProvider getComments
     */
    public function testGetMethodSuccessful(string $responseBody, array $commentsList): void
    {
        $response = new Response(200, [], $responseBody);
        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        self::assertEquals($commentsList, $this->commentsClient->get());
    }

    /**
     * @return (Comment[]|string)[][]
     */
    public static function getComments(): array
    {
        return [
            'Нет комментариев' => [
                '[]',
                [],
            ],
            'Есть комментарии' => [
                '[{"id":1,"name":"foo","text":"bar"},{"id":2,"name":"baz","text":"var"}]',
                [new Comment(1, 'foo', 'bar'), new Comment(2, 'baz', 'var')],
            ],
        ];
    }

    public function testGetMethodFailedResponse(): void
    {
        $response = new Response(500);
        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        $this->expectException(CommentClientException::class);
        $this->commentsClient->get();
    }

    public function testPostMethodSuccessful(): void
    {
        $response = new Response(201, [], json_encode(['id' => 1]));
        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        $newComment = new Comment(null, 'foo', 'bar');
        $comment = $this->commentsClient->post($newComment);

        self::assertNotEquals($newComment, $comment);
        self::assertEmpty($newComment->getId());
        self::assertNotEmpty($comment->getId());
    }

    public function testPostMethodFailedCantCreateIncompleteComment(): void
    {
        $newComment = new Comment(null, '', 'bar');

        $this->expectException(CommentClientException::class);
        $this->commentsClient->post($newComment);
    }

    public function testPutMethodSuccessful(): void
    {
        $content = '{"id":3,"name":"foo","text":"bar"}';
        $response = new Response(200, [], $content);
        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        $updatingComment = new Comment(3, 'foo', 'bar');
        $this->commentsClient->put($updatingComment);
    }

    public function testPutMethodFailedCantModifyNonExistentComment(): void
    {
        $response = new Response(404);
        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response)
        ;

        $updatingComment = new Comment(6, 'foo', 'bar');

        $this->expectException(CommentClientException::class);
        $this->commentsClient->put($updatingComment);
    }

    public function testPutMethodFailedBreakPreCondition(): void
    {
        $updatingComment = new Comment(null, 'foo', 'bar');

        $this->expectException(CommentClientException::class);
        $this->commentsClient->put($updatingComment);
    }
}
