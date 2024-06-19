<?php

namespace Database\Factories;

use App\Models\UniversalWorker;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UniversalWorker>
 */
class UniversalWorkerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = UniversalWorker::class;

    public function definition()
    {
        return [
            'worker_id' => Str::uuid(),
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'country' => $this->faker->country,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'registration_date' => $this->faker->date(),
            'worker_qr' => Str::random(10),
        ];
    }

}
