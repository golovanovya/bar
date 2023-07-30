<?php

namespace enaza;

class GenreChanged
{
    public function __construct(private Genre $genre)
    {
    }

    public function getGenre()
    {
        return $this->genre;
    }
}
