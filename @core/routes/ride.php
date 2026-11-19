<?php

use Illuminate\Support\Facades\Route;

/*
 * The passenger side of ride booking.
 *
 * Open to any signed-in, verified user: being a buyer or a seller on the
 * marketplace has nothing to do with needing a bike, so this group checks
 * only that the feature is on.
 */
Route::group([
    'prefix'     => 'ride',
    'middleware' => ['auth', 'inactiveuser', 'userEmailVerify', 'setlang', 'globalVariable', 'RideEnabled'],
], function () {

    Route::get('/', 'Frontend\RideController@index')->name('ride.index');
    Route::get('/history', 'Frontend\RideController@history')->name('ride.history');

    Route::post('/quote', 'Frontend\RideController@quote')->name('ride.quote');
    Route::post('/book', 'Frontend\RideController@book')->name('ride.book');

    Route::get('/trip/{id}', 'Frontend\RideController@trip')->name('ride.trip');
    Route::get('/trip/{id}/status', 'Frontend\RideController@status')->name('ride.trip.status');
    Route::post('/trip/{id}/cancel', 'Frontend\RideController@cancel')->name('ride.trip.cancel');
    Route::post('/trip/{id}/rate', 'Frontend\RideController@rate')->name('ride.trip.rate');

});
