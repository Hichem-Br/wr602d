# 10 - Mise en place Stripe

Votre application propose trois plans d'abonnement :

<table id="bkmrk-free-0-%E2%82%AC%2Fmois-url-%E2%86%92-"><tbody><tr><td data-row="2">**FREE**</td><td data-row="2">0 €/mois</td><td data-row="2">URL → PDF, Fusion PDF</td></tr><tr><td data-row="3">**BASIC**</td><td data-row="3">9,90 €/mois</td><td data-row="3">+ HTML, Markdown, Office → PDF</td></tr><tr><td data-row="4">**PREMIUM**</td><td data-row="4">45 €/mois</td><td data-row="4">+ Screenshot + tout le reste</td></tr></tbody></table>

**Objectif :** quand un utilisateur clique sur "Choisir BASIC" ou "Choisir PREMIUM", il est redirigé vers Stripe pour payer, puis son `Plan` est mis à jour automatiquement en base de données.

## 1 - Concepts Stripe

### Le mode test vs production

Stripe fournit deux environnements **totalement séparés** avec des clés distinctes :

<table id="bkmrk-test-sk_test_...-424"><tbody><tr><td data-row="2">**Test**</td><td data-row="2">`sk_test_...`</td><td data-row="2">`4242 4242 4242 4242`</td></tr><tr><td data-row="3">**Production**</td><td data-row="3">`sk_live_...`</td><td data-row="3">Vraies cartes</td></tr></tbody></table>

En développement, on utilise **toujours** le mode test. Les paiements sont simulés, aucune carte réelle n'est débitée.

### Les objets clés

