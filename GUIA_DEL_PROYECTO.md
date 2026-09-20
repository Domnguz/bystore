Esta guía explica ByteStore a partir del código que existe en este proyecto. Está escrita para empezar a leerlo sin conocimientos previos de Laravel.

La tienda usa Laravel 12 en el servidor y vistas Blade para construir las páginas. PHP ejecuta Laravel y MySQL/MariaDB guarda los datos. Tailwind da estilo a la tienda; AdminLTE aporta el diseño del administrador. JavaScript agrega interacción y Vite prepara los archivos CSS y JavaScript. Hay React instalado, pero las páginas principales se construyen con Blade: no son una aplicación completa de React.

El problema de las imágenes y su reparación

Las fotos del producto existente estaban guardadas en `storage/app/public/productos`. Sus rutas también estaban correctamente registradas en las tablas `productos` y `producto_imagenes`.

Faltaba `public/storage`. Es el enlace que permite que las fotos almacenadas en esa carpeta se sirvan mediante una dirección web. Al comprobar la URL de la foto principal, el servidor respondió HTTP 403. Después de ejecutar `php artisan storage:link`, la foto principal y la adicional respondieron HTTP 200 con tipo `image/webp`.

En este Windows Laravel creó una unión de directorio, llamada junction:

```text
public/storage -> storage/app/public
```

No es una segunda copia de las imágenes: las dos rutas permiten acceder al mismo contenido. Por eso basta con crear el enlace una vez; las imágenes que se guarden después quedan accesibles automáticamente.

También se agregó `@php artisan storage:link` a la lista `scripts.setup` de `composer.json`. El procedimiento `composer run setup` incluye ahora este paso. `composer install` por sí solo no ejecuta toda esa lista. No hace falta volver a ejecutar el setup completo para arreglar fotos: el comando específico es suficiente.

La relación entre archivo, base de datos y URL es esta:

```text
Archivo real:
storage/app/public/productos/archivo.webp

Valor en productos.imagen:
productos/archivo.webp

URL que pide el navegador:
http://127.0.0.1:8000/storage/productos/archivo.webp
```

La base de datos guarda el nombre y la ruta de la foto, no los bytes de la foto. Tener el registro del producto no basta si el archivo o su enlace público faltan. En este caso el archivo sí existía; no fue necesario volver a cargarlo.

Dónde está cada parte del proyecto

| Carpeta o archivo | Qué contiene y para qué sirve |
| --- | --- |
| `app/Http/Controllers` | Acciones del servidor: consultar productos, iniciar sesión, gestionar el carrito y guardar pedidos. |
| `app/Http/Controllers/Admin` | Acciones del panel: productos, usuarios, pedidos y estadísticas. |
| `app/Models` | Clases que representan los datos y sus relaciones con las tablas. |
| `app/Providers/AppServiceProvider.php` | Lugar para registrar servicios y comportamiento general. Actualmente no tiene lógica personalizada. |
| `routes/web.php` | Relaciona las direcciones de la tienda con las acciones que deben ejecutarse. |
| `routes/console.php` | Definición de comandos de consola. No controla las páginas del navegador. |
| `resources/views` | Plantillas Blade: HTML con instrucciones para mostrar datos de PHP. |
| `resources/views/layouts/app.blade.php` | Estructura compartida de la tienda: recursos, navegación, avisos, carrito lateral y pie de página. |
| `resources/views/partials` | Fragmentos reutilizables: barra de navegación y contenido del carrito. |
| `resources/views/admin` | Pantallas del administrador, organizadas por productos, categorías, usuarios y pedidos. |
| `resources/js/bytestore.js` | Interacción del menú, carrito, avisos y visibilidad de elementos en la portada. |
| `resources/js/app.jsx` | Importa la interacción de ByteStore y monta el componente React de ejemplo solamente si existe `#react-app`. |
| `resources/js/bootstrap.js` | Configura Axios, otra herramienta para peticiones HTTP. El carrito usa `fetch`. |
| `resources/js/app.js` | Otra entrada de JavaScript que importa `bootstrap.js`. |
| `resources/css/app.css` | Entrada de Tailwind y configuración de las fuentes que debe revisar para generar estilos. |
| `public/index.php` | Punto de entrada de las peticiones dinámicas a Laravel. |
| `public/img` y `public/videos` | Recursos fijos del diseño, como los logos y el video de portada. |
| `public/build` | Archivos CSS y JavaScript generados por `npm run build`. Se modifican los originales en `resources`, no estos resultados. |
| `public/storage` | Enlace público a las imágenes subidas. |
| `storage/app/public` | Archivos subidos que deben poder verse en el navegador. |
| `storage/app/private` | Disco local privado configurado en Laravel. No es el destino usado para las fotos de productos. |
| `storage/framework` | Archivos de ejecución, como sesiones, caché y vistas compiladas. |
| `storage/logs` | Registros de errores de la aplicación. |
| `database/migrations` | Cambios de estructura de la base de datos escritos como código. |
| `database/factories` y `database/seeders` | Herramientas para preparar datos de ejemplo o de pruebas. |
| `config` | Configuraciones: base de datos, archivos, sesiones, AdminLTE y otros servicios. |
| `bootstrap/app.php` | Inicia Laravel y registra rutas, middleware y manejo de excepciones. |
| `bootstrap/cache` | Archivos que Laravel genera para acelerar su arranque. |
| `lang` | Traducciones y mensajes. |
| `tests` | Pruebas automatizadas. Las de rendimiento comprueban paginación y cantidad de consultas. |
| `.env` | Valores propios de esta instalación: conexión a la base de datos, entorno, URL y controladores de sesión/caché. |
| `.env.example` | Plantilla para configurar otra instalación. |
| `composer.json` y `composer.lock` | Dependencias PHP, comandos del proyecto y versiones resueltas. |
| `package.json` y `package-lock.json` | Dependencias JavaScript, comandos de Vite y versiones resueltas. |
| `vendor` | Código instalado por Composer: Laravel y paquetes PHP. |
| `node_modules` | Código instalado por npm: Vite, React, Tailwind y otros paquetes. |
| `vite.config.js` | Indica qué archivos debe procesar Vite y qué herramientas utilizar. |
| `artisan` | Programa de consola de Laravel: se usa con `php artisan ...`. |
| `phpunit.xml` | Configuración de las pruebas, incluida la base SQLite en memoria. |
| `.gitignore`, `.gitattributes`, `.editorconfig` | Reglas de Git y formato del proyecto. |
| `README.md` | Presentación general e instrucciones originales del proyecto. |

