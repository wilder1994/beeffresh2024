<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Catalog\TaxonomyStatus;
use App\Models\MeatType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MeatType>
 */
class MeatTypeFactory extends Factory
{
    protected $model = MeatType::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'icon' => null,
            'color' => fake()->hexColor(),
            'status' => TaxonomyStatus::Active,
        ];
    }
}
