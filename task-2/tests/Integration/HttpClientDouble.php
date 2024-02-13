<?php

namespace Drom\Tests\Integration;

use Drom\Entity\Comment;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientDouble implements ClientInterface
{
    /** @var list<Comment> */
    private array $storage = [];

    /**
     * @throws \JsonException
     *
     * @return Response
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if ('GET' === $request->getMethod()) {
            return new Response(
                200,
                [],
                json_encode($this->getCommentItems(), JSON_THROW_ON_ERROR)
            );
        }

        if ('POST' === $request->getMethod()) {
            $data = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            $id = $this->createNewComment($data);

            return new Response(200, [], json_encode(['id' => $id], JSON_THROW_ON_ERROR));
        }

        if ('PUT' === $request->getMethod()) {
            $data = json_decode(
                $request->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $id = $this->getIdFrom($request);
            $comment = $this->storage[$id] ?? null;

            if ( ! $comment) {
                return new Response(404);
            }

            $comment = $this->updateComment($comment, $data);
            return new Response(
                200,
                [],
                json_encode(
                    ['id' => $comment->getId(), 'name' => $comment->getName(), 'text' => $comment->getText()],
                    JSON_THROW_ON_ERROR,
                )
            );
        }

        return new Response(405);
    }

    private function getCommentItems(): array
    {
        $result = [];
        foreach ($this->storage as $comment) {
            $result[] = ['id' => $comment->getId(), 'name' => $comment->getName(), 'text' => $comment->getText()];
        }

        return $result;
    }


    /**
     * @psalm-return int<1, max>
     */
    private function createNewComment(array $data): int
    {
        $lastInsertedKey = array_key_last($this->storage);
        $lastInsertedKey = $lastInsertedKey ? $lastInsertedKey + 1 : 1;
        $comment = new Comment($lastInsertedKey, $data['name'], $data['text']);
        $this->storage[$lastInsertedKey] = $comment;

        return $lastInsertedKey;
    }

    private function updateComment(Comment $comment, array $data): Comment
    {
        $comment = $comment->withName($data['name'])->withText($data['text']);
        $this->storage[$comment->getId()] = $comment;

        return $comment;
    }

    private function getIdFrom(RequestInterface $request): int
    {
        $path = explode('/', $request->getUri()->getPath());

        return (int) array_pop($path);
    }
}
