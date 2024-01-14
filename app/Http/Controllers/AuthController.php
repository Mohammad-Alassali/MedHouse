<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyForgetPasswordRequest;
use App\Http\Requests\VerifyingRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\TempUser;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use UltraMsg\WhatsAppApi;

class AuthController extends Controller
{

    /*
     * register as user and send verify code
     *
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($request->has('photo')) {
            $data['photo'] = ImageController::store($data['photo'], "Users");
        }
        TempUser::query()
            ->firstWhere('phone_number', '=', $data['phone_number'])
            ?->delete();
        TempUser::query()->create($data);
        $code = rand(100000, 999999);
        Verification::query()
            ->where('type', '=', 'verify_account')
            ->where('phone_number', '=', $data['phone_number'])
            ->first()
            ?->delete();

        Verification::query()->create([
            'code' => $code,
            'phone_number' => $data['phone_number'],
            'type' => 'verify_account'
        ]);
        require_once(base_path('vendor/autoload.php'));
        $ultramsg_token = env('WHATSAPP_TOKEN'); // Ultramsg.com token
        $instance_id = env('WHATSAPP_ID'); // Ultramsg.com instance id
        $client = new WhatsAppApi($ultramsg_token, $instance_id);
        $number = "+963" . substr($data['phone_number'], 1, 9);
        $to = $number;
        $body = trans('messages.register_verify', ['name' => $request['name'], 'code' => $code]);
        $client->sendChatMessage($to, $body);
        return $this->success(null, 'we send the code');
    }

    /*
     * log out from application for any role
     *
     */
    public function logout(): JsonResponse
    {
        User::query()->find(Auth::id())->tokens()->delete();
        return $this->success(null, 'logged out');
    }

    /*
     * verify user, save as real user and send access token
     *
     */

    public function verifyingRegister(VerifyingRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $verification = Verification::query()
            ->where('type', '=', 'verify_account')
            ->where('code', '=', $data['code'])
            ->where('phone_number', '=', $data['phone_number'])
            ->first();
        if ($verification) {
            $tempUser = TempUser::query()
                ->firstWhere('phone_number', '=', $data['phone_number']);
            $userData['name'] = $tempUser['name'];
            $userData['password'] = Hash::make($tempUser['password']);
            $userData['phone_number'] = $tempUser['phone_number'];
            $userData['number_of_orders'] = 1;
            $userData['role'] = 'user';
            if ($tempUser['photo'])
                $userData['photo'] = $tempUser['photo'];
            $tempUser->delete();
            $verification->delete();
            $user = User::query()->create($userData);
            return $this->success(new UserResource($user), '');
        }
        return $this->failed('invalid code');
    }

    /*
     * login as user and send access token
     *
     *
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->firstWhere('phone_number', '=', $data['phone_number']);

        if (!Hash::check($data['password'], $user['password'])) {
            return $this->failed('invalid password');
        }

        return $this->success(new UserResource($user), '');
    }

    /*
     * accept forget password request and send verify code
     * delete old verification if exists
     *
     */
    public function forgetPassword(ForgetPasswordRequest $request): JsonResponse
    {
        $request->validated();
        $code = rand(100000, 999999);
        Verification::query()
            ->where('type', '=', 'forget_password')
            ->where('phone_number', '=', $request['phone_number'])
            ->first()?->delete();
        Verification::query()->create([
            'code' => $code,
            'phone_number' => $request['phone_number'],
            'type' => 'forget_password'
        ]);
        $user = User::firstWhere('phone_number', $request['phone_number']);
        require_once(base_path('vendor/autoload.php'));
        $ultramsg_token = env('WHATSAPP_TOKEN'); // Ultramsg.com token
        $instance_id = env('WHATSAPP_ID'); // Ultramsg.com instance id
        $client = new WhatsAppApi($ultramsg_token, $instance_id);
        $number = "+963" . substr($request['phone_number'], 1, 9);
        $to = $number;
        $body = trans('messages.reset_verify', ['name' => $user['name'], 'code' => $code]);
        $client->sendChatMessage($to, $body);
        return $this->success(null, 'we send the code');
//        return $this->success(['code'=>$code],"We send the code");
    }

    public function verifyForgetPassword(VerifyForgetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $verification = Verification::query()
            ->where('type', '=', 'forget_password')
            ->where('code', '=', $data['code'])
            ->where('phone_number', '=', $data['phone_number'])
            ->first();
        if ($verification) {
            return $this->success(null, 'valid code');
        }
        return $this->failed('invalid code');

    }

    /*
     * reset password after forget it by verify code
     *
     */

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $verification = Verification::query()
            ->where('type', '=', 'forget_password')
            ->where('code', '=', $data['code'])
            ->where('phone_number', '=', $data['phone_number'])
            ->first();
        if ($verification) {
            $user = User::query()->firstWhere('phone_number', '=', $data['phone_number']);
            $user->update([
                'password' => Hash::make($data['password'])
            ]);
            $verification->delete();
            return $this->success(null, 'password has been changed successfully');
        }
        return $this->failed('invalid code');
    }


}