Cómo llega una página al navegador

```text
Navegador solicita /productos
  -> public/index.php inicia Laravel
  -> routes/web.php encuentra la ruta
  -> ProductoController::index() consulta los datos
  -> Producto y Categoria leen MySQL mediante Eloquent
  -> productos.blade.php y layouts/app.blade.php producen HTML
  -> el navegador recibe HTML y carga CSS, JavaScript e imágenes
```

Esto se conoce como modelo-vista-controlador. El modelo representa datos; la vista presenta información; el controlador coordina la acción. Las rutas deciden qué controlador se debe ejecutar.

Por ejemplo, en `routes/web.php`:

```php
Route::get('/productos', [ProductoController::class, 'index']);
```

`get` significa que se está solicitando información. `/productos` es la dirección. `ProductoController::class` identifica la clase y `index` el método que se ejecutará.

En ese controlador:

```php
$productos = Producto::orderBy('id')->paginate(12);
$categorias = Categoria::all();
return view('productos', compact('productos', 'categorias'));
```

`$productos` es una variable. `Producto` representa la tabla de productos. `orderBy('id')` establece un orden estable y `paginate(12)` obtiene la página solicitada con un máximo de 12 productos. `Categoria::all()` obtiene las categorías. `compact(...)` crea un conjunto de datos con esos nombres. `view('productos', ...)` entrega esos datos a `resources/views/productos.blade.php`.

Las direcciones principales y sus acciones

| Dirección / método | Acción actual |
| --- | --- |
| `GET /` | Carga categorías y los cuatro productos más recientes con su categoría; muestra `inicio.blade.php`. |
| `GET /productos` | Catálogo paginado. |
| `GET /categoria/{id}` | Productos paginados de una categoría. `{id}` se sustituye por el identificador real. |
| `GET /login` | Formulario de acceso. |
| `POST /login` | Valida credenciales e inicia sesión. |
| `POST /logout` | Cierra sesión. |
| `GET /carrito` | Muestra el carrito de la sesión. |
| `POST /carrito/agregar/{id}` | Agrega una unidad y devuelve JSON. |
| `POST /carrito/eliminar/{id}` | Quita el producto del carrito. |
| `GET /carrito/contenido` | Devuelve el HTML del contenido del panel lateral. `/carrito/vista` tiene la misma respuesta. |
| `GET /checkout` | Muestra la confirmación de compra; requiere sesión iniciada. |
| `POST /checkout` | Registra el pedido y su detalle; requiere sesión iniciada. |
| `GET /profile` | Muestra los datos del usuario autenticado. |
| `GET /admin` | Muestra cantidades, ventas y pedidos recientes. |
| `/admin/productos`, `/admin/categorias`, `/admin/usuarios` | Grupos de rutas para listar, crear, editar y eliminar registros. |
| `/admin/pedidos` | Listado, detalle y acciones de estado de los pedidos. |

