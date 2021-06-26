<?php

namespace enaza;

class Visitor
{
    private $name;
    private $genre;

    public function __construct(string $name, Genre $genre)
    {
        $this->name = $name;
        $this->genre = $genre;
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
