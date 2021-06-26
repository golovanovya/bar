<?php

namespace enaza;

class Pub
{
    private $genreFactory;
    private $visitorNames = ["Вася", "Петя", "Лёша", "Саша"];
    private $visitors;
    private $genre;
    private $danceFloor = [];
    private $bar = [];

    public function __construct(GenreFactory $genreFactory)
    {
        $this->genreFactory = $genreFactory;
        $this->visitors = array_map(function ($name) {
            return new Visitor($name, $this->genreFactory->getRandomGenre());
        }, $this->visitorNames);
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
