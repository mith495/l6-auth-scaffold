<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);


$api->version('v1', ['middleware' => ['api']], function (Router $api) {
    $api->group(['namespace' => 'App\Http\Controllers\Api'], function (Router $api) {
        $api->get('/', 'HomeController@index');

        /**
         * Login/registration
         */

        $api->group(['prefix' => 'auth'], function (Router $api) {
            $api->get('login', 'AuthController@token');
            $api->post('registration', 'AuthController@register');
            $api->get('logout', 'AuthController@logout');
        });

        /**
         * Account verification
         */
        $api->group(['prefix' => 'email'], function (Router $api) {
            $api->get('verify', [
                'as' => 'verification.notice',
                'uses' => 'VerificationController@show'
            ]);
            $api->post('verify/{id}', [
                'as' => 'verification.verify',
                'uses' => 'VerificationController@verify'
            ]);
            $api->post('resend', [
                'as' => 'verification.resend',
                'uses' => 'VerificationController@resend'
            ]);
        });

        /**
         * Reset password
         */
        $api->group(['prefix' => 'password'], function (Router $api) {
            $api->post('email', [
                'as' => 'password.reset',
                'uses' => 'PasswordResetController@sendResetLinkEmail'
            ]);

            $api->post('reset', 'PasswordResetController@reset');
        });

        /**
         * Profile & users
         */
        $api->group(['middleware' => [
            'auth:api',
            'verified',
            'active'
        ]], function (Router $api) {
            $api->group(['prefix' => 'profile'], function (Router $api) {
                $api->get('/', 'ProfileController@get');
                $api->patch('{id}', 'ProfileController@patch');
                $api->patch('{id}/password', 'PasswordController@patch');
            });
        });
    });
});
