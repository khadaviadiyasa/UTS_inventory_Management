<?php

namespace App\Services;

use Illuminate\Support\Str;

class NumberGenerator
{
    public function purchase(): string
    {
        return 'PCH-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
    }

    public function sale(): string
    {
        return 'SAL-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
    }
}