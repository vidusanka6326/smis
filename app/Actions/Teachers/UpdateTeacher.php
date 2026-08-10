<?php

namespace App\Actions\Teachers;

use App\Enums\UserStatus;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class UpdateTeacher
{
    /**
     * Update teacher profile and linked user fields.
     *
     * @param  array{
     *     name: string,
     *     email: string,
     *     password?: string|null,
     *     status: string,
     *     employee_no: string,
     *     phone?: string|null
     * }  $data
     */
    public function handle(Teacher $teacher, array $data): Teacher
    {
        return DB::transaction(function () use ($teacher, $data): Teacher {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => UserStatus::from($data['status']),
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $teacher->user->update($userData);

            $teacher->update([
                'employee_no' => $data['employee_no'],
                'phone' => $data['phone'] ?? null,
            ]);

            return $teacher->refresh()->load('user');
        });
    }
}
