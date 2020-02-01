<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\MemberUser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\UnauthorizedHttpException;
use App\Controllers\Features\JWTAuthenticationTrait;

class AuthController extends Controller
{
    use JWTAuthenticationTrait;

    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function token(Request $request)
    {
        $authHeader = $request->header('Authorization');

        // Get for Auth Basic
        if (strtolower(substr($authHeader, 0, 5)) !== 'basic') {
            throw new UnauthorizedHttpException('Invalid authorization header, should be type basic');
        }

        // Get credentials
        $credentials = base64_decode(trim(substr($authHeader, 5)));

        list ($login, $password) = explode(':', $credentials, 2);

        $attempt = ['email' => $login, 'password' => $password];

        // Do auth
        if (!$token = auth()->attempt($attempt)) {
            throw new UnauthorizedHttpException('Unauthorized login');
        }

        if (!auth()->user()->isActive()) {
            throw new UnauthorizedHttpException('Your account is not activated. Please activate your account.');
        }

        return $this->respondWithToken($token);
    }

    /**
     * Register user and respond with token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'password'=> 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'email' => $request->get('email'),
            'active' => false,
            'password' => bcrypt($request->get('password')),
        ]);

        event(new Registered($user));

        return new MemberUser($user);
    }

    /**
     * Get the authenticated User.
     *
     * @return \App\Http\Resources\MemberUser
     */
    public function getUser()
    {
        $user = User::with(['roles'])->findOrFail($this->auth->user()->user_id);

        return new MemberUser($user);
    }
}
