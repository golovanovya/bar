<?php

namespace enaza;

class GenreFactory
{
    private $genreNames = ["Рок", "Хип-хоп", "Фолк", "Рейв", "Кантри", "Шансон", "Джаз", "Электронная музыка", "Поп-музыка"];

    public function getGenreList()
    {
        return array_map(function ($genreName) {
            return new Genre($genreName);
        }, $this->genreNames);
    }

    public function getRandomGenre(): Genre
    {
        return new Genre($this->genreNames[array_rand($this->genreNames, 1)]);
    }
}
