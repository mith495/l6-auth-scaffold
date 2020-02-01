<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\UnauthorizedHttpException;
use App\Http\Resources\UserProfile;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \App\Http\Resources\UserProfile
     */
    public function get(Request $request)
    {
        return new UserProfile($request->user());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \App\Http\Resources\UserProfile
     * @throws \Illuminate\Validation\ValidationException
     */
    public function patch(Request $request, $id)
    {
        /** \App\Models\User */
        $user = User::findOrFail($id);

        if ($user != $request->user()) {
            throw new UnauthorizedHttpException('Unauthorized account access.');
        }

        $this->validate($request, [
            'first_name' => 'nullable|string|min:3',
            'last_name' => 'nullable|string|min:1',
            'email' => "nullable|email|unique:users,email,{$user->getKey()},user_id"
        ]);

        $updatedUser = tap($user)
            ->update(
                $request->only(
                    'first_name',
                    'last_name',
                    'email'
                )
            );

        return new UserProfile($updatedUser);
    }
}
