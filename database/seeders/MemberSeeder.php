<?php

namespace Database\Seeders;

use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'name' => 'Md. Abu Bakar Siddique',
                'username' => 'jewel',
                'password' => 'jewel',
                'role' => 'member',
                'avatar' => 'images/members/member_1.jpg',
            ],
            [
                'name' => 'A.Z.M. Monjur Hossain',
                'username' => 'monjur',
                'password' => 'monjur',
                'role' => 'member',
                'avatar' => 'images/members/member_2.jpg',
            ],
            [
                'name' => 'Taposh Kumar Biswas',
                'username' => 'taposh',
                'password' => 'taposh',
                'role' => 'member',
                'avatar' => 'images/members/member_3.jpg',
            ],
            [
                'name' => 'Md Kamruzzaman',
                'username' => 'saiful',
                'password' => 'saiful',
                'role' => 'member',
                'avatar' => 'images/members/member_4.jpg',
            ],
            [
                'name' => 'Ram Prasad Chakraborty',
                'username' => 'ramu',
                'password' => 'ramu',
                'role' => 'member',
                'avatar' => 'images/members/member_5.jpg',
            ],
            [
                'name' => 'Muhammad Mizanur Rahman',
                'username' => 'mizan',
                'password' => 'mizan',
                'role' => 'member',
                'avatar' => 'images/members/member_6.jpg',
            ],
            [
                'name' => 'Mohammad Nizam Uddin',
                'username' => 'nizam',
                'password' => 'nizam',
                'role' => 'admin',
                'avatar' => 'images/members/member_7.jpg',
            ],
            [
                'name' => 'Md Mohibur Rahman',
                'username' => 'rana',
                'password' => 'rana',
                'role' => 'member',
                'avatar' => 'images/members/member_8.jpg',
            ],
            [
                'name' => 'Md. Mostafa Shamsuzzaman',
                'username' => 'mostafa',
                'password' => 'mostafa',
                'role' => 'member',
                'avatar' => 'images/members/member_9.jpg',
            ],
            [
                'name' => 'Mohammad Ziaur Rahaman',
                'username' => 'ziur',
                'password' => 'ziur',
                'role' => 'member',
                'avatar' => 'images/members/member_10.jpg',
            ],
            [
                'name' => 'Proshanta Podder',
                'username' => 'proshanta',
                'password' => 'proshanta',
                'role' => 'member',
                'avatar' => 'images/members/member_11.jpg',
            ],
            [
                'name' => 'Md Tarek Salah Uddin',
                'username' => 'tarek',
                'password' => 'tarek',
                'role' => 'member',
                'avatar' => 'images/members/member_12.jpg',
            ],
        ];

        $adminRole = class_exists(Role::class) ? Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator']) : null;
        $superAdminRole = class_exists(Role::class) ? Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']) : null;
        $userRole = class_exists(Role::class) ? Role::firstOrCreate(['slug' => 'user'], ['name' => 'User']) : null;

        foreach ($members as $m) {
            $user = User::updateOrCreate(
                ['name' => $m['name']],
                [
                    'username' => $m['username'],
                    'email' => $m['username'].'@marineharmony.com',
                    'password' => Hash::make($m['password']),
                    'role' => $m['role'],
                    'avatar' => $m['avatar'],
                ]
            );

            if ($user->isAdmin() && $adminRole && $superAdminRole) {
                $user->roles()->syncWithoutDetaching([$adminRole->id, $superAdminRole->id]);
            } elseif ($userRole) {
                $user->roles()->syncWithoutDetaching([$userRole->id]);
            }
        }
    }
}
