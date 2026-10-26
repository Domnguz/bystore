<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;

class SitemapController extends Controller
{
    public function __invoke()
    {
        return response()->stream(function () {
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            $url = function (string $direccion) {
                echo '<url><loc>'.htmlspecialchars($direccion, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc></url>';
            };
            $url(url('/'));
            $url(route('catalogo'));
            foreach (Categoria::orderBy('id')->cursor() as $categoria) {
                $url(url('/categoria/'.$categoria->id));
            }
            foreach (Producto::orderBy('id')->cursor() as $producto) {
                $url(route('tienda.productos.show', $producto->id));
            }
            echo '</urlset>';
        }, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
