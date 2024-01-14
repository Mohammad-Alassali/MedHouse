<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classification_id')->constrained('classifications')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('scientific_name');
            $table->string('commercial_name');
            $table->string('scientific_name_ar');
            $table->string('commercial_name_ar');
            $table->string('description');
            $table->integer('quantity');
            $table->double('price');
            $table->date('expiration_date');
            $table->string('photo')->nullable();
            $table->integer('number_of_sales')->default(0);
            $table->boolean('is_otc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
