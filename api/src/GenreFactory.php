<?php

namespace enaza;

class GenreFactory
{
    public const GENRE_NAMES = [
        "Рок",
        "Хип-хоп",
        "Фолк",
        "Рейв",
        "Кантри",
        "Шансон",
        "Джаз",
        "Электронная музыка",
        "Поп-музыка",
    ];

    public function getGenreList(): array
    {
        return array_map(function ($genreName) {
            return new Genre($genreName);
        }, self::GENRE_NAMES);
    }

    public function getRandomGenre(): Genre
    {
        return new Genre(self::GENRE_NAMES[array_rand(self::GENRE_NAMES, 1)]);
    }
}
