# Array-to-object serializer

Tool to convert your class nested with others to associative array

## How to use

Just run: 
```php
// Array to convert
$array = [
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

// Convert array to object with one step
$object = \Sitnikovik\ArrayToObjectSerializer\ArrayToObject::serialize($array, "needed_class_name_with_namespace");
```

## How to specify object

You have to create object with properties annotated with `@ToObject` for each property you want to convert.

> Note: You have to specify full class name with namespace in `@ToObject` annotation 
> with `use` statement and `::class` syntax

```php
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
```

