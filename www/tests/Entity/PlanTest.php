<?php

namespace App\Tests\Entity;

use App\Entity\Plan;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    public function testGetterAndSetter()
    {
        $plan = new Plan();
        $name = 'Premium Plan';
        $description = 'Best plan ever';
        $limitGeneration = 100;
        $price = 19.99;
        $active = true;

        $plan->setName($name);
        $plan->setDescription($description);
        $plan->setLimitGeneration($limitGeneration);
        $plan->setPrice($price);
        $plan->setActive($active);

        $this->assertEquals($name, $plan->getName());
        $this->assertEquals($description, $plan->getDescription());
        $this->assertEquals($limitGeneration, $plan->getLimitGeneration());
        $this->assertEquals($price, $plan->getPrice());
        $this->assertEquals($active, $plan->isActive());
    }
}
