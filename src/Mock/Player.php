<?php

namespace Sitnikovik\ArrayToObjectSerializer\Mock;

use Sitnikovik\ArrayToObjectSerializer\ToObject;

class Player
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $health;

    /**
     * @var Weapon
     * @ToObject(Sitnikovik\ArrayToObjectSerializer\Mock\Weapon)
     */
    public $weapon;
}