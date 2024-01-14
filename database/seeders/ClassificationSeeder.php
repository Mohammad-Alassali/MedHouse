<?php

namespace Database\Seeders;

use App\Models\Classification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Stichoza\GoogleTranslate\GoogleTranslate;

class ClassificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classifications = [
            'serum',
            'capsule',
            'tablet',
            'suspension',
            'injection',
            'child_care',
            'eye_drop',
            'skin_care',
            'dental_care',
            'supplement'
        ];

        foreach ($classifications as $classification) {
            $data = [];
            $data['name'] = $classification;
            Classification::query()->create($data);
        }
    }
}