`Route::resource(...)` genera las rutas habituales de un mantenimiento de datos. En el controlador, `index` lista, `create` muestra el formulario, `store` guarda, `show` muestra un registro, `edit` prepara su edición, `update` guarda cambios y `destroy` elimina. En productos, `show` reutiliza la pantalla de edición.

Hay dos clases llamadas `ProductoController`. La que está directamente en `Controllers` atiende el catálogo público. La de `Controllers/Admin` administra los registros. El alias `AdminProductoController` de las rutas permite distinguirlas.

Qué representan los modelos

| Modelo | Tabla | Relaciones principales |
| --- | --- | --- |
| `Categoria` | `categorias` | Tiene muchos productos. |
| `Producto` | `productos` | Pertenece a una categoría y tiene muchas imágenes adicionales. |
| `ProductoImagen` | `producto_imagenes` | Pertenece a un producto. |
| `User` | `users` | Representa a la persona que inicia sesión y sus datos de perfil. |
| `Pedido` | `pedidos` | Pertenece a un usuario mediante `usuario_id` y tiene detalles. |
| `DetallePedido` | `detalle_pedidos` | Pertenece a un pedido y a un producto. |

`$fillable` enumera los campos que pueden asignarse mediante operaciones como `create($datos)` o `update($datos)`. No reemplaza la validación del formulario. `belongsTo` expresa «pertenece a» y `hasMany` expresa «tiene varios».

`Producto::with('categoria')` carga categorías en conjunto. Evita hacer una consulta nueva cada vez que una plantilla lee `$producto->categoria->nombre`. `findOrFail($id)` busca un registro y devuelve una respuesta 404 si no existe.

Cómo funciona la carga de productos e imágenes, paso a paso

1. `create()` obtiene las categorías y abre `admin/productos/create.blade.php`.
2. Esa plantilla contiene un formulario que envía `POST` a la ruta `productos.store`. El nombre corresponde al administrador, aunque no empiece por `admin.`.
3. `enctype="multipart/form-data"` permite enviar archivos además de los campos de texto.
4. `_form.blade.php` reúne los campos compartidos por creación y edición. El campo `imagen` corresponde a la foto principal. Los campos `imagenes[]` de `_galeria.blade.php` corresponden a las fotos adicionales.
5. `@csrf` introduce un token que Laravel comprueba al recibir el formulario. En edición, `@method('PUT')` indica que el POST debe tratarse como actualización.
6. `store(Request $request)` recibe los datos. `$request` contiene campos y archivos de la petición.
7. `validar()` comprueba categoría existente, nombre, precio, stock y archivos. Admite JPG, JPEG, PNG y WebP, con máximo de 5 MB por archivo y ocho fotos adicionales por envío.
8. `guardarFoto()` ejecuta `$foto->store('productos', 'public')`. `productos` es la subcarpeta; `public` es el disco configurado en `config/filesystems.php`.
9. Laravel asigna un nombre al archivo y devuelve una ruta relativa como `productos/archivo.webp`.
10. `Producto::create($datos)` guarda el registro principal. La ruta principal queda en `productos.imagen`.
11. `$producto->imagenes()->create(['ruta' => $ruta])` guarda cada foto adicional en `producto_imagenes` y la relaciona con su producto.
12. Se redirige al listado con el mensaje de éxito.

`DB::transaction(...)` agrupa los cambios de base de datos: si se produce una excepción, esos cambios se revierten. Los archivos no se revierten automáticamente. Por eso el controlador guarda las rutas nuevas en `$archivosNuevos` y las elimina en el `catch` si falla la operación.

El `&` de `&$archivosNuevos` permite que la función modifique la misma lista de archivos que revisará el código exterior. `try` contiene el trabajo que puede fallar; `catch (Throwable $error)` maneja el fallo y vuelve a lanzarlo después de limpiar los archivos nuevos.

En `update()`, si no eliges otra foto principal, se conserva la actual. Si eliges una nueva, la anterior se elimina después de que la actualización de base de datos haya terminado. Las fotos adicionales marcadas para eliminar se buscan dentro de la relación del producto, para no quitar imágenes de otro producto.

