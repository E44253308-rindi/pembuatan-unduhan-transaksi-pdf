<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_product_with_an_image_and_see_it_on_the_dashboard(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Komik Baru']);

        $this->actingAs($admin)
            ->post(route('admin.comics.store'), [
                'category_id' => $category->id,
                'title' => 'Komik Dashboard',
                'author' => 'Penulis',
                'publisher' => 'Penerbit',
                'price' => 25000,
                'stock' => 7,
                'barcode' => '8991234567890',
                'cover' => UploadedFile::fake()->image('komik-dashboard.jpg'),
            ])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('success');

        $comic = Comic::where('title', 'Komik Dashboard')->firstOrFail();
        Storage::disk('public')->assertExists($comic->cover);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Komik Dashboard')
            ->assertSee(e(Storage::disk('public')->url($comic->cover)), false)
            ->assertSee('Kelola semua produk');
    }

    public function test_dashboard_updates_product_count_and_displays_default_cover_without_an_uploaded_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Komik Tanpa Sampul']);
        Comic::create([
            'category_id' => $category->id,
            'title' => 'Produk Tanpa Foto',
            'author' => 'Penulis',
            'publisher' => 'Penerbit',
            'price' => 10000,
            'stock' => 3,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total produk')
            ->assertSee('Produk Tanpa Foto')
            ->assertSee(asset('images/no-cover.svg'), false);
    }
}
