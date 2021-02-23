<?php

namespace Railroad\Usora\Routes;

use Illuminate\Routing\Router;
use Railroad\Usora\Controllers\ApiController;
use Railroad\Usora\Controllers\AuthenticationController;

class RouteRegistrar
{
    /**
     * @var Router
     */
    private $router;

    /**
     * RouteRegistrar constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function registerAll()
    {
        $this->authenticationRoutes();
        $this->passwordRoutes();
        $this->emailRoutes();
        $this->userJSONApiRoutes();
        $this->userFormApiRoutes();
        $this->userJWTApiRoutes();
    }

    public function authenticationRoutes()
    {
        $this->router->group(
            [
                'prefix' => config('usora.route_prefix'),
                'middleware' => config('usora.route_middleware_public_groups'),
            ],
            function () {

                $this->router->post(
                    'authenticate/with-credentials',
                    AuthenticationController::class . '@authenticateViaCredentials'
                )
                    ->name('usora.authenticate.with-credentials');

                $this->router->get(
                    'authenticate/with-verification-token',
                    AuthenticationController::class . '@authenticateViaVerificationToken'
                )
                    ->name('usora.authenticate.with-verification-token');

                $this->router->get(
                    'authenticate/with-third-party',
                    AuthenticationController::class . '@authenticateViaThirdParty'
                )
                    ->name('usora.authenticate.with-third-party');

                $this->router->get(
                    'authenticate/render-post-message-verification-token',
                    AuthenticationController::class . '@renderVerificationTokenViaPostMessage'
                )
                    ->name('usora.authenticate.render-post-message-verification-token');

                $this->router->match(
                    ['GET', 'POST'],
                    'authenticate/set-authentication-cookie',
                    AuthenticationController::class . '@setAuthenticationCookieViaVerificationToken'
                )
                    ->name('usora.authenticate.set-authentication-cookie');

                $this->router->get(
                    'is-email-unique',
                    ApiController::class . '@isEmailUnique'
                )
                    ->name('usora.is-email-unique');

                $this->router->get(
                    'is-display-name-unique',
                    ApiController::class . '@isDisplayNameUnique'
                )
                    ->name('usora.is-display-name-unique');
            }
        );

        $this->router->group(
            [
                'prefix' => config('usora.route_prefix'),
                'middleware' => config('usora.route_middleware_logged_in_groups'),
            ],
            function () {

                $this->router->get(
                    'deauthenticate',
                    AuthenticationController::class . '@deauthenticate'
                )
                    ->name('usora.deauthenticate');

            }
        );

    }

    public function passwordRoutes()
    {
        $this->router->group(
            [
                'prefix' => config('usora.route_prefix'),
                'middleware' => config('usora.route_middleware_public_groups'),
            ],
            function () {

                $this->router->post(
                    'password/send-reset-email',
                    \Railroad\Usora\Controllers\ForgotPasswordController::class . '@sendResetLinkEmail'
                )
                    ->name('usora.password.send-reset-email');

                $this->router->post(
                    'password/reset',
                    \Railroad\Usora\Controllers\ResetPasswordController::class . '@reset'
                )
                    ->name('usora.password.reset');

            }
        );

        $this->router->group(
            [
                'prefix' => config('usora.route_prefix'),
                'middleware' => config('usora.route_middleware_logged_in_groups'),
            ],
            function () {

                $this->router->patch(
                    'user/update-password',
                    \Railroad\Usora\Controllers\PasswordController::class . '@update'
                )
                    ->name('usora.user-password.update');

            }
        );
    }

    public function emailRoutes()
    {
        $this->router->group(
            [
                'prefix' => config('usora.route_prefix'),
                'middleware' => config('usora.route_middleware_logged_in_groups'),
            ],
            function () {
                $this->router->post(
                    'email-change/request',
                    \Railroad\Usora\Controllers\EmailChangeController::class . '@request'
                )
                    ->name('usora.email-change.request');

                $this->router->get(
                    'email-change/confirm',
                    \Railroad\Usora\Controllers\EmailChangeController::class . '@confirm'
                )
                    ->name('usora.email-change.confirm');
            }
        );
    }

    public function userJSONApiRoutes()
    {
        $this->router->group(
            [
                'prefix' => config('usora.route_prefix'),
                'middleware' => config('usora.route_middleware_logged_in_groups'),
            ],
            function () {
                $this->router->get(
                    'json-api/user/index',
                    \Railroad\Usora\Controllers\UserJsonController::class . '@index'
                )
                    ->name('usora.json-api.user.index');

                $this->router->get(
                    'json-api/user/show/{id}',
                    \Railroad\Usora\Controllers\UserJsonController::class . '@show'
                )
                    ->name('usora.json-api.user.show');

                $this->router->put(
                    'json-api/user/store',
                    \Railroad\Usora\Controllers\UserJsonController::class . '@store'
                )
                    ->name('usora.json-api.user.store');

                $this->router->patch(
                    'json-api/user/update/{id}',
                    \Railroad\Usora\Controllers\UserJsonController::class . '@update'
                )
                    ->name('usora.json-api.user.update');

                $this->router->delete(
                    'json-api/user/delete/{id}',
                    \Railroad\Usora\Controllers\UserJsonController::class . '@delete'
                )
                    ->name('usora.json-api.user.delete');

            }
        );
    }

    public function userFormApiRoutes()
    {
        $this->router->group(
            [
                'prefix' => config('usora.route_prefix'),
                'middleware' => config('usora.route_middleware_logged_in_groups'),
            ],
            function () {
                $this->router->put(
                    'user/store',
                    \Railroad\Usora\Controllers\UserController::class . '@store'
                )
                    ->name('usora.user.store');

                $this->router->patch(
                    'user/update/{id}',
                    \Railroad\Usora\Controllers\UserController::class . '@update'
                )
                    ->name('usora.user.update');
                $this->router->delete(
                    'user/delete/{id}',
                    \Railroad\Usora\Controllers\UserController::class . '@delete'
                )
                    ->name('usora.user.delete');

            }
        );
    }

    // todo: this can probably be done better
    public function userJWTApiRoutes()
    {
        $this->router->group(
            [
                'prefix' => config('usora.route_prefix'),
                'middleware' => config('usora.route_middleware_app_logged_in_groups'),
            ],
            function () {
                $this->router->put('api/logout', \Railroad\Usora\Controllers\ApiController::class . '@logout');
                $this->router->put('api/profile', \Railroad\Usora\Controllers\ApiController::class . '@getAuthUser');
                $this->router->post('api/profile/update', \Railroad\Usora\Controllers\ApiController::class . '@updateUser');
            }
        );

        $this->router->group(
            [
                'prefix' => config('usora.route_prefix'),
                'middleware' => config('usora.route_middleware_app_public_groups', []),
            ],
            function () {
                $this->router->put('api/login', \Railroad\Usora\Controllers\ApiController::class . '@login');
                $this->router->put('api/forgot', \Railroad\Usora\Controllers\ApiController::class . '@forgotPassword');
                $this->router->get(
                    'api/is-email-unique',
                    ApiController::class . '@isEmailUnique'
                );
                $this->router->get(
                    'api/is-display-name-unique',
                    ApiController::class . '@isDisplayNameUnique'
                );
            }
        );


    }
}