<?php

namespace Sitnikovik\ArrayToObjectSerializer\Tests;

use ReflectionException;
use Sitnikovik\ArrayToObjectSerializer\ArrayToObject;
use PHPUnit\Framework\TestCase;
use Sitnikovik\ArrayToObjectSerializer\Mock\Game;
use Sitnikovik\ArrayToObjectSerializer\Mock\Player;

class ArrayToObjectTest extends TestCase
{
    /**
     * Input array
     *
     * @var array
     */
    private $input;

    /**
     * @inheritDoc
     * @override
     */
    protected function setUp(): void
    {
        $this->input = [
            "name" => "csgo",
            'players' => [
                [
                    "name" => "bonnie",
                    "health" => 100,
                    "weapon" => [
                        "name" => "m4a1",
                        "ammo" => 30,
                    ],
                ],
                [
                    "name" => "klyde",
                    "health" => 100,
                    "weapon" => [
                        "name" => "ak47",
                        "ammo" => 30,
                    ],
                ],
            ],
        ];
    }

    /**
     * Test serialize
     *
     * @return void
     * @throws ReflectionException
     */
    public function testSerialize(): void
    {
        $game = ArrayToObject::serialize($this->input, Game::class);

        $this->assertInstanceOf(Game::class, $game);
        $this->assertEquals($this->input['name'], $game->name);
        $this->assertCount(2, $game->players);
        $this->assertInstanceOf(Player::class, $game->players[0]);
        $this->assertInstanceOf(Player::class, $game->players[1]);
        $this->assertEquals($this->input['players'][0]['name'], $game->players[0]->name);
        $this->assertEquals($this->input['players'][0]['health'], $game->players[0]->health);
        $this->assertEquals($this->input['players'][0]['weapon']['name'], $game->players[0]->weapon->name);
        $this->assertEquals($this->input['players'][0]['weapon']['ammo'], $game->players[0]->weapon->ammo);
        $this->assertEquals($this->input['players'][1]['name'], $game->players[1]->name);
        $this->assertEquals($this->input['players'][1]['health'], $game->players[1]->health);
        $this->assertEquals($this->input['players'][1]['weapon']['name'], $game->players[1]->weapon->name);
        $this->assertEquals($this->input['players'][1]['weapon']['ammo'], $game->players[1]->weapon->ammo);
    }
}
