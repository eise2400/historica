<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ucfirst($this->faker->unique()->words(3, true)),
            'image_path' => 'photos/'.$this->faker->uuid().'.jpg',
            'category_id' => Category::factory(),
            'is_published' => true,
        ];
    }
}
