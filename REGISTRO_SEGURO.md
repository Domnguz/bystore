# Registro de clientes con DNI y verificación de correo

Implementación del 3 de octubre de 2026. Verificación por código actualizada el 13 de octubre de 2026.

El registro exige nombres y apellidos en campos separados, DNI de 8 dígitos, correo, contraseña confirmada, aceptación del aviso de privacidad y un CAPTCHA visual. Los nombres admiten hasta 100 caracteres y los apellidos hasta 150; ambos son obligatorios y se guardan en la cuenta. El DNI se conserva como texto para mantener los ceros iniciales. La validación del servidor y el índice único existente de `users.dni` impiden asociar el mismo número a varias cuentas. El correo se normaliza a minúsculas.

Al registrarse, el cliente inicia sesión y recibe un código de 6 dígitos que vence en 10 minutos. Lo escribe en la pantalla «Verifica tu correo» y pulsa «Verificar mi correo». Puede ver el catálogo, editar su perfil y consultar sus pedidos, pero no abrir ni enviar el checkout hasta verificar el correo y completar un DNI único. Las cuentas antiguas deben completar esos pasos antes de crear pedidos; su historial se conserva. El acceso administrativo conserva su protección por rol.

El cliente no puede sustituir su DNI desde el perfil. Las correcciones deben revisarse por el negocio y realizarse desde la administración. Si el DNI ya está registrado, el formulario dirige a la recuperación por correo o al contacto del negocio. Conocer el DNI no permite recuperar una contraseña ni consultar el correo de otro cliente. No se fusionan ni eliminan cuentas existentes automáticamente.

Al cambiar el correo desde el perfil, se anula la verificación anterior y se envía otro código. El código anterior deja de ser válido. Si un administrador modifica el correo, también se anula su verificación; el cliente puede solicitar un código desde su cuenta.

La aceptación del uso del DNI guarda su fecha en `users.dni_consent_at`. La migración agrega un campo nullable; no atribuye consentimiento a cuentas antiguas ni modifica sus datos anteriores. El DNI y esa fecha se excluyen de la serialización pública del modelo de usuario.

## Protección del registro

- CAPTCHA local PNG de seis dígitos, generado con números aleatorios criptográficos, con respuesta almacenada como hash en la sesión. Vence en 10 minutos y se consume al intentar registrar la cuenta, incluso si el código o los demás datos son incorrectos. No requiere GD ni un proveedor externo.
- La renovación del CAPTCHA conserva los datos que ya escribió el cliente cuando JavaScript está habilitado. La sesión mantiene hasta cinco desafíos para permitir varias pestañas. Las rutas de registro serializan solicitudes de una misma sesión para impedir reutilización simultánea del desafío.
- Un campo oculto detecta formularios completados indiscriminadamente por bots.
- Hasta 6 intentos de registro por minuto y 20 por hora por IP. Hasta 30 cargas de CAPTCHA por minuto por IP. Los límites también afectan a usuarios que comparten una IP.
- Reenvíos de verificación: 1 por minuto y 6 por hora por usuario, con un límite adicional de 20 por hora por IP. Las modificaciones del perfil tienen un límite de 6 por minuto y 20 por hora por usuario.
- El código se genera con `random_int`, conserva ceros iniciales y se guarda en la caché como HMAC-SHA256 con la clave de la aplicación, asociado a la cuenta y a su correo actual. Nunca se muestra el código correcto en la página ni se conserva como texto en la caché o en los datos del formulario tras un error.
- Cada código permite hasta 5 intentos incorrectos, vence en 10 minutos y se elimina al verificar. Los intentos se comparten entre sesiones. La verificación admite hasta 6 solicitudes por minuto y 30 por hora por usuario, además de 30 por minuto por IP. Los bloqueos de caché serializan envíos y verificaciones de una cuenta; la verificación bloquea la fila del usuario para comprobar el correo actual.
- Un reenvío exitoso sustituye el código anterior. Si falla el transporte del correo, la cuenta o el cambio de perfil se conservan y la pantalla permite volver a solicitar el código; un reenvío fallido conserva el código anterior hasta su vencimiento.

Estas medidas reducen registros duplicados y automatizados. No comprueban que el DNI exista ni que quien lo escribe sea su titular. No hay integración con RENIEC. El CAPTCHA local es una barrera básica y no impide todos los bots.

## Entrega de correo

El entorno local está configurado con `MAIL_MAILER=smtp`. La verificación utiliza el mismo transporte de correo existente. Las pruebas automáticas usan notificaciones simuladas y el transporte `array`; no envían correos reales ni comprueban su llegada a la bandeja de entrada.

Si se usa `MAIL_MAILER=log`, los mensajes se guardan en `storage/logs/laravel.log`, sin llegar al cliente. En desarrollo, el código de ese mensaje permite comprobar el flujo con la sesión de su cuenta. Los registros contienen códigos privados y deben mantenerse restringidos.

Con los transportes `log` y `array`, la pantalla de verificación muestra que el envío no está disponible y los avisos de registro, reenvío y cambio de correo no afirman que se entregó un mensaje. La cuenta permanece sin verificar. Al activar un transporte de envío real, vuelve a mostrarse el formulario de reenvío.

Para configurar Gmail en otro entorno, usa servidor `smtp.gmail.com`, esquema `smtp`, puerto 587 y nombre remitente ByteStore.

1. Activa la verificación en dos pasos de la cuenta remitente y crea una [contraseña de aplicación de Google](https://support.google.com/accounts/answer/185833?hl=es-419). Usa esta credencial para SMTP.
2. Escribe tu dirección de Gmail en `MAIL_USERNAME` y `MAIL_FROM_ADDRESS`. Guarda la contraseña de aplicación en `MAIL_PASSWORD`, sin los espacios de presentación.
3. Cambia `MAIL_MAILER=log` a `MAIL_MAILER=smtp` y limpia la configuración con el comando indicado más abajo.

Los parámetros siguen la [configuración SMTP documentada por Google](https://support.google.com/mail/answer/7104828?hl=es-419).

Para enviar mensajes reales, configura en `.env` los valores de tu proveedor:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_correo@gmail.com
MAIL_PASSWORD=tu_contrasena_de_aplicacion
MAIL_FROM_ADDRESS=tu_correo@gmail.com
MAIL_FROM_NAME="ByteStore"
```

Usa el esquema y puerto que indique el proveedor; por ejemplo, un proveedor que exija TLS directo puede requerir `smtps` y el puerto 465. Configura `APP_URL` con la dirección real de la tienda y HTTPS en producción. No compartas las credenciales en el chat ni las subas al repositorio.

Después de cambiar la configuración:

```powershell
php artisan config:clear
```

El envío de verificación es síncrono: no requiere un trabajador de colas. Comprueba con un correo propio que lleguen el código inicial y un reenvío, y que puedas escribir el último código para verificar la cuenta. La recuperación de contraseña conserva su enlace; revisa que abra el dominio correcto.

## Comprobaciones

`tests/Feature/AccountRegistrationTest.php` verifica el registro, DNI duplicado también a nivel de base de datos, conservación de ceros iniciales, CAPTCHA inválido/vencido/reutilizado, códigos válidos, incorrectos y vencidos, límite de intentos incluso entre sesiones y con caché en base de datos, invalidación del código anterior al reenviar o cambiar el correo, aislamiento entre usuarios, correo HTML y texto, bloqueo del checkout, cuentas antiguas, DNI inmutable y fallos del transporte de correo. La suite existente de tienda y pagos comprueba que los clientes con datos completos y correo verificado conservan el flujo de compra.

```powershell
php artisan test --compact
npm run build
```
