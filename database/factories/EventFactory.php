<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->text(),
            'body' => fake()->text(),
            'date' => fake()->date(),
            'is_private' => false,
        ];
    }

    public function public(): Factory
    {
        return $this->state(function (array $attr)
        {
            return [
                'is_private' => false,
            ];
        });
    }

    public function private(): Factory
    {
        return $this->state(function (array $attr)
        {
            return [
                'is_private' => true,
            ];
        });
    }
}
