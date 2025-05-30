<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


\Illuminate\Support\Facades\Schedule::call(function () {
    \App\Models\MainCategoriesModel::updateMainCategories();
});

Artisan::command('icons', function () {
    (new \App\Models\ChildCategoriesModel())->changeIcons();
});
