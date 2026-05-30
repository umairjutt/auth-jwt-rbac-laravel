<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['service' => 'Auth Starter', 'docs' => '/api/auth/me']));
