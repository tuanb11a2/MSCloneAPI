<?php

namespace Modules\ManagerApi\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\Group;
use App\Models\Channel;
use App\Transformers\SuccessResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Avatar\Facade as Avatar;
use Modules\ManagerApi\Transformers\GroupsResource;
use Modules\ManagerApi\Http\Requests\StoreChannelRequest;
use Modules\ManagerApi\Http\Requests\StoreGroupRequest;
use Modules\ManagerApi\Http\Requests\UpdateChannelRequest;
use Modules\ManagerApi\Http\Requests\UpdateGroupRequest;
use Modules\ManagerApi\Transformers\GroupResource;
use Modules\ManagerApi\Transformers\UsersResource;

class GroupController extends Controller
{
    public const PER_PAGE = 10;

    /**
     *
     * @return GroupsResource
     */
    public function index(): GroupsResource
    {
        $groups = Group::query()->whereHas('users', function (Builder $query) {
            $query->where('user_id', auth()->user()->id);
        })->withCount(['users', 'channels'])->with('creator')->paginate(self::PER_PAGE);

        return GroupsResource::make($groups);
    }


    public function show(int $groupId): GroupResource
    {
        $group = Group::query()->where('id', $groupId)
            ->with('channels', function ($query) {
                $query->withCount('posts', 'exercises');
            })
            ->with('users', function ($query) {
                $query->with('posts', function ($query) {
                    $query->withCount('comments');
                })->withCount('posts');
            })
            ->withCount('users', 'channels')
            ->withCount(['channels', 'exercises'])->first();
        $group->users_count = $group->users->count();
        $group['users']->map(function ($user) use ($group) {
            $user->posts_count = $user->posts->whereIn('channel_id', $group->channels->pluck('id'))->count();
            $user->is_creator = $user->id === $group->creator->id;
        });

        if (!$group) {
            throw ApiException::notFound('Nhóm không tồn tại!');
        }

        return GroupResource::make($group);
    }


    /**
     *
     * @param StoreGroupRequest $request
     * @return GroupResource
     */
    public function store(StoreGroupRequest $request): GroupResource
    {
        $imageName = $request->get('name') . '.png';

        if (!$request->has('avatar')) {
            $avatar = Avatar::create($request->get('name'))->getImageObject()->save($imageName);
            $path = 'images/groups/' . $imageName;
            Storage::put($path, $avatar);
            $s3Path = Storage::url($path);
        } else {
            $avatar = $request->file('avatar');
            $path = Storage::put('images/groups', $avatar);
            $s3Path = Storage::url($path);
        }

        $group = Group::query()->create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'slug' => Str::uuid()->toString(),
            'creator_id' => Auth::user()->id,
            'privacy' => $request->get('privacy'),
            'avatar' => $s3Path
        ]);
        $group->channels()->create([
            'name' => 'Chung',
            'slug' => 'general',
        ]);
        $group->users()->attach([
            'user_id' => $request->get('user_id'),
        ]);


        return GroupResource::make($group);
    }

    public function update(int $groupId, UpdateGroupRequest $request)
    {
        $group = Group::query()->where('id', $groupId)->first();
        if (!$group) {
            throw ApiException::notFound('Nhóm không tồn tại!');
        }

        if (!$request->has('avatar')) {
            $s3Path = $group->avatar;
        } else {
            $avatar = $request->file('avatar');
            $path = Storage::put('images/groups', $avatar);
            $s3Path = Storage::url($path);
        }

        $group->update([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'creator_id' => Auth::user()->id,
            'privacy' => $request->get('privacy'),
            'avatar' => $s3Path
        ]);
        $group->save();

        return GroupResource::make($group);
    }

    /**
     * Delete Group
     *
     * @param integer $id
     * @return void
     */
    public function destroy(int $id)
    {
        $group = Group::query()->find($id);
        if (!$group) {
            throw ApiException::notFound('Nhóm không tồn tại!');
        }
        $channels = $group->channels;
        foreach ($channels as $channel) {
            foreach ($channel->posts as $post) {
                $post->comments()->delete();
                $post->delete();
            }
            foreach ($channel->exercises as $exercise) {
                $exercise->comments()->delete();
                $exercise->users()->detach();
                $exercise->submissions()->delete();
                $exercise->delete();
            }
            $channel->delete();
        }
        $group->users()->detach();
        $group->todos()->delete();
        $group->delete();

        return new SuccessResource();
    }

    public function addMembers(int $groupId, Request $request)
    {
        $group = Group::query()->find($groupId);
        if (!$group) {
            throw ApiException::notFound('Nhóm không tồn tại!');
        }

        $members = collect($request->get('users'))->pluck('id');
        $group->users()->attach($members);

        return new SuccessResource();
    }


    /**
     * Add Channel
     *
     * @param integer $groupId
     * @param StoreChannelRequest $request
     * @return GroupResource
     */
    public function addChannel(int $groupId, StoreChannelRequest $request): GroupResource
    {
        $group = Group::find($groupId);
        if (!$group) {
            throw ApiException::notFound('Nhóm không tồn tại!');
        }
        $name = $request->get('name');
        $channel = $group->channels()->create([
            'name' => $name,
            'slug' => Str::slug($name)
        ]);

        return GroupResource::make($channel);
    }

    /**
     *  Update Channel
     *
     * @param integer $groupId
     * @param UpdateChannelRequest $request
     * @return GroupResource
     */
    public function updateChannel(int $groupId, int $channelId, UpdateChannelRequest $request): GroupResource
    {
        $group = Group::find($groupId);
        if (!$group) {
            throw ApiException::notFound('Nhóm không tồn tại!');
        }
        $channel = $group->channels()->find($channelId);
        if (!$channel) {
            throw ApiException::notFound('Kênh không tồn tại!');
        }
        $channel->update([
            'name' => $request->get('name'),
            'slug' => Str::slug($request->get('name'))
        ]);

        return GroupResource::make($channel);
    }


    /**
     * Delete Channel
     *
     * @param integer $groupId
     * @param integer $channelId
     * @return SuccessResource
     */
    public function deleteChannel(int $groupId, int $channelId): SuccessResource
    {
        $group = Group::find($groupId);
        if (!$group) {
            throw ApiException::notFound('Nhóm không tồn tại!');
        }

        if (Auth::id() !== $group->creator_id) {
            throw ApiException::unauthorized('Bạn không có quyền xóa kênh này!');
        }

        $channel = $group->channels()->find($channelId);

        if (!$channel) {
            throw ApiException::notFound('Kênh không tồn tại!');
        }

        foreach ($channel->posts as $post) {
            $post->comments()->delete();
            $post->delete();
        }
        foreach ($channel->exercises as $exercise) {
            $exercise->comments()->delete();
            $exercise->users()->detach();
            $exercise->delete();
        }
        $channel->delete();

        return new SuccessResource();
    }

    /**
     * Remove member from group
     *
     * @param integer $groupId
     * @param integer $memberId
     * @return SuccessResource
     */
    public function removeMember(int $groupId, int $memberId): SuccessResource
    {
        $group = Group::query()->find($groupId);

        if (!$group) {
            throw ApiException::notFound('Nhóm không tồn tại!');
        }

        if (!$group->users()->where('user_id', $memberId)->exists()) {
            throw ApiException::forbidden('Bạn không là thành viên cuả nhóm này!');
        }

        $group->users()->detach($memberId);

        return new SuccessResource();
    }
}
