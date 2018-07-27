<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    /*
     *  EVENT ROUTES
     */
Route::middleware(['cors'])->group(function () {
// Get all events
    Route::get('events', 'EventController@index')
        ->name('events.index');

// Get event
    Route::get('events/{event}', 'EventController@show')
        ->name('events.show');

// Create event
    Route::post('events', 'EventController@store')
        ->middleware(['jwt.auth', 'permission:event-create']);

// Edit event
    Route::patch('events/{event}', 'EventController@update')
        ->middleware(['jwt.auth', 'permission:event-edit']);

// Delete event
    Route::delete('events/{event}', 'EventController@destroy')
        ->middleware(['jwt.auth', 'permission:event-delete']);

// Get the subscribed users for an event
    Route::middleware(['jwt.auth', 'permission:event-get-subscribers'])->group(function () {
        Route::get('events/{event}/users', 'EventController@subscribers')
            ->name('events.users');

        Route::get('events/{event}/relationships/users', 'EventController@subscribers')
            ->name('events.relationships.users');
    });

// Event subscription
    Route::middleware(['jwt.auth', 'permission:event-subscribe'])->group(function () {

        // Subscribe to Event
        Route::post('events/{event}/users', 'EventController@subscribe');

        // Unsubscribe from event
        Route::delete('events/{event}/users', 'EventController@unsubscribe');
    });

    /*
     * USER ROUTES
     */
// Get all users
    Route::get('users', 'UserController@index')
        ->name('users.index')
        ->middleware('jwt.auth', 'permission:user-list');

// Get user
    Route::get('users/{user}', 'UserController@show')
        ->name('users.show')
        ->middleware('jwt.auth', 'permission:user-detail');

// Register user
    Route::post('users/register', 'UserController@register');


// Get a Users Event subscriptions
    Route::middleware(['jwt.auth', 'permission:user-get-subscribed'])->group(function () {
        Route::get(
            'users/{user}/events', 'UserController@subscribed_to'
        )->name('users.events');

        Route::get(
            'users/{user}/relationships/events', 'UserController@subscribed_to'
        )->name('users.relationships.events');
    });

    /*
     *  AUTHENTICATION ROUTES
     */
// Login
    Route::post('auth/login', 'AuthController@login');

// Logout
    Route::post('auth/logout', 'AuthController@logout')
        ->middleware('jwt.auth');

});
