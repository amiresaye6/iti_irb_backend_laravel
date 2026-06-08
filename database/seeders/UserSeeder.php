<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Students (IDs 1-4)
            ['role' => 'student', 'full_name' => 'د. عمر الفاروق', 'email' => 'omar@med.edu', 'password' => 'password', 'national_id' => '29001011234001', 'phone_number' => '01011111111', 'faculty' => 'كلية الطب', 'department' => 'الجراحة العامة', 'id_front_url' => 'uploads/seed/dummy_id_front.jpg', 'id_back_url' => 'uploads/seed/dummy_id_back.jpg', 'is_active' => true],
            ['role' => 'student', 'full_name' => 'د. ليلى عثمان', 'email' => 'laila@med.edu', 'password' => 'password', 'national_id' => '29001011234002', 'phone_number' => '01022222222', 'faculty' => 'كلية الطب', 'department' => 'الأطفال', 'id_front_url' => 'uploads/seed/dummy_id_front.jpg', 'id_back_url' => 'uploads/seed/dummy_id_back.jpg', 'is_active' => true],
            ['role' => 'student', 'full_name' => 'د. كريم محسن', 'email' => 'karim@med.edu', 'password' => 'password', 'national_id' => '29001011234003', 'phone_number' => '01033333333', 'faculty' => 'كلية الطب', 'department' => 'النساء والتوليد', 'id_front_url' => 'uploads/seed/dummy_id_front.jpg', 'id_back_url' => 'uploads/seed/dummy_id_back.jpg', 'is_active' => true],
            ['role' => 'student', 'full_name' => 'د. نهى عبد الرحمن', 'email' => 'noha@med.edu', 'password' => 'password', 'national_id' => '29001011234004', 'phone_number' => '01044444444', 'faculty' => 'كلية الصيدلة', 'department' => 'الصيدلانيات', 'id_front_url' => 'uploads/seed/dummy_id_front.jpg', 'id_back_url' => 'uploads/seed/dummy_id_back.jpg', 'is_active' => true],

            // Admin (ID 5)
            ['role' => 'admin', 'full_name' => 'أستاذ محمود (أدمن اللجان)', 'email' => 'admin@irb.edu', 'password' => 'password', 'national_id' => '28001011234005', 'phone_number' => '01112345678', 'faculty' => '', 'department' => '', 'id_front_url' => '', 'id_back_url' => '', 'is_active' => true],

            // Sample Officers (IDs 6-7)
            // ['role' => 'sample_officer', 'full_name' => 'م. حسام (الإحصاء الطبي)', 'email' => 'sample1@irb.edu', 'password' => 'password', 'national_id' => '27001011234006', 'phone_number' => '01212345678', 'faculty' => '', 'department' => '', 'id_front_url' => '', 'id_back_url' => '', 'is_active' => true],
            // ['role' => 'sample_officer', 'full_name' => 'م. رشا (مسؤول عينات)', 'email' => 'sample2@irb.edu', 'password' => 'password', 'national_id' => '27001011234007', 'phone_number' => '01212345679', 'faculty' => '', 'department' => '', 'id_front_url' => '', 'id_back_url' => '', 'is_active' => true],

            // Reviewers (IDs 8-10)
            ['role' => 'reviewer', 'full_name' => 'أ.د. خالد عبد السلام', 'email' => 'khaled.rev@irb.edu', 'password' => 'password', 'national_id' => '26001011234008', 'phone_number' => '01512345678', 'faculty' => 'كلية الطب', 'department' => 'الباطنة', 'id_front_url' => '', 'id_back_url' => '', 'is_active' => true],
            ['role' => 'reviewer', 'full_name' => 'أ.د. هدى الشربيني', 'email' => 'hoda.rev@irb.edu', 'password' => 'password', 'national_id' => '26001011234009', 'phone_number' => '01512345679', 'faculty' => 'كلية الطب', 'department' => 'الأورام', 'id_front_url' => '', 'id_back_url' => '', 'is_active' => true],
            ['role' => 'reviewer', 'full_name' => 'أ.د. عصام النجار', 'email' => 'essam.rev@irb.edu', 'password' => 'password', 'national_id' => '26001011234010', 'phone_number' => '01512345680', 'faculty' => 'كلية الطب', 'department' => 'الصحة العامة', 'id_front_url' => '', 'id_back_url' => '', 'is_active' => true],

            // Manager (ID 11)
            ['role' => 'manager', 'full_name' => 'أ.د. طارق الحديدي (مدير IRB)', 'email' => 'manager@irb.edu', 'password' => 'password', 'national_id' => '25001011234011', 'phone_number' => '01099999999', 'faculty' => 'كلية الطب', 'department' => 'إدارة الجودة والبحث', 'id_front_url' => '', 'id_back_url' => '', 'is_active' => true],

            // More Students (IDs 12-16)
            ['role' => 'student', 'full_name' => 'د. يوسف الشناوي', 'email' => 'youssef@med.edu', 'password' => 'password', 'national_id' => '29001011234012', 'phone_number' => '01055555555', 'faculty' => 'كلية الطب', 'department' => 'جراحة العظام', 'id_front_url' => 'uploads/seed/dummy_id_front.jpg', 'id_back_url' => 'uploads/seed/dummy_id_back.jpg', 'is_active' => true],
            ['role' => 'student', 'full_name' => 'د. سلمى رضا', 'email' => 'salma@med.edu', 'password' => 'password', 'national_id' => '29001011234013', 'phone_number' => '01066666666', 'faculty' => 'كلية الأسنان', 'department' => 'طب الفم والأسنان', 'id_front_url' => 'uploads/seed/dummy_id_front.jpg', 'id_back_url' => 'uploads/seed/dummy_id_back.jpg', 'is_active' => true],
            ['role' => 'student', 'full_name' => 'د. ماجد توفيق', 'email' => 'maged@med.edu', 'password' => 'password', 'national_id' => '29001011234014', 'phone_number' => '01077777777', 'faculty' => 'كلية التمريض', 'department' => 'تمريض باطني وجراحي', 'id_front_url' => 'uploads/seed/dummy_id_front.jpg', 'id_back_url' => 'uploads/seed/dummy_id_back.jpg', 'is_active' => true],
            ['role' => 'student', 'full_name' => 'د. سارة كمال', 'email' => 'sara@med.edu', 'password' => 'password', 'national_id' => '29001011234015', 'phone_number' => '01088888888', 'faculty' => 'كلية الطب', 'department' => 'الرمد', 'id_front_url' => 'uploads/seed/dummy_id_front.jpg', 'id_back_url' => 'uploads/seed/dummy_id_back.jpg', 'is_active' => true],
            ['role' => 'student', 'full_name' => 'د. أحمد مصطفى', 'email' => 'ahmed@med.edu', 'password' => 'password', 'national_id' => '29001011234016', 'phone_number' => '01099999990', 'faculty' => 'كلية الطب', 'department' => 'الباطنة', 'id_front_url' => 'uploads/seed/dummy_id_front.jpg', 'id_back_url' => 'uploads/seed/dummy_id_back.jpg', 'is_active' => true],

            // Super Admin (ID 17)
            ['role' => 'super_admin', 'full_name' => 'أ.د. احمد عناني', 'email' => 'superAdmin@irb.edu', 'password' => 'password', 'national_id' => '21001011234605', 'phone_number' => '01112345699', 'faculty' => '', 'department' => '', 'id_front_url' => '', 'id_back_url' => '', 'is_active' => true],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
