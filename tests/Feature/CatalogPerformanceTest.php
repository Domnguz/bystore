<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogPerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        // Isolated in-memory schema: never migrate or clear the local shop database.
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
        });
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoria_id');
            $table->string('nombre');
            $table->text('descripcion');
            $table->decimal('precio', 10, 2);
            $table->integer('stock');
            $table->string('imagen');
            $table->timestamps();
        });

        DB::table('categorias')->insert([
            ['id' => 1, 'nombre' => 'Componentes'],
            ['id' => 2, 'nombre' => 'Accesorios'],
        ]);
        foreach (range(1, 25) as $id) {
            DB::table('productos')->insert([
                'id' => $id,
                'categoria_id' => $id <= 13 ? 1 : 2,
                'nombre' => "Producto $id",
                'descripcion' => 'Producto de prueba',
                'precio' => 10,
                'stock' => 5,
                'imagen' => 'test.webp',
                'created_at' => now()->addSeconds($id),
                'updated_at' => now(),
            ]);
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
    }

    public function test_home_loads_categories_in_batches_without_duplicate_menu_query(): void
    {
        $this->get('/')->assertOk()->assertViewHas('productos', fn ($items) =>
            $items->count() === 4 && $items->every(fn ($item) => $item->relationLoaded('categoria'))
        );
        $this->assertCount(3, DB::getQueryLog());
    }

    public function test_catalog_pagination_keeps_every_product_accessible(): void
    {
        $ids = [];
        foreach (range(1, 3) as $page) {
            DB::flushQueryLog();
            $response = $this->get('/productos?page='.$page)->assertOk();
            $products = $response->viewData('productos');
            $this->assertSame(25, $products->total());
            $this->assertLessThanOrEqual(12, $products->count());
            $ids = array_merge($ids, $products->pluck('id')->all());
            $this->assertCount(3, DB::getQueryLog());
        }
        $this->assertSame(range(1, 25), $ids);
    }

    public function test_category_pagination_keeps_filter_and_avoids_duplicate_menu_query(): void
    {
        $response = $this->get('/categoria/1?page=2')->assertOk();
        $products = $response->viewData('productos');
        $this->assertSame(13, $products->total());
        $this->assertSame([13], $products->pluck('id')->all());
        $this->assertCount(4, DB::getQueryLog());
        $this->get('/categoria/999')->assertNotFound();
    }

    public function test_admin_product_categories_use_two_queries_for_25_products(): void
    {
        $user = new User(['name' => 'Administrador', 'email' => 'test@example.test']);
        $user->id = 1;
        $this->actingAs($user)->get('/admin/productos')->assertOk();
        $this->assertCount(2, DB::getQueryLog());
    }
}
