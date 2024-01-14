<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditNameRequest;
use App\Http\Requests\EditPasswordRequest;
use App\Http\Requests\EditPhotoRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use UltraMsg\WhatsAppApi;

class UserController extends Controller
{
    public function showNotifications(): JsonResponse
    {
        return $this->success(
            NotificationResource::collection(
                User::query()->find(Auth::id())['myNotifications']
            )
        );
    }

    public function editPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^([0-9]*)$/|min:10|max:10|unique:users'
        ]);
        $code = rand(100000, 999999);
        $user = User::query()->find(Auth::id());
        Verification::query()
            ->where('type', '=', 'change_number')
            ->where('phone_number', '=', $user['phone_number'])
            ->first()
            ?->delete();


        Verification::query()->create([
            'phone_number' => $user['phone_number'],
            'code' => $code,
            'type' => 'change_number'
        ]);
        require_once(base_path('vendor/autoload.php'));
        $ultramsg_token = env('WHATSAPP_TOKEN'); // Ultramsg.com token
        $instance_id = env('WHATSAPP_ID'); // Ultramsg.com instance id
        $client = new WhatsAppApi($ultramsg_token, $instance_id);
        $number = "+963" . substr($request['phone_number'], 1, 9);
        $to = $number;
        $body = trans('messages.register_verify', ['name' => $user['name'], 'code' => $code]);
        $client->sendChatMessage($to, $body);
        return $this->success(null, 'we send the code');
    }

    public function verifyChangeNumber(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string|regex:/^([0-9]*)$/|min:10|max:10|unique:users',
            'code' => 'required|min:6|max:6'
        ]);
        $user = User::query()->find(Auth::id());
        $verification = Verification::query()
            ->where('type', '=', 'change_number')
            ->where('code', '=', $request['code'])
            ->where('phone_number', '=', $user['phone_number'])
            ->first();
        if ($verification) {
            $verification->delete();
            User::query()->find(Auth::id())->update([
                'phone_number' => $request['phone_number']
            ]);
            return $this->success(null, 'number changed');
        }
        return $this->failed('invalid code');
    }

    public function showUser(): JsonResponse
    {
        return $this->success(new UserResource(Auth::user()), '');
    }

    public function editName(EditNameRequest $request): JsonResponse
    {
        $request->validated();
        User::query()->find(Auth::id())->update([
            'name' => $request['name']
        ]);
        return $this->success(null, 'name changed');
    }

    public function editPhoto(EditPhotoRequest $request): JsonResponse
    {
        $request->validated();
        $user = User::query()->find(Auth::id());
        $photoName = ImageController::update($request->file('photo'), $user['photo'], 'Users');
        $user->update([
            'photo' => $photoName
        ]);
        return $this->success(null, 'photo changed');
    }

    public function editPassword(EditPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::query()->find(Auth::id());
        if (Hash::check($data['password'], $user['password'])) {
            $user->update([
                'password' => Hash::make($data['new_password'])
            ]);
            return $this->success(null, 'password has been changed successfully');
        }
        return $this->failed('invalid code');
    }

    public function saveNotificationToken(Request $request)
    {
        $request->validate([
            'notification_token' => 'required|string'
        ]);
        Auth::user()->update([
            'notification_token' => $request['notification_token']
        ]);
        return $this->success(null);
    }

}
