<?php

namespace App\Http\Requests\CashierAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;

class Cashier extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|regex:/^[A-Za-z .]+$/|max:100',
            'phone_number' => 'required|numeric|digits_between:10,15',
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg'
            // 'email' => ['required','string','email','max:191',
            //     Rule::unique('users')->where(function ($query) {
            //         $query->where('store_id', request()->store_id);
            //         $query->whereNotIn('id', [Auth::user()->id]);
            //         $query->where('email', request()->email);
            //     })
            // ],
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('store-admin.name'),
            'email' => __('store-admin.email_address'),
            'phone_number' => __('store-admin.phone_number'),
        ];
    }
}
