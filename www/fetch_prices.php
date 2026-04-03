<?php
require __DIR__ . '/vendor/autoload.php';

$stripe = new \Stripe\StripeClient('sk_test_REDACTED');

$products = [
    'Premium' => 'prod_UE4ZIS0XhBB2FZ',
    'Enterprise' => 'prod_UE4aTDRySn4PQE'
];

$results = [];
foreach ($products as $name => $prodId) {
    try {
        $prices = $stripe->prices->all(['product' => $prodId, 'active' => true]);
        if (count($prices->data) > 0) {
            $results[$name] = $prices->data[0]->id;
        } else {
            $results[$name] = "NO_ACTIVE_PRICE_FOUND";
        }
    } catch (\Exception $e) {
        $results[$name] = "ERROR: " . $e->getMessage();
    }
}

echo json_encode($results, JSON_PRETTY_PRINT);
