<?php

namespace App\Http\Services;

use App\Models\User;
use App\Http\Services\ImageUploadService;

class UserService
{
    protected $uploadService;

    public function __construct(ImageUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function getAllUsers()
    {
        return User::latest()->get();
    }

    public function getUserById(int $id): User
    {
        return User::findOrFail($id);
    }

    public function activateUser(int $id): User
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'is_active' => 1 
        ]);
        $user->notify(new \App\Notifications\AccountActivatedNotification());

        return $user;
    }

    public function deleteUser(int $id): bool
    {
        $user = User::findOrFail($id);
        if ($user->id_front_url) {
            $this->uploadService->delete($user->id_front_url);
        }
        if ($user->id_back_url) {
            $this->uploadService->delete($user->id_back_url);
        }

        return $user->delete();
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update([
            'full_name'    => $data['full_name'] ?? $user->full_name,
            'phone_number' => $data['phone_number'] ?? $user->phone_number,
            'faculty'      => $data['faculty'] ?? $user->faculty,
            'department'   => $data['department'] ?? $user->department,
        ]);

        return $user;
    }
    
}