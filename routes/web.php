<?php

use Illuminate\Support\Facades\Route;




// Catch-all route for Vue SPA, but exclude API routes
Route::view('/{any}', 'app')->where('any', '^(?!api).*$');
