<?php

use Illuminate\Support\Facades\Route;

/*
 * The rider area.
 *
 * Riding is opt-in rather than a user type, so this group does not use
 * BuyerCheck: any signed-in, verified user may apply. RiderCheck decides who
 * gets past the application pages, and turns the whole area off when ride
 * booking is disabled in settings.
 */
Route::group([
    'prefix'     => 'rider',
    'middleware' => ['auth', 'inactiveuser', 'userEmailVerify', 'setlang', 'globalVariable', 'RiderCheck'],
], function () {

    // Open to any signed-in user — this is how somebody becomes a rider.
    Route::match(['get', 'post'], '/apply', 'Frontend\RiderController@apply')->name('rider.apply');
    Route::get('/status', 'Frontend\RiderController@status')->name('rider.status');

    // Approved riders only, enforced by RiderCheck.
    Route::get('/dashboard', 'Frontend\RiderController@dashboard')->name('rider.dashboard');
    Route::post('/online', 'Frontend\RiderController@toggleOnline')->name('rider.online');
    Route::post('/ping', 'Frontend\RiderController@ping')->name('rider.ping');

});
