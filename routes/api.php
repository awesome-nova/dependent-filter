<?php

use Illuminate\Support\Facades\Route;

Route::get('/{resource}/filters/options', 'FilterController@options');
Route::get('/{resource}/lens/{lens}/filters/options', 'LensFilterController@options');