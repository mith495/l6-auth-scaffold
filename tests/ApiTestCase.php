<?php

namespace Tests;

use UserStorySeeder;

abstract class ApiTestCase extends TestCase
{
    /**
     * Authorization header array
     *
     * @var null|array
     */
    protected static $auth = null;

    /**
     * @var array Credentials for admin user
     */
    protected static $adminUser = [
        'email' => UserStorySeeder::TEST_ADMIN_EMAIL,
        'password' =>  UserStorySeeder::TEST_ADMIN_PASSWORD
    ];

    /**
     * @var array Credentials for member user
     */
    protected static $memberUser = [
        'email' => UserStorySeeder::TEST_MEMBER_EMAIL,
        'password' =>  UserStorySeeder::TEST_MEMBER_PASSWORD
    ];

    /**
     * API Test case helper function for setting up
     * the request auth header as supplied user
     *
     * @param array $credentials
     * @return $this
     */
    public function actingAsUser($credentials)
    {
        $token = $this->loginUsingCredentials($credentials);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token]);

        return $this;
    }

    /**
     * API Test case helper function for setting up
     * the request as a logged in admin user
     *
     * @return ApiTestCase
     */
    public function actingAsAdmin()
    {
        return $this->actingAsUser(static::$adminUser);
    }

    /**
     * API Test case helper function for setting up
     * the request as a logged in member user
     *
     * @return ApiTestCase
     */
    public function actingAsMember()
    {
        return $this->actingAsUser(static::$memberUser);
    }

    /**
     * Login using credentials provided and return the JWT
     *
     * @param array $credentials
     * @return false|string JWT
     */
    public function loginUsingCredentials($credentials)
    {
        $token = \JWTAuth::attempt($credentials);

        $this::$auth = ['Authorization' => 'Bearer ' . $token];

        return $token;
    }

    /**
     * API Test case helper function to return the logged in user
     *
     * @return \Tymon\JWTAuth\Contracts\JWTSubject
     */
    public function actingUser()
    {
        return \JWTAuth::user();
    }

    /**
     * Generate basic auth string for given credentials
     *
     * @param string $username
     * @param string $password
     * @return string
     */
    public function generateBasicAuthString($username, $password)
    {
        return 'Basic ' . base64_encode($username . ':' . $password);
    }
}
