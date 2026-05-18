<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\CintaSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CintaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_cinta_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.cinta.edit'))
            ->assertOk()
            ->assertSee('Haz clic en una casilla vacía para seleccionar una imagen')
            ->assertSee('formato 16:9')
            ->assertSee('bf-cinta-slot', false);
    }

    public function test_admin_can_upload_cinta_slide(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $file = UploadedFile::fake()->image('banner.jpg', 1920, 1080);

        $this->actingAs($admin)
            ->post(route('admin.cinta.store'), [
                'slot' => 0,
                'imagen' => $file,
            ])
            ->assertRedirect(route('admin.cinta.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('cinta_slides', 1);
        $slide = CintaSlide::query()->first();
        $this->assertNotNull($slide);
        $this->assertSame(0, $slide->sort_order);
        Storage::disk('public')->assertExists('cinta/'.$slide->image);
    }

    public function test_home_shows_cinta_when_slides_exist(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $file = UploadedFile::fake()->image('slide.jpg', 1920, 1080);

        $this->actingAs($admin)->post(route('admin.cinta.store'), [
            'slot' => 2,
            'imagen' => $file,
        ]);

        $slide = CintaSlide::query()->first();
        $this->assertNotNull($slide);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('bf-cinta-marquee', false)
            ->assertSee($slide->imageUrl(), false);
    }

    public function test_customer_cannot_access_cinta_admin(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.cinta.edit'))
            ->assertForbidden();
    }
}
