<?php

namespace enaza;

class Pub
{
    public const VISITOR_NAMES = ["Вася", "Петя", "Лёша", "Саша"];
    private array $visitors;
    private Genre $genre;
    private array $danceFloor = [];
    private array $bar = [];

    public function __construct(private GenreFactory $genreFactory)
    {
        $this->genreFactory = $genreFactory;
        $this->visitors = array_map(function ($name) {
            return new Visitor($name, $this->genreFactory->getRandomGenre());
        }, self::VISITOR_NAMES);
        $this->changeGenre();
    }

    public function getCurrentGenre(): Genre
    {
        return $this->genre;
    }

    public function changeGenre()
    {
        $this->genre = $this->genreFactory->getRandomGenre();
        $this->onChangeGenre();
    }

    private function onChangeGenre()
    {
        $this->danceFloor = array_values(array_filter($this->visitors, function (Visitor $visitor) {
            return $visitor->getGenre()->isEqual($this->getCurrentGenre());
        }));
        $this->bar = array_values(array_filter($this->visitors, function (Visitor $visitor) {
            return !$visitor->getGenre()->isEqual($this->getCurrentGenre());
        }));
    }

    public function getDanceFloorVisitors()
    {
        return $this->danceFloor;
    }

    public function getBarVisitors()
    {
        return $this->bar;
    }
}
