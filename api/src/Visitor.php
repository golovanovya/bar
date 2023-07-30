<?php

namespace enaza;

class Visitor
{
    public function __construct(
        private string $name,
        private Genre $genre,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGenre(): Genre
    {
        return $this->genre;
    }
}
