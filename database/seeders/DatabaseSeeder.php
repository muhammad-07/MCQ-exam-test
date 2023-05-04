<?php

namespace Database\Seeders;

use App\Models\ExamSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Question::factory(10)->create();
        $categories = [
            [
                'name' => 'negative',
                'value' => 0.5,
            ],
            [
                'name' => 'time_limit',
                'value' => 10,
            ],
            [
                'name' => 'question_count',
                'value' => 10,
            ]
        ];

        foreach ($categories as $category) {
            ExamSetting::create($category);
        }
    }
}
