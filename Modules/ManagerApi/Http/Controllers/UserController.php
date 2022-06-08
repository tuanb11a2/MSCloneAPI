<?php

namespace Modules\ManagerApi\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Transformers\SuccessResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\ManagerApi\Http\Requests\UpdateUserRequest;
use Modules\ManagerApi\Transformers\UserResource;
use Modules\ManagerApi\Transformers\UsersResource;

class UserController extends Controller
{
    public const PER_PAGE = 10;

    public function index(): UsersResource
    {
        $users = User::query()->withCount('groups', 'posts')->orderByDesc('created_at')->paginate(self::PER_PAGE);

        return UsersResource::make($users);
    }

    public function show(int $userId): UserResource
    {
        $user = User::query()->find($userId);

        if (!$user) {
            throw ApiException::notFound('Người dùng không tồn tại');
        }

        return UserResource::make($user);
    }

    public function update(UpdateUserRequest $request, int $userId): SuccessResource
    {
        $user = User::query()->find($userId);

        if (!$user) {
            throw ApiException::notFound('Người dùng không tồn tại');
        }

        if (!$request->has('avatar')) {
            $s3Path = $user->avatar;
        } else {
            $avatar = $request->file('avatar');
            $path = Storage::put('images/avatars', $avatar);
            $s3Path = Storage::url($path);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'avatar' => $s3Path,
        ]);

        $user->save();

        return new SuccessResource();
    }

    public function destroy(int $id): SuccessResource
    {
        $user = User::query()->find($id);

        if (!$user) {
            throw ApiException::notFound('Người dùng không tồn tại');
        }

        $user->delete();

        return new SuccessResource();
    }
}