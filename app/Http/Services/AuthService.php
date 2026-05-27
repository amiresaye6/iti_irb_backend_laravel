<?php

namespace App\Http\Services;

use App\Models\User;
use App\Http\Services\ImageUploadService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthService
{
    protected $uploadService;

    public function __construct(ImageUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function register(array $data): User
    {
        $idFrontUrl =!empty($data['id_front']) ? $this->uploadService->store($data['id_front'], 'id_cards') : '';
        $idBackUrl = !empty($data['id_back']) ? $this->uploadService->store($data['id_back'], 'id_cards') : '';
        $role = $data['role'] ?? 'student';
        $isActive = ($role === 'student') ? 0 : 1;

        return User::create([
            'role'          => $role,
            'full_name'     => $data['full_name'],
            'email'         => $data['email'],
            'password'      => $data['password'],
            'national_id'   => $data['national_id'],
            'phone_number'  => $data['phone_number'],
            'faculty'       => $data['faculty'],
            'department'    => $data['department'] ?? null,
            'id_front_url'  => $idFrontUrl,
            'id_back_url'   => $idBackUrl,
            'is_active'     => $isActive,
        ]);
    }

    public function login(array $credentials): array
{
    $user = User::where('email', $credentials['email'])->first();
    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['البريد الإلكتروني أو كلمة المرور غير صحيحة'],
        ]);
    }
    if (!$user->is_active) {
        throw ValidationException::withMessages([
            'email' => ['حسابك قيد المراجعة من قبل الإدارة، سيتم تفعيله قريباً.'],
        ]);
    }
    return [
        'user'  => $user,
        'token' => $user->createToken('auth-token')->plainTextToken,
    ];
}
    public function Logout(User $user): void
    {
        $user->currentAccessToken()->delete();

    }
    public function sendPasswordResetLink(string $email): bool
    {
      $status = Password::broker()->sendResetLink(['email' => $email]);
      return $status === Password::RESET_LINK_SENT;
    }
    public function resetPassword(array $data): bool
    {
      $status = Password::broker()->reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));
            }
        );
        return $status === Password::PASSWORD_RESET;
    }

}
 