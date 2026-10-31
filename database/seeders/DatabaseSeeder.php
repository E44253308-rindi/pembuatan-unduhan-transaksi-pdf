<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);

        foreach (['Shonen','Seinen','Shoujo','Kodomo','Fantasy'] as $name) {
            \App\Models\Category::create(['name'=>$name]);
        }
        $category = \App\Models\Category::first();
        \App\Models\Comic::create(['category_id'=>$category->id,'title'=>'Naruto Vol. 1','author'=>'Masashi Kishimoto','publisher'=>'Elex Media','price'=>45000,'stock'=>20,'barcode'=>'8990000000001','synopsis'=>'Perjalanan ninja muda yang penuh semangat.']);
        \App\Models\Comic::create(['category_id'=>$category->id,'title'=>'One Piece Vol. 1','author'=>'Eiichiro Oda','publisher'=>'Elex Media','price'=>50000,'stock'=>15,'barcode'=>'8990000000002','synopsis'=>'Petualangan mencari harta karun legendaris.']);
    }
}
