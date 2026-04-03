<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlanFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Free Plan
        $freePlan = new Plan();
        $freePlan->setName('Free');
        $freePlan->setDescription('Basic access to the platform.');
        $freePlan->setLimitGeneration(2);
        $freePlan->setRole('ROLE_USER');
        $freePlan->setPrice(0.0);
        $freePlan->setActive(true);
        $manager->persist($freePlan);
        $this->addReference('plan_free', $freePlan);

        // Premium Plan
        $premiumPlan = new Plan();
        $premiumPlan->setName('Premium');
        $premiumPlan->setDescription('Full access with higher limits.');
        $premiumPlan->setLimitGeneration(5);
        $premiumPlan->setRole('ROLE_PREMIUM');
        $premiumPlan->setPrice(9.99);
        $premiumPlan->setActive(true);
        $manager->persist($premiumPlan);
        $this->addReference('plan_premium', $premiumPlan);

        // Enterprise Plan (Inactive)
        $enterprisePlan = new Plan();
        $enterprisePlan->setName('Enterprise');
        $enterprisePlan->setDescription('Unlimited access for teams.');
        $enterprisePlan->setLimitGeneration(50);
        $enterprisePlan->setRole('ROLE_ENTERPRISE');
        $enterprisePlan->setPrice(99.99);
        $enterprisePlan->setActive(false);
        $manager->persist($enterprisePlan);
        $this->addReference('plan_enterprise', $enterprisePlan);

        $manager->flush();
    }
}
