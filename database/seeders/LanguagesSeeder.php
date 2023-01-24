<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Language::firstOrCreate(
            ['short_tag' => "hy"],
            [
                'title' => "Armenian",
                'short_tag' => "hy",
                'icon' => "",
                'ordering' => "1",
                'is_default' => "1",
            ]
        );
        Language::firstOrCreate(
            ['short_tag' => "en"],
            [
                'title' => "English",
                'short_tag' => "en",
                'icon' => "",
                'ordering' => "2",
                'is_default' => "0",
            ]
        );

    }
}
