<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminUserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(
            User::query()
                ->whereIn('user_type', [User::TYPE_ADMIN, User::TYPE_SUPER_ADMIN])
                ->latest()
                ->paginate(request()->integer('perPage', 15))
        );
    }

    public function store(StoreAdminUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'user_type' => $data['userType'],
            'is_active' => $data['isActive'] ?? true,
        ]);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $adminUser): UserResource
    {
        abort_unless($adminUser->isAdmin(), 404);

        return new UserResource($adminUser);
    }

    public function update(UpdateAdminUserRequest $request, User $adminUser): UserResource
    {
        abort_unless($adminUser->isAdmin(), 404);

        $data = $request->validated();

        $attributes = [];

        if (array_key_exists('name', $data)) {
            $attributes['name'] = $data['name'];
        }

        if (array_key_exists('email', $data)) {
            $attributes['email'] = $data['email'];
        }

        if (array_key_exists('password', $data) && filled($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        if (array_key_exists('userType', $data)) {
            $attributes['user_type'] = $data['userType'];
        }

        if (array_key_exists('isActive', $data)) {
            $attributes['is_active'] = $data['isActive'];
        }

        $adminUser->fill($attributes)->save();

        if (array_key_exists('is_active', $attributes) && ! $adminUser->is_active) {
            $adminUser->tokens()->delete();
        }

        return new UserResource($adminUser->refresh());
    }
}
