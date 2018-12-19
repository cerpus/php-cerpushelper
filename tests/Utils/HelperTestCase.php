<?php

namespace Cerpus\HelperTests\Utils;

use PHPUnit\Framework\TestCase;
use Cerpus\HelperTests\Utils\Traits\WithFaker;

/**
 * Class HelperTestCase
 * @package Cerpus\HelperTests\Utils
 *
 * @method void setupFaker
 */
class HelperTestCase extends TestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->setUpTraits();
    }

    public function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[WithFaker::class])) {
            $this->setUpFaker();
        }
    }
}