<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_penyewa' => [
                'required',
                'string',
            ],
            'email_penyewa' => [
                'required',
                'email',
            ],
            'nomor_penyewa' => [
                'required',
                'string'
            ],
            'cart_items' => [
                'required',
                'array',
                'min:1'
            ]
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'nama_penyewa.required' => 'Nama penyewa wajib diisi',
            'nama_penyewa.string' => 'Nama penyewa wajib berupa karakter',
            'email_penyewa.required' => 'Email penyewa wajib diisi',
            'email_penyewa.email' => 'Email penyewa wajib berupa email',
            'nomor_penyewa.required' => 'Nomor penyewa wajib diisi',
            'nomor_penyewa.string' => 'Nomor penyewa wajib berupa string',
            'cart_items.required' => 'Keranjang wajib terdapat jadwal booking',
            'cart_items.min' => 'Keranjang minimal berisi 1 jadwal booking',
        ];
    }
}
