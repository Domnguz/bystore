<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompletarCatalogoSeeder extends Seeder
{
    /**
     * Completa el catálogo de demostración hasta 100 productos.
     * Los precios son referencias de Mercado Libre Perú, en soles.
     */
    public function run(): void
    {
        $catalogo = [
            'Procesadores' => [
                ['Procesador AMD Ryzen 5 5500 6 núcleos 4.2 GHz AM4', 'AMD', 'Ryzen 5 5500', 375, '6 núcleos, 12 hilos, hasta 4.2 GHz, socket AM4, TDP 65 W', 'Placas madre AM4 con BIOS compatible; memoria DDR4'],
                ['Procesador AMD Ryzen 5 5600 6 núcleos 4.4 GHz AM4', 'AMD', 'Ryzen 5 5600', 643, '6 núcleos, 12 hilos, hasta 4.4 GHz, 32 MB caché L3, AM4', 'Placas madre AM4; memoria DDR4'],
                ['Procesador AMD Ryzen 5 5600G con gráficos Radeon Vega', 'AMD', 'Ryzen 5 5600G', 590, '6 núcleos, 12 hilos, gráficos Radeon integrados, AM4', 'Placas madre AM4; memoria DDR4'],
                ['Procesador AMD Ryzen 5 5600X 6 núcleos AM4', 'AMD', 'Ryzen 5 5600X', 680, '6 núcleos, 12 hilos, hasta 4.6 GHz, 32 MB caché L3', 'Placas madre AM4; memoria DDR4'],
                ['Procesador AMD Ryzen 7 5700G con gráficos Radeon Vega', 'AMD', 'Ryzen 7 5700G', 850, '8 núcleos, 16 hilos, gráficos Radeon integrados, AM4', 'Placas madre AM4; memoria DDR4'],
                ['Procesador AMD Ryzen 7 5800X 8 núcleos AM4', 'AMD', 'Ryzen 7 5800X', 990, '8 núcleos, 16 hilos, hasta 4.7 GHz, 32 MB caché L3', 'Placas madre AM4; memoria DDR4'],
                ['Procesador AMD Ryzen 5 7500F 6 núcleos AM5', 'AMD', 'Ryzen 5 7500F', 720, '6 núcleos, 12 hilos, hasta 5.0 GHz, socket AM5', 'Placas madre AM5; memoria DDR5'],
                ['Procesador AMD Ryzen 5 7600 6 núcleos AM5', 'AMD', 'Ryzen 5 7600', 850, '6 núcleos, 12 hilos, hasta 5.1 GHz, gráficos Radeon integrados', 'Placas madre AM5; memoria DDR5'],
                ['Procesador AMD Ryzen 5 9600X 6 núcleos AM5', 'AMD', 'Ryzen 5 9600X', 786, '6 núcleos, 12 hilos, hasta 5.4 GHz, socket AM5', 'Placas madre AM5; memoria DDR5'],
                ['Procesador Intel Core i5-12400F 6 núcleos LGA1700', 'Intel', 'Core i5-12400F', 520, '6 núcleos, 12 hilos, hasta 4.4 GHz, sin gráficos integrados', 'Placas madre LGA1700; memoria DDR4 o DDR5 según placa'],
                ['Procesador Intel Core i5-13400F 10 núcleos LGA1700', 'Intel', 'Core i5-13400F', 790, '10 núcleos híbridos, 16 hilos, hasta 4.6 GHz', 'Placas madre LGA1700; memoria DDR4 o DDR5 según placa'],
                ['Procesador Intel Core i5-14400F 10 núcleos LGA1700', 'Intel', 'Core i5-14400F', 900, '10 núcleos híbridos, 16 hilos, hasta 4.7 GHz', 'Placas madre LGA1700; memoria DDR4 o DDR5 según placa'],
            ],
            'Tarjeta Grafica' => [
                ['Tarjeta gráfica MSI GeForce GTX 1650 Ventus XS 4 GB', 'MSI', 'GeForce GTX 1650 Ventus XS', 780, '4 GB GDDR6, interfaz PCIe 3.0, doble ventilador', 'PC de escritorio con ranura PCIe x16 y fuente adecuada'],
                ['Tarjeta gráfica Gigabyte GeForce RTX 3050 Windforce OC 6 GB', 'Gigabyte', 'GeForce RTX 3050 Windforce OC', 1050, '6 GB GDDR6, PCIe 4.0, doble ventilador', 'PC de escritorio con ranura PCIe x16'],
                ['Tarjeta gráfica MSI GeForce RTX 3060 Ventus 2X 12 GB', 'MSI', 'GeForce RTX 3060 Ventus 2X', 1450, '12 GB GDDR6, PCIe 4.0, doble ventilador', 'PC de escritorio con ranura PCIe x16'],
                ['Tarjeta gráfica Gigabyte GeForce RTX 4060 Windforce OC 8 GB', 'Gigabyte', 'GeForce RTX 4060 Windforce OC', 1550, '8 GB GDDR6, PCIe 4.0, doble ventilador', 'PC de escritorio con ranura PCIe x16'],
                ['Tarjeta gráfica ASUS Dual GeForce RTX 4060 OC 8 GB', 'ASUS', 'Dual GeForce RTX 4060 OC', 1650, '8 GB GDDR6, PCIe 4.0, dos ventiladores Axial-tech', 'PC de escritorio con ranura PCIe x16'],
                ['Tarjeta gráfica MSI GeForce RTX 4060 Ti Ventus 2X 8 GB', 'MSI', 'GeForce RTX 4060 Ti Ventus 2X', 2200, '8 GB GDDR6, PCIe 4.0, doble ventilador', 'PC de escritorio con ranura PCIe x16'],
                ['Tarjeta gráfica Gigabyte Radeon RX 7600 Gaming OC 8 GB', 'Gigabyte', 'Radeon RX 7600 Gaming OC', 1600, '8 GB GDDR6, PCIe 4.0, triple ventilador', 'PC de escritorio con ranura PCIe x16'],
                ['Tarjeta gráfica Sapphire Pulse Radeon RX 6600 8 GB', 'Sapphire', 'Radeon RX 6600 Pulse', 1100, '8 GB GDDR6, PCIe 4.0, doble ventilador', 'PC de escritorio con ranura PCIe x16'],
                ['Tarjeta gráfica ASUS Dual Radeon RX 7600 OC 8 GB', 'ASUS', 'Dual Radeon RX 7600 OC', 1650, '8 GB GDDR6, PCIe 4.0, doble ventilador', 'PC de escritorio con ranura PCIe x16'],
                ['Tarjeta gráfica Gigabyte Radeon RX 7700 XT Gaming OC 12 GB', 'Gigabyte', 'Radeon RX 7700 XT Gaming OC', 2350, '12 GB GDDR6, PCIe 4.0, triple ventilador', 'PC de escritorio con ranura PCIe x16 y fuente adecuada'],
                ['Tarjeta gráfica MSI GeForce RTX 5060 Ti Ventus 2X 8 GB', 'MSI', 'GeForce RTX 5060 Ti Ventus 2X', 2100, '8 GB GDDR7, PCIe 5.0, doble ventilador', 'PC de escritorio con ranura PCIe x16'],
            ],
            'Memorias RAM' => [
                ['Memoria Kingston Fury Beast DDR4 8 GB 3200 MHz', 'Kingston', 'Fury Beast KF432C16BB/8', 170, '8 GB DDR4 UDIMM, 3200 MHz, CL16, disipador de calor', 'Placas madre de escritorio compatibles con DDR4'],
                ['Kit Kingston Fury Beast DDR4 32 GB 2x16 3200 MHz', 'Kingston', 'Fury Beast KF432C16BBK2/32', 520, 'Kit 2x16 GB DDR4, 3200 MHz, CL16, disipador de calor', 'Placas madre de escritorio compatibles con DDR4'],
                ['Kit Corsair Vengeance LPX DDR4 16 GB 2x8 3200 MHz', 'Corsair', 'Vengeance LPX CMK16GX4M2B3200C16', 280, 'Kit 2x8 GB DDR4, 3200 MHz, CL16', 'Placas madre de escritorio compatibles con DDR4'],
                ['Kit Corsair Vengeance LPX DDR4 32 GB 2x16 3200 MHz', 'Corsair', 'Vengeance LPX CMK32GX4M2E3200C16', 450, 'Kit 2x16 GB DDR4, 3200 MHz, CL16', 'Placas madre de escritorio compatibles con DDR4'],
                ['Kit Kingston Fury Beast DDR5 16 GB 2x8 5200 MHz', 'Kingston', 'Fury Beast KF552C40BBK2-16', 330, 'Kit 2x8 GB DDR5, 5200 MHz, CL40', 'Placas madre de escritorio compatibles con DDR5'],
                ['Kit Kingston Fury Beast DDR5 32 GB 2x16 5600 MHz', 'Kingston', 'Fury Beast KF556C40BBK2-32', 570, 'Kit 2x16 GB DDR5, 5600 MHz, CL40', 'Placas madre de escritorio compatibles con DDR5'],
                ['Kit Corsair Vengeance DDR5 32 GB 2x16 6000 MHz', 'Corsair', 'Vengeance CMK32GX5M2E6000C36', 650, 'Kit 2x16 GB DDR5, 6000 MHz, CL36', 'Placas madre de escritorio compatibles con DDR5'],
                ['Memoria Crucial DDR4 16 GB 3200 MHz UDIMM', 'Crucial', 'CT16G4DFRA32A', 260, '16 GB DDR4 UDIMM, 3200 MHz, CL22', 'Placas madre de escritorio compatibles con DDR4'],
                ['Memoria Kingston ValueRAM DDR4 16 GB SODIMM 3200 MHz', 'Kingston', 'ValueRAM KVR32S22S8/16', 399, '16 GB DDR4 SODIMM, 3200 MHz, 1.2 V', 'Notebooks con ranura SODIMM DDR4'],
                ['Kit TeamGroup T-Force Delta RGB DDR5 32 GB 2x16 6000 MHz', 'TeamGroup', 'T-Force Delta RGB FF3D532G6000HC38', 720, 'Kit 2x16 GB DDR5, 6000 MHz, iluminación RGB', 'Placas madre de escritorio compatibles con DDR5'],
            ],
            'Almacenamiento SSD y HDD' => [
                ['SSD Kingston NV3 NVMe PCIe 4.0 M.2 500 GB', 'Kingston', 'NV3 SNV3S/500G', 330, '500 GB, M.2 2280, NVMe PCIe 4.0 x4, lectura hasta 5000 MB/s', 'Placas madre con ranura M.2 NVMe'],
                ['SSD Kingston NV3 NVMe PCIe 4.0 M.2 2 TB', 'Kingston', 'NV3 SNV3S/2000G', 1050, '2 TB, M.2 2280, NVMe PCIe 4.0 x4', 'Placas madre con ranura M.2 NVMe'],
                ['SSD WD Blue SN580 NVMe M.2 1 TB', 'Western Digital', 'Blue SN580 WDS100T3B0E', 490, '1 TB, M.2 2280, NVMe PCIe 4.0', 'Placas madre con ranura M.2 NVMe'],
                ['SSD WD Black SN770 NVMe M.2 1 TB', 'Western Digital', 'Black SN770 WDS100T3X0E', 580, '1 TB, M.2 2280, NVMe PCIe 4.0, lectura hasta 5150 MB/s', 'Placas madre con ranura M.2 NVMe'],
                ['SSD Samsung 990 EVO Plus NVMe M.2 1 TB', 'Samsung', '990 EVO Plus MZ-V9S1T0', 720, '1 TB, M.2 2280, NVMe PCIe 4.0/5.0 x2', 'Placas madre con ranura M.2 NVMe'],
                ['SSD Crucial BX500 SATA 2.5 pulgadas 1 TB', 'Crucial', 'BX500 CT1000BX500SSD1', 430, '1 TB, formato 2.5 pulgadas, interfaz SATA III', 'PC o notebook con conexión SATA de 2.5 pulgadas'],
                ['SSD Kingston A400 SATA 2.5 pulgadas 480 GB', 'Kingston', 'A400 SA400S37/480G', 220, '480 GB, formato 2.5 pulgadas, interfaz SATA III', 'PC o notebook con conexión SATA de 2.5 pulgadas'],
                ['Disco duro Seagate Barracuda 2 TB 3.5 pulgadas', 'Seagate', 'Barracuda ST2000DM008', 290, '2 TB, 3.5 pulgadas, SATA III, 7200 RPM', 'PC de escritorio con bahía y conexión SATA'],
                ['Disco duro WD Blue 1 TB 3.5 pulgadas', 'Western Digital', 'Blue WD10EZEX', 230, '1 TB, 3.5 pulgadas, SATA III, 7200 RPM', 'PC de escritorio con bahía y conexión SATA'],
                ['SSD Crucial P3 Plus NVMe M.2 2 TB', 'Crucial', 'P3 Plus CT2000P3PSSD8', 770, '2 TB, M.2 2280, NVMe PCIe 4.0', 'Placas madre con ranura M.2 NVMe'],
                ['SSD WD Black SN770 NVMe M.2 2 TB', 'Western Digital', 'Black SN770 WDS200T3X0E', 920, '2 TB, M.2 2280, NVMe PCIe 4.0', 'Placas madre con ranura M.2 NVMe'],
            ],
            'Placas madre' => [
                ['Placa madre Gigabyte A520M DS3H AM4 DDR4', 'Gigabyte', 'A520M DS3H', 330, 'Micro-ATX, chipset A520, socket AM4, memoria DDR4', 'Procesadores AMD Ryzen AM4 compatibles con BIOS'],
                ['Placa madre MSI B550M PRO-VDH WIFI AM4 DDR4', 'MSI', 'B550M PRO-VDH WIFI', 470, 'Micro-ATX, chipset B550, Wi-Fi integrado, DDR4', 'Procesadores AMD Ryzen AM4'],
                ['Placa madre Gigabyte B550M DS3H AC AM4 DDR4', 'Gigabyte', 'B550M DS3H AC', 450, 'Micro-ATX, chipset B550, Wi-Fi y Bluetooth, DDR4', 'Procesadores AMD Ryzen AM4'],
                ['Placa madre ASUS TUF Gaming B550M-PLUS AM4 DDR4', 'ASUS', 'TUF Gaming B550M-PLUS', 590, 'Micro-ATX, chipset B550, PCIe 4.0, DDR4', 'Procesadores AMD Ryzen AM4'],
                ['Placa madre Gigabyte B650M Gaming WiFi AM5 DDR5', 'Gigabyte', 'B650M Gaming WiFi', 690, 'Micro-ATX, chipset B650, Wi-Fi, memoria DDR5', 'Procesadores AMD Ryzen AM5'],
                ['Placa madre MSI PRO B650M-A WIFI AM5 DDR5', 'MSI', 'PRO B650M-A WIFI', 790, 'Micro-ATX, chipset B650, Wi-Fi 6E, DDR5', 'Procesadores AMD Ryzen AM5'],
                ['Placa madre ASUS Prime B650M-A WiFi II AM5 DDR5', 'ASUS', 'Prime B650M-A WiFi II', 850, 'Micro-ATX, chipset B650, Wi-Fi, DDR5', 'Procesadores AMD Ryzen AM5'],
                ['Placa madre ASRock B650M-HDV/M.2 AM5 DDR5', 'ASRock', 'B650M-HDV/M.2', 550, 'Micro-ATX, chipset B650, dos ranuras M.2, DDR5', 'Procesadores AMD Ryzen AM5'],
                ['Placa madre MSI PRO H610M-G DDR4 LGA1700', 'MSI', 'PRO H610M-G DDR4', 300, 'Micro-ATX, chipset H610, socket LGA1700, DDR4', 'Procesadores Intel Core LGA1700'],
                ['Placa madre Gigabyte B760M DS3H DDR4 LGA1700', 'Gigabyte', 'B760M DS3H DDR4', 490, 'Micro-ATX, chipset B760, socket LGA1700, DDR4', 'Procesadores Intel Core LGA1700'],
                ['Placa madre ASUS TUF Gaming B760-PLUS WiFi D4 LGA1700', 'ASUS', 'TUF Gaming B760-PLUS WiFi D4', 790, 'ATX, chipset B760, Wi-Fi, socket LGA1700, DDR4', 'Procesadores Intel Core LGA1700'],
                ['Placa madre MSI PRO B760M-A WIFI DDR5 LGA1700', 'MSI', 'PRO B760M-A WIFI', 650, 'Micro-ATX, chipset B760, Wi-Fi, socket LGA1700, DDR5', 'Procesadores Intel Core LGA1700'],
            ],
            'Periféricos' => [
                ['Mouse gamer Logitech G203 Lightsync 8000 DPI', 'Logitech', 'G203 Lightsync', 75, 'Mouse con cable, sensor de hasta 8000 DPI, iluminación RGB', 'PC y notebook con puerto USB'],
                ['Mouse gamer Logitech G305 Lightspeed inalámbrico', 'Logitech', 'G305 Lightspeed', 150, 'Sensor HERO de hasta 12 000 DPI, conexión inalámbrica Lightspeed', 'PC y notebook con puerto USB para receptor inalámbrico'],
                ['Mouse gamer Redragon Cobra M711 RGB 10 000 DPI', 'Redragon', 'Cobra M711', 90, 'Sensor óptico de hasta 10 000 DPI, 7 botones, RGB', 'PC con puerto USB'],
                ['Teclado mecánico Redragon Kumara K552 RGB español', 'Redragon', 'Kumara K552', 150, 'Formato TKL, switches mecánicos, iluminación RGB, distribución español', 'PC con puerto USB'],
                ['Teclado mecánico Royal Kludge RK X87 cableado', 'Royal Kludge', 'RK X87', 220, 'Formato TKL, switches mecánicos, conexión USB-C', 'PC con puerto USB'],
                ['Kit Logitech MK120 teclado y mouse USB español', 'Logitech', 'MK120', 65, 'Teclado de tamaño completo y mouse óptico con cable', 'PC o notebook con puertos USB'],
                ['Audífonos gamer HyperX Cloud Stinger 2', 'HyperX', 'Cloud Stinger 2', 220, 'Audífonos circumaurales con micrófono abatible y conexión 3.5 mm', 'PC, consola o dispositivo con entrada 3.5 mm'],
                ['Audífonos gamer Logitech G335 con micrófono', 'Logitech', 'G335', 250, 'Audífonos alámbricos ligeros, micrófono abatible, conexión 3.5 mm', 'PC y consolas con entrada 3.5 mm'],
                ['Mousepad gamer Redragon Flick XL P032 90 x 40 cm', 'Redragon', 'Flick XL P032', 70, 'Superficie textil extendida de 90 x 40 cm, base antideslizante', 'Compatible con mouse ópticos y láser'],
                ['Kit gamer Redragon S147 teclado mouse audífonos y pad', 'Redragon', 'S147', 170, 'Combo de cuatro piezas: teclado, mouse, audífonos y mousepad', 'PC con puertos USB y conectores de audio'],
                ['Kit gamer Antryx GC-5500 teclado mecánico y mouse', 'Antryx', 'GC-5500', 213, 'Teclado mecánico con switches y mouse gamer con iluminación', 'PC con puertos USB'],
            ],
            'Monitores' => [
                ['Monitor Teros TE-2417S 24 pulgadas FHD IPS 144 Hz', 'Teros', 'TE-2417S', 274, 'Pantalla IPS de 23.8 pulgadas, Full HD, 144 Hz, 1 ms', 'PC con salida HDMI o DisplayPort'],
                ['Monitor Xiaomi A24i 24 pulgadas FHD IPS 144 Hz', 'Xiaomi', 'A24i', 400, 'Pantalla IPS de 23.8 pulgadas, Full HD, hasta 144 Hz', 'PC con salida HDMI o DisplayPort'],
                ['Monitor LG UltraGear 24U411B-B 24 pulgadas 120 Hz', 'LG', '24U411B-B', 361, 'Pantalla de 24 pulgadas, Full HD, IPS, 120 Hz', 'PC con salida HDMI'],
                ['Monitor Gigabyte GS25F14 24.5 pulgadas FHD 144 Hz', 'Gigabyte', 'GS25F14', 340, 'Pantalla de 24.5 pulgadas, Full HD, IPS, 144 Hz', 'PC con salida HDMI o DisplayPort'],
                ['Monitor MSI PRO MP243W E14 24 pulgadas IPS 144 Hz', 'MSI', 'PRO MP243W E14', 410, 'Pantalla de 23.8 pulgadas, Full HD, IPS, 144 Hz', 'PC con salida HDMI o DisplayPort'],
                ['Monitor Samsung Essential S3 24 pulgadas Full HD', 'Samsung', 'Essential S3 LS24F330', 279, 'Pantalla de 24 pulgadas, Full HD, panel VA', 'PC con salida HDMI'],
                ['Monitor Teros TE-2415S 24 pulgadas IPS 120 Hz', 'Teros', 'TE-2415S', 266, 'Pantalla IPS de 23.8 pulgadas, Full HD, 120 Hz', 'PC con salida HDMI'],
                ['Monitor LG UltraGear 27 pulgadas FHD IPS 120 Hz', 'LG', '27U411A-B', 429, 'Pantalla IPS de 27 pulgadas, Full HD, 120 Hz', 'PC con salida HDMI'],
                ['Monitor portátil AOC 16T20 15.6 pulgadas Full HD', 'AOC', '16T20', 520, 'Pantalla portátil de 15.6 pulgadas, Full HD, conexión USB-C', 'Equipo con USB-C DisplayPort Alt Mode o HDMI'],
                ['Monitor Xiaomi Gaming G24i 24 pulgadas Full HD 200 Hz', 'Xiaomi', 'G24i', 397, 'Pantalla de 24 pulgadas, Full HD, hasta 200 Hz', 'PC con salida HDMI o DisplayPort'],
                ['Monitor Xiaomi G27Qi 27 pulgadas 2K 180 Hz', 'Xiaomi', 'G27Qi', 756, 'Pantalla IPS de 27 pulgadas, resolución QHD 2560 x 1440', 'PC con salida HDMI o DisplayPort'],
            ],
        ];

        DB::transaction(function () use ($catalogo): void {
            // Corrige dos productos existentes que estaban asignados a RAM.
            Producto::whereKey(37)->update(['categoria_id' => Categoria::where('nombre', 'Almacenamiento SSD y HDD')->value('id')]);
            Producto::whereKey(44)->update(['categoria_id' => Categoria::where('nombre', 'Tarjeta Grafica')->value('id')]);

            foreach ($catalogo as $nombreCategoria => $productos) {
                $categoriaId = Categoria::where('nombre', $nombreCategoria)->value('id');

                if (! $categoriaId) {
                    throw new \RuntimeException("No existe la categoría: {$nombreCategoria}");
                }

                $prefijo = match ($nombreCategoria) {
                    'Procesadores' => 'CPU',
                    'Tarjeta Grafica' => 'GPU',
                    'Memorias RAM' => 'RAM',
                    'Almacenamiento SSD y HDD' => 'STO',
                    'Placas madre' => 'MB',
                    'Periféricos' => 'PER',
                    'Monitores' => 'MON',
                };

                foreach ($productos as $indice => [$nombre, $marca, $modelo, $precio, $especificaciones, $compatibilidad]) {
                    $sku = sprintf('MLP-%s-%03d', $prefijo, $indice + 1);

                    $imagen = collect(Storage::disk('public')->files('productos'))
                        ->first(fn (string $ruta): bool => str_starts_with(basename($ruta), $sku . '.'));

                    $producto = Producto::firstOrCreate(
                        ['sku' => $sku],
                        [
                            'categoria_id' => $categoriaId,
                            'nombre' => $nombre,
                            'descripcion' => $nombre . '. ' . $especificaciones . '. Revisar la compatibilidad con el equipo antes de comprar.',
                            'precio' => $precio,
                            'stock' => 5,
                            'imagen' => $imagen,
                            'marca' => $marca,
                            'modelo' => $modelo,
                            'especificaciones' => $especificaciones,
                            'compatibilidad' => $compatibilidad,
                            'garantia' => 'Consultar condiciones de garantía con el vendedor.',
                        ]
                    );

                    if ($imagen) {
                        $producto->update(['imagen' => $imagen]);
                    }
                }
            }
        });
    }
}
