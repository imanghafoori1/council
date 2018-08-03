<?php

namespace App\Providers;

use App\Exceptions\ThrottleException;
use App\Reply;
use App\Thread;
use Illuminate\Auth\AuthenticationException;
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
//        Gate::before(function ($user) {
//            if ($user->name === 'John Doe') return true;
//        });
    }

    private function authenticateRoutes(): void
    {
        HeyMan::whenYouReachRoute([
            'avatar',
            'threads.store',
            'threads.destroy',
        ])->youShouldBeLoggedIn()->otherwise()->weThrowNew(AuthenticationException::class);

        HeyMan::whenYouReachRoute(['replies.store'])->youShouldBeLoggedIn()->otherwise()->redirect()->to('/login');
    }

    private function validateRequests(): void
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
    }

    private function authorizeAdminRoutes(): void
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

    private function authorizeEloquentModels(): void
    {
        $createReply = function ($user) {
            if (! $lastReply = $user->fresh()->lastReply) {
                return true;
            }
            return ! $lastReply->wasJustPublished();
        };

        Gate::define('createReply', $createReply);

        $hasConfirmedEmail = function () {
            return auth()->user()->confirmed or auth()->user()->isAdmin();
        };

        HeyMan::whenYouCreate(Thread::class)->thisClosureShouldAllow($hasConfirmedEmail)->otherwise()->redirect()->to('/threads')->with('flash', 'You must first confirm your email address.');

        HeyMan::whenYouReachRoute('replies.store')->thisGateShouldAllow('createReply')->otherwise()->weThrowNew(ThrottleException::class, 'You are replying too frequently. Please take a break.');

        $shouldOwnModel = function ($model) {
            return auth()->id() == $model->user_id or auth()->user()->isAdmin();
        };
        Gate::define('ownModel', function ($user, $model) {
            return $user->id == $model->user_id or $user->isAdmin();
        });
        HeyMan::whenYouUpdate([Thread::class, Reply::class])->thisGateShouldAllow('ownModel')->otherwise()->weDenyAccess();
        HeyMan::whenYouDelete([Thread::class, Reply::class,])->thisClosureShouldAllow($shouldOwnModel)->otherwise()->weDenyAccess();
    }
}
