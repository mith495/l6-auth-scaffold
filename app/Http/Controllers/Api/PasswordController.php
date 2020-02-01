<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\UnauthorizedHttpException;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PasswordController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
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
            'password' => 'required|confirmed|min:6',
        ]);

        $request->user()->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'message' => 'Password has been updated successfully!'
        ]);
    }
}
