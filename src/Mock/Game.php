<?php

namespace Sitnikovik\ArrayToObjectSerializer\Mock;

use Sitnikovik\ArrayToObjectSerializer\ToObject;

class Game
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var Player[]
     *
     * @ToObject(Sitnikovik\ArrayToObjectSerializer\Mock\Player)
     */
    public $players = [];
}