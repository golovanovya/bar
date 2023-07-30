<?php

namespace enaza;

use League\Event\EventDispatcher;

class Pub
{
    public const VISITOR_NAMES = ["Вася", "Петя", "Лёша", "Саша"];
    private array $visitors;
    private Genre $genre;
    private array $danceFloor = [];
    private array $bar = [];

    public function __construct(
        private GenreFactory $genreFactory,
        private EventDispatcher $dispatcher,
    ) {
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

        $this->danceFloor = array_values(array_filter($this->visitors, function (Visitor $visitor) {
            return $visitor->getGenre()->isEqual($this->getCurrentGenre());
        }));
        $this->bar = array_values(array_filter($this->visitors, function (Visitor $visitor) {
            return !$visitor->getGenre()->isEqual($this->getCurrentGenre());
        }));

        $this->dispatcher->dispatch(new GenreChanged($this->genre));
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
