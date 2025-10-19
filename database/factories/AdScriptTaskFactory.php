<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdScriptTask>
 */
class AdScriptTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array|mixed[]
     */
    public function definition(): array
    {
        return [
            'reference_script' => $this->faker->paragraph(3),
            'outcome_description' => $this->faker->sentence(10),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'error' => null,
        ];
    }

    /**
     * Failed state
     *
     * @return $this
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error' => $this->faker->sentence(5),
        ]);
    }

    /**
     * Completed state
     *
     * @return $this
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'new_script' => $this->faker->paragraph(4),
            'analysis' => $this->faker->paragraph(2),
            'error' => null,
        ]);
    }

    /**
     * Pending state
     *
     * @return $this
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'error' => null,
        ]);
    }
}
