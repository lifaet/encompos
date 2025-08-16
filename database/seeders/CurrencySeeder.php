<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            ['name' => 'Bangladeshi Taka', 'code' => 'BDT', 'symbol' => '৳','active'=>true]
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }
    }
}
