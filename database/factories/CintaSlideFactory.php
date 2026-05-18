<?php

namespace Database\Factories;

use App\Models\CintaSlide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CintaSlide>
 */
class CintaSlideFactory extends Factory
{
    protected $model = CintaSlide::class;

    public function definition(): array
    {
        return [
            'image' => fake()->uuid().'.jpg',
            'alt' => fake()->optional()->sentence(3),
            'link_url' => null,
            'sort_order' => 0,
        ];
    }
}
