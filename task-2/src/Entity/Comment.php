<?php

namespace Drom\Entity;

/**
 * @psalm-immutable
 */
final readonly class Comment implements \JsonSerializable
{
    public function __construct(private ?int $id, private string $name, private string $text) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function withId(int $id): self
    {
        return new self($id, $this->name, $this->text);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function withName(string $name): self
    {
        return new self($this->id, $name, $this->text);
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function withText(string $text): self
    {
        return new self($this->id, $this->name, $text);
    }

    /**
     * @psalm-api
     *
     * @psalm-return array{name: null|string, text: null|string}
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'text' => $this->text,
        ];
    }
}
