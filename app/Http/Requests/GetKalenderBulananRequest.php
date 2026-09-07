<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Override;

class GetKalenderBulananRequest extends FormRequest
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
        // Ambil batas maksimal bulan (1 bulan dari sekarang)
        $maxDate = now()->addMonth();

        return [
            'year' => [
                'required',
                'integer',
                'min:' . now()->year,
                // Kita izinkan tahun depan HANYA JIKA bulan ini adalah Desember.
                // Jika sekarang bulan Agustus, $maxDate->year tetap tahun ini.
                'max:' . $maxDate->year,
            ],
            'month' => [
                'required',
                'integer',
                'min:1',
                'max:12',
                function (string $attribute, mixed $value, Closure $fail) {
                    $year = $this->input('year');

                    if (!$year) return;

                    // ambil bulannya saja pake start of month
                    $requestMonth = Carbon::createFromDate($year, $value, 1)->startOfMonth();

                    // buat range batas waktu
                    $currentMonth = now()->startOfMonth();

                    // pake less than dan greater than
                    if ($requestMonth->lt($currentMonth) || $requestMonth->gt($currentMonth)) {
                        $fail('Slot booking untuk bulan tersebut tidak tersedia');
                    }
                }
            ]
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'year.required' => 'Tahun wajib dipilih',
            'year.min' => 'Tidak bisa melihat tahun yang sudah lewat',
            'year.max' => 'Slot booking belum tersedia',
            'month.required' => 'Bulan wajib dipilih',
        ];
    }
}
