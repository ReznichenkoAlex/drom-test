<?php

namespace Drom\Entity;

final readonly class Comment implements \JsonSerializable
{
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $text = null
    ) {}

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
     * @return (int|null|string)[]
     *
     * @psalm-return array{id: int|null, name: null|string, text: null|string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'text' => $this->text,
        ];
    }
}
