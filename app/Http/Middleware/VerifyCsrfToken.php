<?php

namespace App\Http\Middleware;

use illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        // Daftarkan endpoint webhook di sini
        'api/public/webhook/midtrans',
    ];
}
