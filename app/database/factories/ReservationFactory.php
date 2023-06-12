<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'lesson_id' => function () {
                $lesson = Lesson::factory()->create();
                \Log::info('チェックReservationFactory');
                \Log::info($lesson);
                return Lesson::factory()->create()->id;
            },
            'user_id' => function () {
                return User::factory()->create()->id;
            },
        ];
    }
}
