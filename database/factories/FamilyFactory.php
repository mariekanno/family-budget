<?php

namespace Database\Factories;

use App\Models\Family;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamilyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'created_by' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Family $family) {
            $family->users()->syncWithoutDetaching([
                $family->created_by => ['role' => 'admin'],
            ]);
        });
    }
}