{{--
    The buyer's invoice.

    Deliberately plain: this has to render under the dompdf driver, which understands a subset of CSS
    with no flexbox and no grid, so the layout is tables and inline styles rather than the site's
    Tailwind. That keeps it working on an install with no Chromium, and it still renders correctly if
    an owner switches LARAVEL_PDF_DRIVER to browsershot. Every money figure arrives already formatted
    in the order's own currency — the template must never do arithmetic on minor units.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Invoice :number', ['number' => $number]) }}</title>
    <style>
        @page { margin: 32px 36px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 20px; margin: 0 0 2px; }
        .muted { color: #6b7280; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        .head td { vertical-align: top; padding-bottom: 18px; }
        .meta td { padding: 2px 0; }
        .items { margin-top: 8px; }
        .items th {
            text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;
            color: #6b7280; border-bottom: 1px solid #d1d5db; padding: 6px 4px;
        }
        .items td { padding: 7px 4px; border-bottom: 1px solid #f3f4f6; }
        .totals { margin-top: 14px; width: 46%; }
        .totals td { padding: 3px 4px; }
        .totals .grand td { border-top: 1px solid #d1d5db; font-weight: bold; font-size: 12px; padding-top: 7px; }
        .footer { margin-top: 26px; font-size: 10px; color: #6b7280; }
        .badge { font-size: 10px; padding: 2px 6px; border: 1px solid #d1d5db; border-radius: 8px; }
    </style>
</head>
<body>

<table class="head">
    <tr>
        <td>
            <h1>{{ $storeName }}</h1>
            <div class="muted">{{ $siteName }}</div>
        </td>
        <td class="right">
            <h1>{{ __('Invoice') }}</h1>
            <div class="muted">#{{ $number }}</div>
            <div style="margin-top: 6px;"><span class="badge">{{ $status }}</span></div>
        </td>
    </tr>
</table>

<table class="meta">
    <tr>
        <td style="width: 50%;">
            <div class="muted">{{ __('Billed To') }}</div>
            <div>{{ $buyer }}</div>
            @if ($buyerEmail)
                <div class="muted">{{ $buyerEmail }}</div>
            @endif
            @if ($playerUsername)
                <div class="muted">{{ __('Delivered to :player in game', ['player' => $playerUsername]) }}</div>
            @endif
        </td>
        <td style="width: 50%;" class="right">
            <div class="muted">{{ __('Issued') }}</div>
            <div>{{ $issuedAt }}</div>
            @if ($gateway)
                <div class="muted" style="margin-top: 6px;">{{ __('Paid by :gateway', ['gateway' => ucfirst($gateway)]) }}</div>
            @endif
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>{{ __('Item') }}</th>
            <th class="right" style="width: 70px;">{{ __('Qty') }}</th>
            <th class="right" style="width: 100px;">{{ __('Unit') }}</th>
            <th class="right" style="width: 110px;">{{ __('Amount') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td class="right">{{ $item['quantity'] }}</td>
                <td class="right">{{ $item['unit'] }}</td>
                <td class="right">{{ $item['total'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Right-aligned by pushing an empty cell, since dompdf has no margin-left: auto on tables. --}}
<table>
    <tr>
        <td style="width: 54%;"></td>
        <td>
            <table class="totals">
                <tr>
                    <td class="muted">{{ __('Subtotal') }}</td>
                    <td class="right">{{ $money['subtotal'] }}</td>
                </tr>
                @if ($money['sale_discount'])
                    <tr>
                        <td class="muted">{{ __('Sale discount') }}</td>
                        <td class="right">−{{ $money['sale_discount'] }}</td>
                    </tr>
                @endif
                @if ($money['coupon_discount'])
                    <tr>
                        <td class="muted">
                            {{ __('Coupon') }}@if ($couponCode) <span class="muted">({{ $couponCode }})</span>@endif
                        </td>
                        <td class="right">−{{ $money['coupon_discount'] }}</td>
                    </tr>
                @endif
                @if ($money['tax_amount'])
                    <tr>
                        <td class="muted">{{ $money['tax_label'] }}</td>
                        <td class="right">{{ $money['tax_amount'] }}</td>
                    </tr>
                @endif
                <tr class="grand">
                    <td>{{ __('Total') }}</td>
                    <td class="right">{{ $money['total'] }}</td>
                </tr>
                @if ($money['gift_card_amount'])
                    <tr>
                        <td class="muted">{{ __('Gift card') }}</td>
                        <td class="right">−{{ $money['gift_card_amount'] }}</td>
                    </tr>
                    <tr>
                        <td class="muted">{{ __('Paid') }}</td>
                        <td class="right">{{ $money['amount_due'] }}</td>
                    </tr>
                @endif
                @if ($money['refunded'])
                    <tr>
                        <td class="muted">{{ __('Refunded') }}</td>
                        <td class="right">−{{ $money['refunded'] }}</td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<div class="footer">
    {{ __('Order :number · :currency · Thank you for supporting the server.', [
        'number' => $number,
        'currency' => $order->currency,
    ]) }}
</div>

</body>
</html>
