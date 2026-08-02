<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('store.store_name', 'Store');
        $this->migrator->add('store.store_description', null);

        $this->migrator->add('store.base_currency', 'USD');
        $this->migrator->add('store.currency_rate_source', 'manual');

        // Tax is rows in store_taxes, one rule per country - see StoreTaxService.

        // On by default. Most Minecraft buyers have no website account, so shipping with this off
        // makes the entire guest path dead on a fresh install — and it contradicts
        // require_email_on_guest_checkout below, which only means anything once guests can buy.
        $this->migrator->add('store.enable_guest_checkout', true);
        $this->migrator->add('store.require_email_on_guest_checkout', true);
        $this->migrator->add('store.mojang_username_verification', true);

        // Off by default: a Minecraft store has no goods to ship, so most owners never need one.
        // Owners who do — for invoicing or tax records — get the full set of fields at checkout,
        // for guests and signed-in buyers alike.
        $this->migrator->add('store.collect_billing_address', false);

        $this->migrator->add('store.terms_text', null);

        // Gateways are rows in store_payment_gateways, not settings keys — see
        // StorePaymentGatewaySeeder.

        $this->migrator->add('store.show_recent_purchases', true);
        $this->migrator->add('store.show_purchase_goal', false);
        // Minor units of the base currency. Zero means no goal, so the bar stays hidden until an
        // owner sets a real target.
        $this->migrator->add('store.purchase_goal_amount', 0);
        $this->migrator->add('store.show_top_donor', false);
        $this->migrator->add('store.hide_buyer_identity', false);
        $this->migrator->add('store.notify_staff_on_purchase', true);
        // Empty is the off switch: a webhook with nowhere to post announces nothing.
        $this->migrator->add('store.discord_purchase_webhook_url', null);
        $this->migrator->add('store.auto_ban_on_chargeback', false);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('store.store_name');
        $this->migrator->deleteIfExists('store.store_description');

        $this->migrator->deleteIfExists('store.base_currency');
        $this->migrator->deleteIfExists('store.currency_rate_source');

        $this->migrator->deleteIfExists('store.enable_guest_checkout');
        $this->migrator->deleteIfExists('store.require_email_on_guest_checkout');
        $this->migrator->deleteIfExists('store.mojang_username_verification');
        $this->migrator->deleteIfExists('store.collect_billing_address');

        $this->migrator->deleteIfExists('store.terms_text');

        $this->migrator->deleteIfExists('store.enabled_gateways');
        $this->migrator->deleteIfExists('store.gateway_credentials');

        $this->migrator->deleteIfExists('store.show_recent_purchases');
        $this->migrator->deleteIfExists('store.show_purchase_goal');
        $this->migrator->deleteIfExists('store.purchase_goal_amount');
        $this->migrator->deleteIfExists('store.show_top_donor');
        $this->migrator->deleteIfExists('store.hide_buyer_identity');
        $this->migrator->deleteIfExists('store.notify_staff_on_purchase');
        $this->migrator->deleteIfExists('store.discord_purchase_webhook_url');
        $this->migrator->deleteIfExists('store.auto_ban_on_chargeback');
    }
};
