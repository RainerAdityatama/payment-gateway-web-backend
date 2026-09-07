<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Override;

class TambahLapanganRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Override]
    public function prepareForValidation()
    {
        $dataSlug = $this->nama;

        $this->merge([
            'slug' => Str::slug($dataSlug)
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => [
                'required',
                'string',
                'min:5',
                'max:50',
                Rule::unique('lapangan', 'nama'),
            ],
            'harga_per_jam' => [
                'required',
                'integer',
                'min:0'
            ],
            'tipe_lapangan' => [
                'required',
                'string',
                'in:futsal,badminton'
            ],
            'slug' => [
                'required'
            ]
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'nama.required' => 'Nama lapangan wajib diisi',
            'nama.string' => 'Nama harus berupa karakter',
            'nama.min' => 'Nama minimal berisi 5 karakter',
            'nama.max' => 'Nama maksimal berisi 50 karakter',
            'nama.unique' => 'Nama sudah digunakan, silahkan ganti nama yang lain!',
            'harga_per_jam.required' => 'Harga wajib diisi',
            'harga_per_jam.integer' => 'Harga harus berupa angka',
            'harga_per_jam.min' => 'Harga tidak boleh minus',
            'tipe_lapangan.required' => 'Tipe Lapangan wajib diisi',
            'tipe_lapangan.string' => 'Tipe Lapangan harus berupa karakter',
            'tipe_lapangan.in' => 'Tipe Lapangan yang anda pilih tidak ditemukan',
        ];
    }
}