<div class="ql-code-block-container" id="bkmrk-stripe-dashboard-%E2%94%9C%E2%94%80%E2%94%80" spellcheck="false"><div class="ql-code-block" data-language="plain">Stripe Dashboard</div><div class="ql-code-block" data-language="plain">├── Product (ex: "Plan BASIC")</div><div class="ql-code-block" data-language="plain">│ └── Price (ex: 9,90€/mois, récurrent)</div><div class="ql-code-block" data-language="plain">│</div><div class="ql-code-block" data-language="plain">└── Customer (un client Stripe, lié à un User)</div><div class="ql-code-block" data-language="plain">└── Subscription (l'abonnement actif du customer)</div></div>**Product** : ce que vous vendez (ex: "Plan BASIC PDF Factory") **Price** : le tarif d'un Product (montant, devise, récurrence mensuelle/annuelle) **Customer** : représentation Stripe d'un utilisateur **Subscription** : abonnement actif d'un Customer à un Price **Checkout Session** : page de paiement hébergée par Stripe (clé en main)

### Le flux Checkout Session

C'est la méthode **recommandée** : Stripe gère entièrement la page de paiement.

<div class="ql-code-block-container" id="bkmrk-utilisateur-clique-%22" spellcheck="false"><div class="ql-code-block" data-language="plain">Utilisateur clique "Choisir BASIC"</div><div class="ql-code-block" data-language="plain">│</div><div class="ql-code-block" data-language="plain">▼</div><div class="ql-code-block" data-language="plain">[1] PaymentController::checkout()</div><div class="ql-code-block" data-language="plain">→ Crée une Checkout Session via l'API Stripe</div><div class="ql-code-block" data-language="plain">→ Obtient une URL Stripe (https://checkout.stripe.com/...)</div><div class="ql-code-block" data-language="plain">│</div><div class="ql-code-block" data-language="plain">▼</div><div class="ql-code-block" data-language="plain">[2] Redirection vers Stripe</div><div class="ql-code-block" data-language="plain">→ L'utilisateur entre ses coordonnées bancaires</div><div class="ql-code-block" data-language="plain">→ Stripe traite le paiement</div><div class="ql-code-block" data-language="plain">│</div><div class="ql-code-block" data-language="plain">├── Succès → redirection vers /payment/success</div><div class="ql-code-block" data-language="plain">└── Annulation → redirection vers /payment/cancel</div><div class="ql-code-block" data-language="plain">│</div><div class="ql-code-block" data-language="plain">▼</div><div class="ql-code-block" data-language="plain">[3] Stripe envoie un Webhook (en parallèle, dans tous les cas)</div><div class="ql-code-block" data-language="plain">→ POST sur /payment/webhook</div><div class="ql-code-block" data-language="plain">→ Événement : checkout.session.completed</div><div class="ql-code-block" data-language="plain">→ On met à jour user.plan en base de données</div><div class="ql-code-block" data-language="plain">  
</div></div>**Pourquoi le webhook et pas la page de succès ?** La page de succès peut être contournée (l'utilisateur ferme le navigateur, coupe internet...). Le webhook est envoyé **directement par Stripe** vers votre serveur, indépendamment du navigateur. C'est la source de vérité.

## 2 - Installation et configuration

### Installer le SDK PHP Stripe

<div class="ql-code-block-container" id="bkmrk-composer-require-str" spellcheck="false"><div class="ql-code-block" data-language="plain">composer require stripe/stripe-php</div></div>### Obtenir les clés API

1. Créer un compte sur [dashboard.stripe.com](https://dashboard.stripe.com/)
2. **Activer le mode Test** (toggle en haut à gauche du Dashboard)
3. Aller dans **Developers → API keys**
4. Copier la **Publishable key** (`pk_test_...`) et la **Secret key** (`sk_test_...`)

### Configurer `.env`

<div class="ql-code-block-container" id="bkmrk-%23-.env-%28ou-.env.loca" spellcheck="false"><div class="ql-code-block" data-language="plain">\# .env (ou .env.local pour ne pas commiter les clés)</div><div class="ql-code-block" data-language="plain">STRIPE_SECRET_KEY=<votre_cle_secrete_test></div><div class="ql-code-block" data-language="plain">STRIPE_PUBLISHABLE_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxxxxx</div><div class="ql-code-block" data-language="plain">STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxxxxx</div></div>### Déclarer les paramètres dans `services.yaml`

<div class="ql-code-block-container" id="bkmrk-%23-config%2Fservices.ya" spellcheck="false"><div class="ql-code-block" data-language="plain">\# config/services.yaml</div><div class="ql-code-block" data-language="plain">parameters:</div><div class="ql-code-block" data-language="plain">stripe_secret_key: '%env(STRIPE_SECRET_KEY)%'</div><div class="ql-code-block" data-language="plain">stripe_publishable_key: '%env(STRIPE_PUBLISHABLE_KEY)%'</div><div class="ql-code-block" data-language="plain">stripe_webhook_secret: '%env(STRIPE_WEBHOOK_SECRET)%'</div></div>## 3 - Préparer les données : Products &amp; Prices Stripe

### Dans le Stripe Dashboard (mode Test)

Pour chaque plan payant, il faut créer un **Product** et son **Price** dans Stripe.

#### Plan BASIC (9,90 €/mois)

1. Dashboard → **Product catalog** → **+ Add product**
2. Nom : `Plan BASIC - PDF Factory`
3. Prix : `9,90` € — Récurrent — Mensuel
4. Cliquer sur **Save product**
5. Copier l'**ID du Price** : `price_xxxxxxxxxxxxxxxxxx`

#### Plan PREMIUM (45 €/mois)

1. Même procédure
2. Nom : `Plan PREMIUM - PDF Factory`
3. Prix : `45,00` € — Récurrent — Mensuel
4. Copier l'**ID du Price** : `price_yyyyyyyyyyyyyyyyyy`

> Ces IDs `price_xxx` seront stockés dans votre base de données, dans l'entité `Plan`.

## 4 - Ajouter stripePriceId à l'entité Plan

L'entité `Plan` a besoin d'un champ pour stocker l'identifiant Stripe du tarif.

## 5 - Créer le StripeService

On centralise toute la logique Stripe dans un service dédié.

```php
// src/Service/StripeService.php
<?php


namespace App\Service;


use App\Entity\Plan;
use App\Entity\User;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;


class StripeService
{
  public function __construct(
    private string $secretKey,
    private string $webhookSecret,
  ) {
    Stripe::setApiKey($this->secretKey);
  }


  /**
   * Crée une Checkout Session Stripe pour l'abonnement à un plan.
   * Retourne l'URL vers laquelle rediriger l'utilisateur.
   */
  public function createCheckoutSession(
    User $user,
    Plan $plan,
    string $successUrl,
    string $cancelUrl,
  ): string {
    $session = Session::create([
      'mode' => 'subscription',
      'customer_email' => $user->getEmail(),
      'line_items' => [[
        'price' => $plan->getStripePriceId(),
        'quantity' => 1,
      ]],
      'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
      'cancel_url' => $cancelUrl,
      // On stocke l'ID utilisateur pour le retrouver dans le webhook
      'metadata' => [
        'user_id' => $user->getId(),
        'plan_id' => $plan->getId(),
      ],
      'subscription_data' => [
        'metadata' => [
          'user_id' => $user->getId(),
          'plan_id' => $plan->getId(),
        ],
      ],
    ]);


    return $session->url;
  }


  /**
   * Vérifie la signature du webhook Stripe et retourne l'événement.
   * Lève une exception si la signature est invalide.
   */
  public function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
  {
    return Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
  }
}

```

<div class="ql-code-block-container" id="bkmrk-injecter-les-param%C3%A8t" spellcheck="false"><div class="ql-code-block" data-language="plain"><span style="color: rgb(34, 34, 34); font-family: -apple-system, 'system-ui', 'Segoe UI', Oxygen, Ubuntu, Roboto, Cantarell, 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif; font-size: 2.333em; font-weight: 400;">Injecter les paramètres dans </span>`services.yaml`</div></div>```yaml
# config/services.yaml
services:
App\Service\StripeService:
arguments:
$secretKey: '%stripe_secret_key%'
$webhookSecret: '%stripe_webhook_secret%'
```

<div class="ql-code-block-container" id="bkmrk-" spellcheck="false"><div class="ql-code-block" data-language="plain">  
</div></div>## 6 - Créer le PaymentController

```php
// src/Controller/PaymentController.php
<?php


namespace App\Controller;


use App\Entity\Plan;
use App\Repository\PlanRepository;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/payment')]
class PaymentController extends AbstractController
{
  /**
   * Crée une Checkout Session Stripe et redirige l'utilisateur vers Stripe.
   */
  #[IsGranted('ROLE_USER')]
  #[Route('/checkout/{id}', name: 'app_payment_checkout')]
  public function checkout(
    Plan $plan,
    StripeService $stripeService,
  ): Response {
    // Le plan FREE ne nécessite pas de paiement
    if ($plan->getStripePriceId() === null) {
      $this->addFlash('info', 'Ce plan est gratuit, aucun paiement requis.');
      return $this->redirectToRoute('app_index');
    }


    $successUrl = $this->generateUrl(
      'app_payment_success',
      [],
      UrlGeneratorInterface::ABSOLUTE_URL
    );


    $cancelUrl = $this->generateUrl(
      'app_payment_cancel',
      [],
      UrlGeneratorInterface::ABSOLUTE_URL
    );


    $checkoutUrl = $stripeService->createCheckoutSession(
      $this->getUser(),
      $plan,
      $successUrl,
      $cancelUrl,
    );


    return $this->redirect($checkoutUrl);
  }


  /**
   * Page affichée après un paiement réussi.
   * NE PAS mettre à jour le plan ici — c'est le rôle du webhook.
   */
  #[Route('/success', name: 'app_payment_success')]
  public function success(): Response
  {
    return $this->render('payment/success.html.twig');
  }


  /**
   * Page affichée si l'utilisateur annule le paiement.
   */
  #[Route('/cancel', name: 'app_payment_cancel')]
  public function cancel(): Response
  {
    return $this->render('payment/cancel.html.twig');
  }
}

```

<div class="ql-code-block-container" id="bkmrk--1" spellcheck="false"><div class="ql-code-block" data-language="plain">  
</div></div>## 7 - Les Webhooks : mettre à jour le plan après paiement

### Pourquoi une route séparée sans CSRF ?

Le webhook est un appel **HTTP POST** envoyé directement par les serveurs Stripe, sans navigateur. Il faut :

1. Lire le **corps brut** de la requête (pas le formulaire parsé)
2. Vérifier la **signature Stripe** (sécurité cryptographique)
3. **Exclure** cette route de la protection CSRF de Symfony

### Créer le WebhookController

```php
// src/Controller/WebhookController.php
<?php


namespace App\Controller;


use App\Repository\PlanRepository;
use App\Repository\UserRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class WebhookController extends AbstractController
{
  #[Route('/payment/webhook', name: 'app_payment_webhook', methods: ['POST'])]
  public function webhook(
    Request $request,
    StripeService $stripeService,
    UserRepository $userRepository,
    PlanRepository $planRepository,
    EntityManagerInterface $em,
  ): Response {
    $payload = $request->getContent();
    $sigHeader = $request->headers->get('Stripe-Signature');


    // 1. Vérifier la signature Stripe
    try {
      $event = $stripeService->constructWebhookEvent($payload, $sigHeader);
    } catch (SignatureVerificationException $e) {
      // Signature invalide : rejeter la requête
      return new Response('Signature invalide', Response::HTTP_BAD_REQUEST);
    }


    // 2. Traiter l'événement
    switch ($event->type) {


      case 'checkout.session.completed':
        $session = $event->data->object;


        // Récupérer l'utilisateur et le plan depuis les métadonnées
        $userId = $session->metadata->user_id ?? null;
        $planId = $session->metadata->plan_id ?? null;


        if (!$userId || !$planId) {
          return new Response('Métadonnées manquantes', Response::HTTP_BAD_REQUEST);
        }


        $user = $userRepository->find($userId);
        $plan = $planRepository->find($planId);


        if (!$user || !$plan) {
          return new Response('Utilisateur ou plan introuvable', Response::HTTP_NOT_FOUND);
        }


        // Mettre à jour le plan de l'utilisateur
        $user->setPlan($plan);
        $em->flush();


        break;


      case 'customer.subscription.deleted':
        // L'abonnement a été annulé (depuis le Dashboard Stripe ou le portail client)
        // On repasse l'utilisateur sur le plan FREE
        $subscription = $event->data->object;
        $userId = $subscription->metadata->user_id ?? null;


        if ($userId) {
          $user = $userRepository->find($userId);
          $freePlan = $planRepository->findOneBy(['name' => 'FREE']);


          if ($user && $freePlan) {
            $user->setPlan($freePlan);
            $em->flush();
          }
        }
        break;


      // Ignorer les autres événements
      default:
        break;
    }


    // Toujours répondre 200 à Stripe, même si on n'a rien fait
    return new Response('OK', Response::HTTP_OK);
  }
}

```

<div class="ql-code-block-container" id="bkmrk-exclure-la-route-web" spellcheck="false"><div class="ql-code-block" data-language="plain"><span style="color: rgb(34, 34, 34); font-family: -apple-system, 'system-ui', 'Segoe UI', Oxygen, Ubuntu, Roboto, Cantarell, 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif; font-size: 2.333em; font-weight: 400;">Exclure la route webhook du CSRF</span></div></div>Symfony n'applique pas de CSRF aux routes non-formulaires par défaut, mais il faut s'assurer que le firewall ne bloque pas les requêtes sans session. Ajoutez la route dans `security.yaml` :

```yaml
# config/packages/security.yaml
firewalls:
main:
# ... config existante ...
# Exclure le webhook de toute vérification de session
```

<div class="ql-code-block-container" id="bkmrk--2" spellcheck="false"><div class="ql-code-block" data-language="plain">  
</div></div>La route `/payment/webhook` est publique par conception (Stripe n'est pas authentifié). La sécurité repose sur la **vérification de signature cryptographique** dans le code (étape 1 du WebhookController).

## 8 - Adapter le template de la page d'accueil

### Modifier les boutons du pricing

### Créer les templates de résultat

#### Page de succès

#### Page d'annulation

<div class="ql-code-block-container" id="bkmrk--3" spellcheck="false">  
</div>### Cartes de test utiles

<table id="bkmrk-paiement-r%C3%A9ussi-4242"><tbody><tr><td data-row="2">Paiement réussi</td><td data-row="2">`4242 4242 4242 4242`</td></tr><tr><td data-row="3">Paiement refusé</td><td data-row="3">`4000 0000 0000 0002`</td></tr><tr><td data-row="4">Authentification 3DS requise</td><td data-row="4">`4000 0025 0000 3155`</td></tr><tr><td data-row="5">Fonds insuffisants</td><td data-row="5">`4000 0000 0000 9995`</td></tr></tbody></table>

<div class="ql-code-block-container" id="bkmrk--4" spellcheck="false"><div class="ql-code-block" data-language="plain">  
</div></div><div class="ql-code-block-container" id="bkmrk--5" spellcheck="false"><div class="ql-code-block" data-language="plain">  
</div></div>> Cet exercice nécessite d'ajouter `stripeCustomerId` à l'entité `User` et de le persister lors de la création de la Checkout Session (en ajoutant `'customer'` aux paramètres).

<div class="ql-code-block-container" id="bkmrk--6" spellcheck="false"><div class="ql-code-block" data-language="plain">  
</div></div>