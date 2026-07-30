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

        $this->migrator->add('store.tax_mode', 'none');
        $this->migrator->add('store.tax_rate_bp', 0);
        $this->migrator->add('store.tax_label', 'Tax');

        $this->migrator->add('store.enable_guest_checkout', true);
        $this->migrator->add('store.require_email_on_guest_checkout', true);
        $this->migrator->add('store.mojang_username_verification', true);

        $this->migrator->add('store.terms_text', null);

        $this->migrator->add('store.enabled_gateways', ['manual']);
        $this->migrator->addEncrypted('store.gateway_credentials', []);

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

        $this->migrator->deleteIfExists('store.tax_mode');
        $this->migrator->deleteIfExists('store.tax_rate_bp');
        $this->migrator->deleteIfExists('store.tax_label');

        $this->migrator->deleteIfExists('store.enable_guest_checkout');
        $this->migrator->deleteIfExists('store.require_email_on_guest_checkout');
        $this->migrator->deleteIfExists('store.mojang_username_verification');

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
