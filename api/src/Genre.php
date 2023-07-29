<?php

namespace enaza;

class Genre
{
    public function __construct(private string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isEqual(Genre $genre): bool
    {
        return $this->getName() === $genre->getName();
    }
}
