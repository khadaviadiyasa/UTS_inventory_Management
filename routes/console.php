<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('project:about', function () {
    $this->info('UTS Inventory & Sales Management System');
})->purpose('Display project information');