<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('', 'threads');

Auth::routes();

Route::get('home', 'HomeController@index')->name('home');

Route::get('threads', 'ThreadsController@index')->name('threads');
Route::get('threads/search', 'SearchController@show')->name('search.show');
Route::get('threads/{channel}/{thread}', 'ThreadsController@show')->name('threads.show');
Route::patch('threads/{channel}/{thread}', 'ThreadsController@update')->name('threads.update');
Route::delete('threads/{channel}/{thread}', 'ThreadsController@destroy')->name('threads.destroy');
Route::post('threads', 'ThreadsController@store')->name('threads.store');
Route::get('threads/{channel}', 'ThreadsController@index')->name('channels');

Route::post('locked-threads/{thread}', 'LockedThreadsController@store')->name('locked-threads.store');
Route::delete('locked-threads/{thread}', 'LockedThreadsController@destroy')->name('locked-threads.destroy');

Route::post('pinned-threads/{thread}', 'PinnedThreadsController@store')->name('pinned-threads.store');
Route::delete('pinned-threads/{thread}', 'PinnedThreadsController@destroy')->name('pinned-threads.destroy');

Route::get('threads/{channel}/{thread}/replies', 'RepliesController@index')->name('replies');
Route::post('threads/{channel}/{thread}/replies', 'RepliesController@store')->name('replies.store');
Route::patch('replies/{reply}', 'RepliesController@update')->name('replies.update');
Route::delete('replies/{reply}', 'RepliesController@destroy')->name('replies.destroy');

Route::post('replies/{reply}/best', 'BestRepliesController@store')->name('best-replies.store');

Route::post('threads/{channel}/{thread}/subscriptions', 'ThreadSubscriptionsController@store')->name('threads.store');
Route::delete('threads/{channel}/{thread}/subscriptions', 'ThreadSubscriptionsController@destroy')->name('threads.destroy');

Route::post('replies/{reply}/favorites', 'FavoritesController@store')->name('replies.favorite');
Route::delete('replies/{reply}/favorites', 'FavoritesController@destroy')->name('replies.unfavorite');

Route::get('profiles/{user}', 'ProfilesController@show')->name('profile');
Route::get('profiles/{user}/activity', 'ProfilesController@index')->name('activity');
Route::get('profiles/{user}/notifications', 'UserNotificationsController@index')->name('user-notifications');
Route::delete('profiles/{user}/notifications/{notification}', 'UserNotificationsController@destroy')->name('user-notification.destroy');

Route::get('register/confirm', 'Auth\RegisterConfirmationController@index')->name('register.confirm');

Route::get('api/users', 'Api\UsersController@index')->name('api.users');
Route::post('api/users/{user}/avatar', 'Api\UserAvatarController@store')->name('avatar');
Route::get('api/channels', 'Api\ChannelsController@index');

Route::get('api/leaderboard', 'Api\LeaderboardController@index')->name('api.leaderboard.index');
Route::get('leaderboard', 'LeaderboardController@index')->name('leaderboard.index');

Route::group([
    'prefix' => 'admin',
    //'middleware' => 'admin',
    'namespace' => 'Admin'
], function () {
    Route::get('', 'DashboardController@index')->name('admin.dashboard.index');
    Route::post('channels', 'ChannelsController@store')->name('admin.channels.store');
    Route::get('channels', 'ChannelsController@index')->name('admin.channels.index');
    Route::get('channels/create', 'ChannelsController@create')->name('admin.channels.create');
    Route::get('channels/{channel}/edit', 'ChannelsController@edit')->name('admin.channels.edit');
    Route::patch('channels/{channel}', 'ChannelsController@update')->name('admin.channels.update');
});
