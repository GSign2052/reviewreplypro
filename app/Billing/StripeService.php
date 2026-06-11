<?php

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeService
{
    public function __construct(private BillingRepository $repo)
    {
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    public function createCheckoutSession(int $orgId, string $email, string $stripePriceId, string $successUrl, string $cancelUrl): string
    {
        $customer = $this->repo->getStripeCustomer($orgId);

        if ($customer) {
            $customerId = $customer['stripe_customer_id'];
        } else {
            $stripeCustomer = Customer::create(['email' => $email]);
            $customerId = $stripeCustomer->id;
            $this->repo->upsertStripeCustomer($orgId, $customerId, $email);
        }

        $session = CheckoutSession::create([
            'customer'            => $customerId,
            'payment_method_types' => ['card'],
            'mode'                => 'subscription',
            'line_items'          => [['price' => $stripePriceId, 'quantity' => 1]],
            'success_url'         => $successUrl,
            'cancel_url'          => $cancelUrl,
            'metadata'            => ['org_id' => (string) $orgId],
        ]);

        return $session->url;
    }

    public function createPortalSession(int $orgId, string $returnUrl): string
    {
        $customer = $this->repo->getStripeCustomer($orgId);
        if (!$customer) {
            throw new RuntimeException('Kein Stripe-Kunde für diese Organisation gefunden.');
        }

        $session = PortalSession::create([
            'customer'   => $customer['stripe_customer_id'],
            'return_url' => $returnUrl,
        ]);

        return $session->url;
    }

    public function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        return Webhook::constructEvent($payload, $sigHeader, env('STRIPE_WEBHOOK_SECRET'));
    }

    public function handleWebhookEvent(\Stripe\Event $event, int|null &$resolvedOrgId): void
    {
        $obj = $event->data->object;

        switch ($event->type) {
            case 'checkout.session.completed':
                $resolvedOrgId = (int) ($obj->metadata['org_id'] ?? 0);
                break;

            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                // org_id über stripe_customer_id ermitteln
                $stripeCustomerId = $obj->customer;
                $this->repo->logBillingEvent($event->id, $event->type, null, ['stripe_customer' => $stripeCustomerId]);
                break;
        }
    }
}
