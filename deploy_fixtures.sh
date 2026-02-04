#!/bin/bash
docker exec -i symfony-web-v2 bash -c "mkdir -p /var/www/src/DataFixtures && cat > /var/www/src/DataFixtures/PlanFixtures.php" <<'PHP_SCRIPT_EOF'
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
        $freePlan->setLimitGeneration(5);
        $freePlan->setRole('ROLE_USER');
        $freePlan->setPrice(0.0);
        $freePlan->setActive(true);
        $manager->persist($freePlan);

        // Premium Plan
        $premiumPlan = new Plan();
        $premiumPlan->setName('Premium');
        $premiumPlan->setDescription('Full access with higher limits.');
        $premiumPlan->setLimitGeneration(100);
        $premiumPlan->setRole('ROLE_PREMIUM');
        $premiumPlan->setPrice(9.99);
        $premiumPlan->setActive(true);
        $manager->persist($premiumPlan);

        // Enterprise Plan (Inactive)
        $enterprisePlan = new Plan();
        $enterprisePlan->setName('Enterprise');
        $enterprisePlan->setDescription('Unlimited access for teams.');
        $enterprisePlan->setLimitGeneration(1000);
        $enterprisePlan->setRole('ROLE_ENTERPRISE');
        $enterprisePlan->setPrice(99.99);
        $enterprisePlan->setActive(false);
        $manager->persist($enterprisePlan);

        $manager->flush();
    }
}
PHP_SCRIPT_EOF
