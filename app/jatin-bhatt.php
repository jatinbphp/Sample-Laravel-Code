<?php
/**
 * Have added validation for $cookies must be integer
 * Log::info added double colon instead of single
 * Added condition that wallet must be greater or equal to $cookie
 * update wallet was not proper, have changed that.
 *
 */
Route::get('buy/{cookies}', function ($cookies) {
    $user = Auth::user();
    $wallet = $user->wallet;
    if ($wallet >= $cookies) {
        $user->wallet = ($wallet - $cookies);
        $user->save();

        Log::info('User ' . $user->email . ' have bought ' . $cookies . ' cookies at ' . date("d-m-Y H:i:s"));
        return 'Success, you have bought ' . $cookies . ' cookies!';
    } else {
        return 'Error, you enough all at balance';
    }
})->where('cookies', '[0-9]+');
