<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class GetLapanganSlotsDailyRequest extends FormRequest
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
        // maksimal boleh booking 1 bulan kedepan
        $maksimalTanggalBooking = now()->addDays(30)->format('Y-m-d');

        return [
            'date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:' . $maksimalTanggalBooking,
            ],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'date.required' => 'Tanggal wajib dipilih.',
            'date.after_or_equal' => 'Tidak bisa melihat jadwal untuk tanggal yang sudah lewat.',
            'date.before_or_equal' => 'Pemesanan maksimal hanya bisa dilakukan untuk 30 hari ke depan.',
        ];
    }
}
