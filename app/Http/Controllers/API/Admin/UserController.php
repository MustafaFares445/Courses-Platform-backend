<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Http\Requests\Admin\UserFilterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(UserFilterRequest $request): AnonymousResourceCollection
    {
        $query = User::query()
            ->where('user_type', User::TYPE_STUDENT);

        $search = $request->string('search')->toString();

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('filter.isActive')) {
            $query->where('is_active', $request->boolean('filter.isActive'));
        }

        match ($request->string('sort')->toString()) {
            'name' => $query->orderBy('name'),
            '-name' => $query->orderByDesc('name'),
            'email' => $query->orderBy('email'),
            '-email' => $query->orderByDesc('email'),
            'createdAt' => $query->orderBy('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        return UserResource::collection(
            $query->paginate($request->integer('perPage', 15))
        );
    }

    public function show(User $user): UserResource
    {
        abort_unless($user->isStudent(), 404);

        return new UserResource($user);
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): UserResource
    {
        abort_unless($user->isStudent(), 404);

        $user->forceFill([
            'is_active' => $request->boolean('isActive'),
        ])->save();

        if (! $user->is_active) {
            $user->tokens()->delete();
        }

        return new UserResource($user->refresh());
    }
}
