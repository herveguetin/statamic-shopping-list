<?php

use App\Services\Ingredients;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::statamic('example', 'example-view', [
//    'title' => 'Example'
// ]);

Route::get('/ingredients/list/{q}', function (string $q) {
    return response()->json(Ingredients::search($q));
});

Route::get('/ingredients/show/{id}', function (string $id) {
    return response()->json(Ingredients::get($id));
});
