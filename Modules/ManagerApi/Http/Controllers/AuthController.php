<?php

namespace Modules\ManagerApi\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\Manager;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\ManagerApi\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @param LoginRequest $request
     *
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $identity = $request->get('identity');
        $user = Manager::where('email', $identity)
            ->orWhere('username', $identity)
            ->orWhere('phone_number', $identity)
            ->first();

        if (!$user) {
            throw ApiException::unauthorized(trans('auth.password'));
        }

        if (!password_verify($request->get('password'), $user->password)) {
            throw ApiException::unauthorized(trans('auth.password'));
        }

        return $this->respondWithToken(Auth::guard('manager')->tokenById($user->id));
    }


    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json(Auth::guard('manager')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::guard('manager')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::guard('manager')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('manager')->factory()->getTTL() * 60
        ]);
    }
}
