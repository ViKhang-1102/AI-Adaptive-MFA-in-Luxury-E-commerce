<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as BaseTrimStrings;

class TrimStrings extends BaseTrimStrings
{
    protected $except = [
        'password',
        'password_confirmation',
    ];
}
