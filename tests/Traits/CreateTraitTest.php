<?php

namespace Cerpus\HelperTests\Traits;

use Cerpus\HelperTests\Utils\HelperTestCase;
use Cerpus\Helper\Traits\CreateTrait;
use Cerpus\HelperTests\Utils\Traits\WithFaker;

class Truck
{

    use CreateTrait;

    public $color;
    public $maxWeight;

    protected $model = "UltraSuper GT 3000";

    private $cargo;
    private $full = false;

    public function __construct()
    {
        $this->cargo = collect();
    }

    public function setFull(bool $full)
    {
        $this->full = $full;
    }

    public function addCargo(Cargo $cargo)
    {
        $this->cargo->push($cargo);
    }

    public function setIsDirty($isDirty)
    {
        $this->isDirty = $isDirty;
    }
}

class Cargo
{

    use CreateTrait;

    public $weight;
    public $fragile = false;

    private $content;

    public function __construct()
    {
        $this->content = collect();
    }

    public function addContent(Content $content)
    {
        $this->content->push($content);
    }
}

class Content
{
    public $type;
    public $name;
}

class CreateTraitTest extends HelperTestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function createTruck()
    {
        $color = $this->faker->colorName;
        $maxWeight = $this->faker->numberBetween(1, 500);

        $truck = new Truck();
        $truck->color = $color;
        $truck->maxWeight = $maxWeight;
        $truck->setIsDirty(true);

        $truck2 = Truck::create([
            'color' => $color,
            'maxWeight' => $maxWeight,
        ]);

        $this->assertEquals($truck, $truck2);

        $truck3 = Truck::create($color, $maxWeight);
        $this->assertEquals($truck2, $truck3);

        $truck->setFull(true);
        $truck2 = Truck::create([
            'color' => $color,
            'maxWeight' => $maxWeight,
            'full' => true
        ]);
        $this->assertEquals($truck, $truck2);
    }

    /**
     * @test
     */
    public function truckAndCargoToArray()
    {
        $color = $this->faker->colorName;
        $maxWeight = 500;

        $weight = $this->faker->numberBetween(1, 500);
        /** @var Truck $truck */
        $truck = Truck::create([
            'color' => $color,
            'maxWeight' => $maxWeight,
        ]);
        /** @var Truck $secondTruck */
        $secondTruck = Truck::create($color, $maxWeight);

        /** @var Cargo $cargo */
        $cargo = Cargo::create([
            'weight' => $weight
        ]);

        $truck->addCargo($cargo);
        $secondTruck->addCargo($cargo);

        $toArray = [
            'color' => $color,
            'maxWeight' => $maxWeight,
            'model' => "UltraSuper GT 3000",
            'cargo' => [
                [
                    'weight' => $weight,
                    'fragile' => false,
                    'content' => [],
                    'wasRecentlyCreated' => false,
                    'isDirty' => true,
                ]
            ],
            'full' => false,
            'wasRecentlyCreated' => false,
            'isDirty' => true,
        ];

        $this->assertEquals($toArray, $truck->toArray());
        $this->assertEquals($toArray, $secondTruck->toArray());

        $content = new Content();
        $content->type = "Glass";
        $content->name = "Rosendal";

        $cargo->addContent($content);
        $toArray['cargo'][0]['content'][] = $content;

        $this->assertEquals($toArray, $truck->toArray());
    }
}