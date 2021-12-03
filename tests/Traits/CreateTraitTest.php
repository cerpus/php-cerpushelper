<?php

namespace Cerpus\HelperTests\Traits;

use Cerpus\Helper\Traits\CreateTrait;
use Cerpus\HelperTests\Utils\HelperTestCase;
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

class A
{
    var $propA;
}

class B extends A
{
    use CreateTrait;

    var $propB;
}

class CreateTraitTest extends HelperTestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function CanNotSetParentPropertiesOnInheritedClasses()
    {
        $b = B::create(123);
        $this->assertEquals(123, $b->propB);
        $this->assertArrayHasKey('propB', $b->toArray());

        $this->expectException(\OutOfRangeException::class);
        B::create(123, 456);
    }

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
            'full' => true,
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
            'weight' => $weight,
        ]);
        $this->assertTrue($truck->isDirty());
        $this->assertTrue($secondTruck->isDirty());

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
                ],
            ],
            'full' => false,
            'wasRecentlyCreated' => false,
            'isDirty' => true,
        ];

        $toArrayWithoutIsDirty = [
            'color' => $color,
            'maxWeight' => $maxWeight,
            'model' => "UltraSuper GT 3000",
            'cargo' => [
                [
                    'weight' => $weight,
                    'fragile' => false,
                    'content' => [],
                    'wasRecentlyCreated' => false,
                ],
            ],
            'full' => false,
            'wasRecentlyCreated' => false,
        ];

        $toArrayWithIsDirty = [
            'color' => $color,
            'maxWeight' => $maxWeight,
            'model' => "UltraSuper GT 3000",
            'cargo' => [
                [
                    'weight' => $weight,
                    'fragile' => false,
                    'content' => [],
                    'isDirty' => true,
                ],
            ],
            'full' => false,
            'isDirty' => true,
        ];

        $toArrayWithoutMetaproperties = [
            'color' => $color,
            'maxWeight' => $maxWeight,
            'model' => "UltraSuper GT 3000",
            'cargo' => [
                [
                    'weight' => $weight,
                    'fragile' => false,
                    'content' => [],
                ],
            ],
            'full' => false,
        ];

        $this->assertEquals($toArrayWithoutMetaproperties, $truck->toArray());
        $this->assertEquals($toArrayWithoutMetaproperties, $secondTruck->toArray());

        $this->assertEquals($toArray, $truck->toArray(true));
        $this->assertEquals($toArray, $secondTruck->toArray(true));

        $this->assertEquals($toArrayWithIsDirty, $truck->toArray('isDirty'));
        $this->assertEquals($toArrayWithIsDirty, $secondTruck->toArray('isDirty'));

        $this->assertEquals($toArrayWithoutIsDirty, $truck->toArray(['isDirty']));
        $this->assertEquals($toArrayWithoutIsDirty, $secondTruck->toArray(['isDirty']));

        $content = new Content();
        $content->type = "Glass";
        $content->name = "Rosendal";

        $cargo->addContent($content);
        $toArray['cargo'][0]['content'][] = $content;
        $toArrayWithoutMetaproperties['cargo'][0]['content'][] = $content;
        $toArrayWithIsDirty['cargo'][0]['content'][] = $content;
        $toArrayWithoutIsDirty['cargo'][0]['content'][] = $content;

        $this->assertEquals($toArrayWithoutMetaproperties, $truck->toArray());
        $this->assertEquals($toArray, $truck->toArray(true));
        $this->assertEquals($toArrayWithIsDirty, $truck->toArray('isDirty'));
        $this->assertEquals($toArrayWithoutIsDirty, $truck->toArray(['isDirty']));

        $this->assertTrue($truck->isDirty());
        $this->assertTrue($secondTruck->isDirty());
    }
}
