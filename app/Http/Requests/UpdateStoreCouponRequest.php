<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;

class UpdateStoreCouponRequest extends CreateStoreCouponRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('storeCoupon'));
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            'code' => array_merge($this->codeRules(), [
                Rule::unique('store_coupons', 'code')->ignore($this->route('storeCoupon')->id),
            ]),
        ]);
    }
}
