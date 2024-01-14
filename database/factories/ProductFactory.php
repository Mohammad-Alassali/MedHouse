<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classification_id' => 1,
            'company_id' => 1,
            'scientific_name' => fake()->name,
            'commercial_name' => fake()->name,
            'description' => 'dfsklaj;jf',
            'quantity' => 1,
            'price' => 24,
            'expiration_date' => fake()->date,
            'number_of_sales' => fake()->randomNumber(),
            /*     $table->foreignId('classification_id')->constrained('classifications')->cascadeOnUpdate()->cascadeOnDelete();
             $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
             $table->string('scientific_name');
             $table->string('commercial_name');
             $table->string('description');
             $table->integer('quantity');
             $table->double('price');
             $table->date('expiration_date');
             $table->string('photo')->nullable();
             $table->integer('number_of_sales');*/
        ];
    }
}
