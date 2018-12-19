<?php

namespace Cerpus\HelperTests\Utils\Traits;


use Faker\Generator;
use Faker\Factory;

trait WithFaker
{
    /** @var  Generator */
    protected $faker;

    public function setUpFaker()
    {
        $this->faker = Factory::create();
    }

}