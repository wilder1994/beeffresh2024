<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Catalog\TaxonomyStatus;
use App\Models\MeatCut;
use App\Models\MeatType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MeatCut>
 */
class MeatCutFactory extends Factory
{
    protected $model = MeatCut::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'meat_type_id' => MeatType::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'status' => TaxonomyStatus::Active,
        ];
    }
}
