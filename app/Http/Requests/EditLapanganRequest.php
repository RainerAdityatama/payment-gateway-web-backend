<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Override;

class EditLapanganRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[Override]
    protected function prepareForValidation()
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
        $lapangan = $this->route('lapangan');

        return [
            'nama' => [
                'required',
                'string',
                'min:5',
                'max:50',
                Rule::unique('lapangan', 'nama')->ignore($lapangan->id, 'id'),
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
            'status' => [
                'required',
                'string',
                'in:aktif,tidak_aktif'
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
            'status.required' => 'Status wajib diisi',
            'status.string' => 'Status harus berupa karakter',
            'status.in' => 'Status yang anda pilih tidak ditemukan',
        ];
    }
}
