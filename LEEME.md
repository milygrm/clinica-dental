# Historia Clínica Odontológica (PHP + MySQL + Bootstrap + AJAX)

Usuario inicial: **admin** · Contraseña: **admin123**  (cámbiela cuando termine la entrega)

## Estructura
- `index.php` – inicio de sesión (AJAX)
- `dashboard.php` – panel principal con estadísticas
- `pacientes.php` – registro, búsqueda, edición y eliminación de pacientes (AJAX)
- `historia.php` – historia clínica del paciente + registro de atenciones (AJAX)
- `historia_pdf.php` – genera la historia clínica en PDF
- `procedimientos.php` – catálogo de procedimientos del odontólogo (AJAX)
- `api/` – endpoints JSON usados por AJAX
- `includes/config.php` – **datos de conexión a MySQL**
- `database.sql` – tablas y datos iniciales

## Publicar en InfinityFree (gratis, soporta PHP + MySQL)
1. Cree cuenta en infinityfree.com y un hosting (subdominio, p. ej. `miclinica.rf.gd`).
2. En el panel (Client Area → Control Panel) abra **MySQL Databases**, cree una base de datos y anote:
   host (ej. `sql204.infinityfree.com`), nombre (ej. `if0_43399442_12345clinica`), usuario (`if0_43399442`) y contraseña.
3. Abra **phpMyAdmin**, seleccione esa base de datos → pestaña **Importar** → suba `database.sql`.
4. Edite `includes/config.php` con los datos del paso 2 (DB_HOST, DB_NAME, DB_USER, DB_PASS).
5. Suba **todo el contenido** de la carpeta (no la carpeta en sí) a `htdocs/` con el Administrador de archivos
   o con FileZilla (FTP). `index.php` debe quedar directamente dentro de `htdocs/`.
6. Entre a `http://su-subdominio/` e inicie sesión. Ese enlace es el que se entrega en el Classroom.

> Funciona igual en FreeHostia, AwardSpace o 000webhost: solo cambian los datos de MySQL del paso 2.
> (Vercel no ejecuta PHP de forma nativa; use uno de los hostings anteriores.)

## Probar en su computador (opcional)
Con XAMPP: copie la carpeta a `htdocs`, cree la base `clinica_dental` en phpMyAdmin, importe `database.sql`
(el config por defecto ya usa root sin contraseña) y abra `http://localhost/clinica_dental/`.
