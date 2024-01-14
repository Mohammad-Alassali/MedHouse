<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\AllowingAdmin;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    /**
     * add number for new admin by SuperAdmin
     * @param Request $request
     * @return JsonResponse
     */
    public function addNewAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^([0-9]*)$/|min:10|max:10|unique:allowing_admins'
        ]);
        $user = User::query()->firstWhere('phone_number', $request['phone_number']);
        if ($user) {
            if ($user['role'] == 'admin') {
                return $this->failed('is admin actually');
            }
            $user->update([
                'role' => 'admin'
            ]);
            if ($request->has('permissions')) {
                foreach ($request['permissions'] as $per) {
                    if ($per == 0) {
                        continue;
                    }
                    $user->permissions()->attach($per);
                }
            }
        } else {
            $allow = AllowingAdmin::query()->create($request->only('phone_number'));
            if ($request->has('permissions')) {
                foreach ($request['permissions'] as $per) {
                    if ($per == 0) {
                        continue;
                    }
                    $allow->permissions()->attach($per);
                }
            }
        }
        return $this->success(null, 'new number has been added successfully');
    }

    /**
     * remove number from admins if exists by SuperAdmin
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^([0-9]*)$/|min:10|max:10'
        ]);

        $user = User::query()->firstWhere('phone_number', $request['phone_number']);
        if ($user) {
            $user->delete();
            return $this->success(null, 'Done');
        }
        $allow = AllowingAdmin::query()->firstWhere('phone_number', $request['phone_number']);
        if ($allow) {
            $allow->delete();
            return $this->success(null, 'Done');
        }

        return $this->failed('not found to delete', 404);

    }

    /**
     * register as admin if the number is allowed to be admin
     * @param RegisterRequest $request
     * @return JsonResponse
     */

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $allow = AllowingAdmin::query()->firstWhere('phone_number', $data['phone_number']);
        if (!$allow) {
            return $this->failed('this number is not allowed to be admin');
        }
        $data['role'] = 'admin';
        $user = User::query()->create($data);
        foreach ($allow->permissions as $per) {
            $user->permissions()->attach($per);
        }
        $allow->delete();
        return $this->success(
            UserResource::make($user),
            'success'
        );
    }


    /**
     * login as admin if role of user is admin or super admin
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->validated();
        $user = User::query()->firstWhere('phone_number', $request['phone_number']);
        if ($user['role'] == 'user') {
            return $this->failed('you not allowed to login as admin');
        }
        if (!Hash::check($request['password'], $user['password'])) {
            return $this->failed('invalid password');
        }
        return $this->success(UserResource::make($user), 'success login');
    }

    public function addPermission(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^([0-9]*)$/|min:10|max:10|exists:users',
            'permission_id' => 'required|integer'
        ]);
        User::query()
            ->firstWhere('phone_number', $request['phone_number'])
            ->permissions()->attach($request['permission_id']);
        return $this->success(null, 'Done');
    }

    public function deletePermission(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^([0-9]*)$/|min:10|max:10|exists:users',
            'permission_id' => 'required|integer'
        ]);
        User::query()
            ->firstWhere('phone_number', $request['phone_number'])
            ->permissions()->detach($request['permission_id']);
        return $this->success(null, 'Done');
    }

    public function getPermissions(): JsonResponse
    {
        $user = User::query()->find(Auth::id());
        if ($user['role'] == 'user') {
            return $this->failed('You are not a admin');
        }
        if ($user['role'] == 'super_admin') {
            return $this->success([
                'permissions' => Permission::all(),
                'role' => 'super_admin'
            ]);
        } else {
            return $this->success([
                'permissions' => $user->permissions,
                'role' => 'admin'
            ]);
        }
    }

    public function getNumberOfUser(): JsonResponse
    {
        return $this->success([
            'count' => User::query()->where('role', '=', 'user')
                ->count()
        ]);
    }

    public function getNumberOfAdmin(): JsonResponse
    {
        return $this->success([
            'count' => User::query()->where('role', '!=', 'user')
                    ->count() - 1
        ]);
    }

    public function getAllAdmins(): JsonResponse
    {
        $admins = User::query()->where('role', '=', 'admin')
            ->with('permissions')->get();
        return $this->success($admins);
    }

    public function isSuperAdmin(): JsonResponse
    {
        return $this->success(['is_super_admin' => auth()->user()->role == 'super_admin']);
    }
}