`destroy()` elimina las relaciones y el producto y después borra sus archivos. Si MySQL impide borrar un producto asociado a un pedido, devuelve un mensaje y conserva los archivos.

La plantilla muestra la imagen con:

```blade
<img src="{{ asset('storage/' . $producto->imagen) }}">
```

`$producto->imagen` contiene la ruta relativa. El punto `.` concatena cadenas en PHP. `asset(...)` construye la URL del recurso. Las llaves `{{ ... }}` imprimen el resultado escapado en el HTML. El navegador pide esa URL y el enlace público permite encontrar el archivo.

La imagen principal es la que muestran las tarjetas de catálogo y el listado. Las adicionales se ven en la galería de edición del administrador. El catálogo actual no tiene una ficha pública de producto que muestre toda la galería. Seleccionar únicamente fotos adicionales no llena automáticamente el campo de foto principal; son campos distintos y ambos son opcionales en la validación actual.

La vista previa de `_galeria.blade.php` utiliza `URL.createObjectURL(archivo)` para mostrar un archivo seleccionado antes de subirlo. Esa dirección temporal no es una foto guardada en el servidor. El guardado sucede al enviar el formulario. `URL.revokeObjectURL(...)` libera la dirección anterior cuando se cambia la selección.

Cómo leer una plantilla Blade

| Instrucción | Significado |
| --- | --- |
| `@extends('layouts.app')` | Usa la estructura común de la tienda. |
| `@extends('adminlte::page')` | Usa la estructura del panel AdminLTE. |
| `@section('contenido')` | Define el contenido que la plantilla base insertará en ese espacio. |
| `@yield('contenido')` | Lugar de la plantilla base donde se inserta la sección. |
| `@include('partials.navbar')` | Incluye un fragmento reutilizable. |
| `@foreach` / `@endforeach` | Repite HTML para cada elemento de una colección. |
| `@forelse` / `@empty` | Repite elementos y ofrece un mensaje cuando no hay ninguno. |
| `@if` / `@endif` | Muestra un bloque solamente si se cumple una condición. |
| `old('nombre', ...)` | Recupera lo escrito si hubo errores de validación, o utiliza el valor predeterminado. |
| `$errors` | Errores de validación para mostrarlos en el formulario. |
| `@push('css')`, `@push('js')` | Añade recursos a los espacios que ofrece la plantilla base. |
| `@vite(...)` | Carga CSS y JavaScript desde Vite durante desarrollo o desde la compilación disponible. |
| `{{ $productos->links() }}` | Muestra los enlaces de paginación. |

Tu archivo abierto: `resources/js/bytestore.js`

El archivo no guarda productos ni imágenes en MySQL. Se ejecuta en el navegador y controla la interacción visible de la tienda.

`iniciarByteStore()` busca elementos por su identificador: menú, botón del menú, categorías, diálogo del carrito, botones de abrir/cerrar, contador y contenedor de avisos. Un identificador como `abrirCarrito` debe coincidir con el `id` del elemento en Blade.

Para el menú, `addEventListener('click', ...)` indica qué hacer al pulsar. `classList.toggle('hidden', ...)` muestra u oculta el menú. También se cierra al hacer clic fuera, elegir un enlace, pulsar Escape o cambiar el tamaño de pantalla. Los atributos `aria-expanded` y `aria-label` describen el estado para las tecnologías de accesibilidad.

El carrito lateral es un elemento HTML `dialog`. `showModal()` lo abre y `close()` lo cierra. Mientras está abierto, se bloquea el desplazamiento del fondo. Al cerrarse se restaura el foco al elemento que lo abrió, cuando todavía es visible y accesible.

`mostrarAviso(mensaje)` coloca el mensaje con `textContent`, lo muestra y programa su ocultación. Usar `textContent` hace que el mensaje se trate como texto, no como HTML.

Para agregar al carrito se intercepta el evento `submit` de `.form-agregar-carrito`:

```text
Clic en agregar
  -> preventDefault() evita el envío tradicional con recarga
  -> se desactiva temporalmente el botón
  -> fetch POST /carrito/agregar/{id} envía el formulario y el token CSRF
  -> CarritoController actualiza la sesión y devuelve JSON
  -> JavaScript actualiza el contador
  -> fetch GET /carrito/contenido obtiene el HTML actualizado
  -> se reemplaza el contenido del panel y se abre el carrito
  -> finally vuelve a habilitar el botón
```

