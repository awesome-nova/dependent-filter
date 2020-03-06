<?php

use Illuminate\Support\Facades\Route;

Route::get('/{resource}/filters/options', 'FilterController@options')->name('dependent-filter.resource.options');
Route::get('/{resource}/lens/{lens}/filters/options', 'LensFilterController@options')->name('dependent-filter.lens.options');
