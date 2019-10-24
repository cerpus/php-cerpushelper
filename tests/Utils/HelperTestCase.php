<?php

namespace Cerpus\HelperTests\Utils;

use Cerpus\HelperTests\Utils\Traits\WithFaker;
use PHPUnit\Framework\TestCase;

/**
 * Class HelperTestCase
 * @package Cerpus\HelperTests\Utils
 *
 * @method void setupFaker
 */
class HelperTestCase extends TestCase
{

    public function setUp()
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
