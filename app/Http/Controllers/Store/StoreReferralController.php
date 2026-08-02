<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreReferral;
use App\Models\StoreReferralPayout;
use App\Services\StoreCurrencyService;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A referrer's own view of their code.
 *
 * Nothing to do with the admin controller of the same name in Admin\Store: that one manages every
 * code on the site, this one shows a member the one that names them.
 */
class StoreReferralController extends Controller
{
    public function __construct(private StoreCurrencyService $currencies) {}

    public function show(Request $request): Response
    {
        $referral = StoreReferral::where('user_id', $request->user()->id)->first();

        // Not 403: a member with no code should not be told one exists to be refused.
        abort_if($referral === null, 404);

        $this->authorize('viewDashboard', $referral);

        $base = $this->currencies->base();

        return Inertia::render('Store/MyStoreReferral', [
            'referral' => [
                'code' => $referral->code,
                'referrer_name' => $referral->referrer_name,
                'share_bp' => (int) $referral->share_bp,
                'is_enabled' => (bool) $referral->is_enabled,
                'visit_count' => (int) $referral->visit_count,
                'orders_count' => $referral->orders()->whereIn('status', StoreReferral::earningStatuses())->count(),
                'earned_formatted' => $this->currencies->format($referral->earnedBase(), $base),
                'paid_out_formatted' => $this->currencies->format($referral->paidOut(), $base),
                'owed' => $referral->owed(),
                'owed_formatted' => $this->currencies->format($referral->owed(), $base),
            ],
            // Summarised, never a row per referred order: who bought what is not a referrer's
            // business, and they are not staff.
            'payouts' => $referral->payouts()
                ->latest('paid_at')
                ->paginate(15)
                ->through(fn (StoreReferralPayout $payout) => [
                    'id' => $payout->id,
                    'paid_at' => $payout->paid_at,
                    'reference' => $payout->reference,
                    'note' => $payout->note,
                    'amount_formatted' => $this->currencies->format(
                        (int) $payout->amount,
                        $this->currencies->find($payout->currency) ?? $base
                    ),
                ]),
            'trackingUrl' => $this->trackingBaseUrl().'?ref='.$referral->code,
        ]);
    }

    private function trackingBaseUrl(): string
    {
        return app(GeneralSettings::class)->homepage_route === 'store'
            ? url('/')
            : route('store.index');
    }
}
