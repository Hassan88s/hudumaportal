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

    // Trips. The dashboard polls requests; everything else is a single step
    // the service checks against where the trip actually is.
    Route::get('/requests', 'Frontend\RiderController@requests')->name('rider.requests');
    Route::post('/requests/{id}/accept', 'Frontend\RiderController@accept')->name('rider.trip.accept');
    Route::get('/trip', 'Frontend\RiderController@trip')->name('rider.trip');
    Route::get('/trip/{id}/done', 'Frontend\RiderController@tripDone')->name('rider.trip.done');
    Route::post('/trip/{id}/rate-passenger', 'Frontend\RiderController@ratePassenger')->name('rider.trip.rate.passenger');
    // Declared last: {action} would otherwise swallow 'rate-passenger'.
    Route::post('/trip/{id}/{action}', 'Frontend\RiderController@tripAction')->name('rider.trip.action');
    Route::get('/earnings', 'Frontend\RiderController@earnings')->name('rider.earnings');
    Route::get('/ratings', 'Frontend\RiderController@ratings')->name('rider.ratings');

});
