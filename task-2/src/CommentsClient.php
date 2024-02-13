<?php

namespace Drom;

use Drom\Entity\Comment;
use Drom\Exception\CommentClientException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final readonly class CommentsClient
{
    private ClientInterface $client;
    private array $config;

    public function __construct(?ClientInterface $client = null, array $config = [])
    {
        $this->client = $client ?: new Client();

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->config = $resolver->resolve($config);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CommentClientException
     * @throws \JsonException
     *
     * @psalm-return list<Comment>
     */
    public function get(): array
    {
        $request = $this->createRequest('get_comments');
        $response = $this->client->sendRequest($request);

        $this->assertResponseIsSuccess($response);

        $responseContent = $this->decodeResponseBody($response);

        return array_map(
            static fn(array $item) => new Comment($item['id'], $item['name'], $item['text']),
            $responseContent,
        );
    }

    /**
     * @throws \JsonException
     * @throws CommentClientException
     * @throws ClientExceptionInterface
     */
    public function post(Comment $comment): Comment
    {
        $request = $this->createRequest('post_comment', $comment);
        $response = $this->client->sendRequest($request);

        $this->assertResponseIsSuccess($response);

        $responseContent = $this->decodeResponseBody($response);

        return $comment->withId($responseContent['id']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws GuzzleException
     * @throws CommentClientException
     * @throws \JsonException
     */
    public function put(Comment $comment): Comment
    {
        $this->assertCommentIsExist($comment);

        $request = $this->createRequest('put_comment', $comment);
        $response = $this->client->sendRequest($request);

        $this->assertResponseIsSuccess($response);

        $responseContent = $this->decodeResponseBody($response);

        return $comment->withName($responseContent['name'])->withText($responseContent['text']);
    }

    private function configureOptions(OptionsResolver $resolver): void
    {
        $generalHeaders = ['Host' => 'example.com'];

        $resolver->setDefaults([
            'get_comments' => [
                'method' => 'GET',
                'uri' => '/comments',
                'headers' => $generalHeaders,
            ],
            'post_comment' => [
                'method' => 'POST',
                'uri' => '/comment',
                'headers' => $generalHeaders + ['Content-type' => 'application/json; charset=utf-8'],
            ],
            'put_comment' => [
                'method' => 'PUT',
                'uri' => '/comment/%s',
                'headers' => $generalHeaders + ['Content-type' => 'application/json; charset=utf-8'],
            ],
        ]);
    }

    private function createRequest(string $endpoint, ?Comment $comment = null): Request
    {
        ['method' => $method, 'uri' => $uri, 'headers' => $headers] = $this->config[$endpoint];

        $content = '';
        if ($comment) {
            $content = $this->encodeRequestBody($comment);
            $headers += ['Content-Length' => mb_strlen($content)];
        }

        if ('PUT' === $method) {
            $uri = sprintf($uri, $comment->getId());
        }

        return new Request($method, $uri, $headers, $content);
    }

    /**
     * @throws \JsonException
     */
    private function encodeRequestBody(Comment $comment): string
    {
        return json_encode($comment, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws CommentClientException
     */
    private function assertResponseIsSuccess(ResponseInterface $response): void
    {
        if ( ! in_array($response->getStatusCode(), [200, 201], true)) {
            throw new CommentClientException($response->getReasonPhrase());
        }
    }

    /**
     * @throws \JsonException
     */
    private function decodeResponseBody(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws CommentClientException
     */
    private function assertCommentIsExist(Comment $comment): void
    {
        if (null === $comment->getId()) {
            throw new CommentClientException('can\'t update non-existent comment');
        }
    }
}