`async` permite esperar operaciones de red dentro de la función. `await` espera el resultado sin bloquear toda la interfaz. `response.ok` indica si la respuesta HTTP fue satisfactoria. `response.json()` lee datos estructurados; `response.text()` lee el fragmento HTML. `try/catch/finally` separa el camino exitoso, los errores y la limpieza que debe hacerse en ambos casos.

`form.dataset.enviando` evita envíos simultáneos repetidos. El mensaje de error distingue entre no haber agregado el producto y haberlo agregado sin poder actualizar el panel, para no inducir a repetir una compra ya registrada en el carrito.

`configurarVisibilidadPortada()` usa `getBoundingClientRect().bottom` para saber si la portada ya salió de la pantalla y ajustar la visibilidad de la navegación y del botón de carrito. Escucha `scroll`, `resize` y `pageshow`; agrupa las actualizaciones con `requestAnimationFrame` y evita programar varias a la vez mediante la variable `pendiente`. Solo cambia las clases cuando cambia la visibilidad. En páginas sin portada deja los elementos visibles. `iniciarPagina()` inicia ambas funciones cuando el documento está listo, usando `DOMContentLoaded` si es necesario.

El carrito, el acceso y los pedidos

El carrito se guarda en `session('carrito')`, una colección de entradas por identificador de producto con nombre, precio, imagen y cantidad. En esta instalación las sesiones utilizan archivos. La cookie del navegador identifica la sesión; el contenido del carrito se conserva en el servidor. Agregar al carrito no crea todavía un pedido.

`LoginController` valida correo y contraseña, llama a `Auth::attempt(...)` y regenera la sesión al entrar. Después elige una redirección según `rol`. Cerrar sesión invalida la sesión y renueva el token CSRF. `ProfileController` obtiene el usuario actual mediante `Auth::user()`.

`CheckoutController` comprueba que el carrito tenga productos y que exista stock, crea `Pedido`, crea sus `DetallePedido`, descuenta stock y vacía el carrito. El código revisado registra pedidos, pero no ejecuta cobros con una pasarela de pago.

`DashboardController` cuenta productos, categorías y pedidos, suma ventas según estados y carga los últimos cinco pedidos con sus usuarios. `Admin/PedidoController` lista pedidos, muestra su detalle y modifica su estado.

Hay detalles del código actual que conviene distinguir del funcionamiento deseado: las rutas administrativas tienen `auth`, que exige haber iniciado sesión, pero no una comprobación de rol de administrador en ese grupo. Además, `CheckoutController::procesar()` descuenta stock y `Admin/PedidoController::aprobar()` vuelve a descontarlo; usar ambos pasos sobre un mismo pedido puede descontar dos veces. Se documentan aquí porque afectan a cómo funciona realmente el proyecto; la reparación de imágenes no cambió estos procesos.

Cómo trabajar en este proyecto

Para cambiar textos y distribución de páginas, empieza en `resources/views`. Para cambiar el menú y el carrito interactivo, ve a `resources/js/bytestore.js`. Para cambiar qué datos se consultan o guardan, revisa el controlador de la ruta. Para cambiar relaciones o campos asignables, revisa el modelo. Para cambiar la estructura de tablas, prepara una migración.

Los comandos habituales, ejecutados desde la raíz, son:

```powershell
php artisan serve
npm run dev
```

Se ejecutan en terminales separadas: el primero sirve Laravel y el segundo sirve recursos con actualización durante desarrollo. MySQL debe estar activo en XAMPP. Si usas el servidor de `artisan serve`, Apache no es quien atiende ese puerto.

```powershell
npm run build
php artisan route:list
php artisan storage:link
php artisan test --filter=CatalogPerformanceTest
```

Esos comandos, respectivamente, compilan recursos, enumeran rutas, crean el enlace de archivos públicos y ejecutan las pruebas de catálogo. El archivo `public/hot` indica a Laravel un servidor Vite de desarrollo; mientras esté activo, las páginas usan ese servidor.

No es necesario ejecutar migraciones para la reparación de imágenes. En particular, las migraciones actuales no incluyen la creación de `productos`, aunque la tabla sí existe en la base local. Por ello no debe suponerse que las migraciones por sí solas reconstruyen toda esta base en otra instalación. Tampoco conviene usar comandos de reinicio de base de datos para resolver fotos que no aparecen.

Para aprender el código, sigue este orden con un caso concreto: `routes/web.php`, el método `index()` del controlador público de productos, `app/Models/Producto.php`, `resources/views/productos.blade.php`, `resources/views/layouts/app.blade.php` y finalmente `resources/js/bytestore.js`. Después recorre el formulario del administrador y su método `store()` siguiendo el proceso de archivos explicado arriba.
