<?php

namespace enaza;

class Genre
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
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
