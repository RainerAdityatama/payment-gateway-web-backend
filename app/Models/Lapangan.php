<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    protected $table = 'lapangan';

    protected $fillable = [
        'nama',
        'harga_per_jam',
        'tipe_lapangan',
        'status',
        'slug'
    ];

    public function detailTransaksis()
    {
        return $this->hasMany(DetailTransaksi::class, 'lapangan_id');
    }
}
