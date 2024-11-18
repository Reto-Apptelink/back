<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word, // Genera un nombre de producto
            'description' => $this->faker->sentence, // Genera una descripción
            'price' => $this->faker->randomFloat(2, 5, 100), // Genera un precio entre 5 y 100 con 2 decimales
            'stock_quantity' => $this->faker->numberBetween(1, 100), // Genera una cantidad de stock entre 1 y 100
        ];
    }
}
