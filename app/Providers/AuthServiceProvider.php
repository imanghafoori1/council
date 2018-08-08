<?php

namespace App\Providers;

use App\Exceptions\ThrottleException;
use App\Reply;
use App\Thread;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Imanghafoori\HeyMan\Facades\HeyMan;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //\App\Thread::class => \App\Policies\ThreadPolicy::class,
        //\App\Reply::class => \App\Policies\ReplyPolicy::class,
        \App\User::class => \App\Policies\UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->authenticateRoutes();
        $this->validateRequests();
        $this->authorizeAdminRoutes();
        $this->authorizeEloquentModels();
    }

    private function authenticateRoutes()
    {
        HeyMan::whenYouReachRoute([
            'avatar',

            'user-notifications',
            'user-notifications.destroy',

            'replies.favorite',
            'replies.unfavorite',

            'threads.update',
            'threads.destroy',
            'threads.store',

            'replies.store',
            'replies.update',
            'replies.destroy'
        ])->youShouldBeLoggedIn()->otherwise()->weRespondFrom('\App\Http\Responses\Authentication@handle');
    }

    private function validateRequests()
    {
        HeyMan::whenYouReachRoute('replies.store')->yourRequestShouldBeValid(['body' => 'required|spamfree']);
        HeyMan::whenYouReachRoute('threads.update')->yourRequestShouldBeValid([
            'title' => 'required',
            'body' => 'required',
        ]);

        HeyMan::whenYouReachRoute('admin.channels.store')->yourRequestShouldBeValid([
            'name' => 'required|unique:channels',
            'color' => 'required',
            'description' => 'required',
        ]);

        HeyMan::whenYouReachRoute('admin.channels.update')->yourRequestShouldBeValid(function () {
            return [
                'name' => ['required', Rule::unique('channels')->ignore(request()->route('channel'), 'slug')],
                'description' => 'required',
                'color' => 'required',
                'archived' => 'required|boolean',
            ];
        });

        HeyMan::whenYouCallAction('RepliesController@update')->yourRequestShouldBeValid(['name' => 'required|spamfree',]);
        HeyMan::whenYouReachRoute('avatar')->yourRequestShouldBeValid(['avatar' => ['required', 'image']]);

        HeyMan::whenYouSendPost('threads')->yourRequestShouldBeValid(function () {
            return [
                'title' => 'required|spamfree',
                'body' => 'required|spamfree',
                'channel_id' => [
                    'required',
                    Rule::exists('channels', 'id')->where(function ($query) {
                        $query->where('archived', false);
                    }),
                ],
                //'g-recaptcha-response' => ['required', $recaptcha]
            ];
        });
    }

    private function authorizeAdminRoutes()
    {
        Gate::define('isAdmin', function ($user) {
            return $user->isAdmin();
        });

        HeyMan::whenYouReachRoute([
            'locked-threads.store',
            'locked-threads.destroy',
            'pinned-threads.store',
            'pinned-threads.destroy',
        ])->thisGateShouldAllow('isAdmin')->otherwise()->abort(403, 'You do not have permission to perform this action.');

        HeyMan::whenYouReachRoute('admin.*')->thisGateShouldAllow('isAdmin')->otherwise()->weDenyAccess();
    }

    private function authorizeEloquentModels()
    {
        Gate::define('createReply', function ($user) {
            if (! $lastReply = $user->fresh()->lastReply) {
                return true;
            }
            return ! $lastReply->wasJustPublished();
        });

        $hasConfirmedEmail = function () {
            return auth()->user()->confirmed or auth()->user()->isAdmin();
        };

        HeyMan::whenYouCreate(Thread::class)->thisClosureShouldAllow($hasConfirmedEmail)->otherwise()->redirect()->to('/threads')->with('flash', 'You must first confirm your email address.');

        HeyMan::whenYouReachRoute('replies.store')->thisGateShouldAllow('createReply')->otherwise()->weThrowNew(ThrottleException::class, 'You are replying too frequently. Please take a break.');

        Gate::define('ownModel', function ($user, $model) {
            return $user->id == $model->user_id or $user->isAdmin();
        });
        HeyMan::whenYouUpdate([Thread::class, Reply::class])->thisGateShouldAllow('ownModel')->otherwise()->weDenyAccess();
        HeyMan::whenYouDelete([Thread::class, Reply::class])->thisGateShouldAllow('ownModel')->otherwise()->weDenyAccess();
    }
}
