<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'question' => $this->faker->sentence,
            'option_1' => $this->faker->sentence,
            'option_2' => $this->faker->sentence,
            'option_3' => $this->faker->sentence,
            'option_4' => $this->faker->sentence,
            'correct_answer' => $this->faker->numberBetween(1,4)
        ];
    }
}
