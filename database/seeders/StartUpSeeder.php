<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StartUpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::create([
            'name' => 'SuperAdmin',
            'email' => 'admin@encomgrid.com',
            'password' => bcrypt(123456),
            'username' => uniqid()
        ]);
        Customer::create([
            'name' => "Walking Customer",
            'phone' => "012345678",
        ]);
        Supplier::create([
            'name' => "Own Supplier",
            'phone' => "012345678",
        ]);
        $role = Role::create(['name' => 'SuperAdmin']);
        $user->syncRoles($role);
        $this->call([
            UnitSeeder::class,
            CurrencySeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
