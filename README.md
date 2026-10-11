# Desafío Iberá — gestión de efluentes

Aplicación Laravel con vistas Blade para propietarios e inspectores, carga privada de informes PDF, evaluación asistida, dictamen humano y consultas climáticas a un servicio Python/FastAPI.

## Preparar una copia nueva

Requisitos: PHP 8.4 con las extensiones exigidas por Composer, Composer, Node.js compatible con Vite 8, MySQL y Python 3.12. El entorno local verificado utiliza MySQL 8.4. Las versiones PHP están fijadas en `composer.lock`. El archivo `requirements.text` fue eliminado; las dependencias Python se indican abajo y aún no tienen versiones fijadas para reproducir una instalación nueva.

```powershell
composer install
npm ci
```

Solo si todavía no existe `.env`, copiar `.env.example` y ejecutar `php artisan key:generate`. Configurar las credenciales de una base propia ya creada. No sobrescribir un `.env` existente ni compartirlo en Git. Para esta configuración, usar una sola línea `DB_CONNECTION=mysql`.

```powershell
php artisan config:clear
php artisan migrate --no-interaction
php artisan db:seed --class=LimiteEfluenteSeeder --no-interaction
npm run build
```

No utilizar `migrate:fresh` en una base con datos que se quieran conservar. Los PDF requieren un directorio temporal de PHP escribible, `upload_max_filesize` de al menos 10M y `post_max_size` mayor que el archivo (por ejemplo 12M).

Preparar Python en un entorno local aislado:

```powershell
py -3.12 -m venv .venv
.\.venv\Scripts\python.exe -m pip install fastapi uvicorn pandas requests
```

## Ejecutar localmente

En terminales separadas, desde la raíz:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

```powershell
.\.venv\Scripts\python.exe -m uvicorn api:app --app-dir motor_riesgo --host 127.0.0.1 --port 8001
```

```powershell
php artisan schedule:work
```

Configurar `CLIMATE_API_URL=http://127.0.0.1:8001`. El programador consulta cada seis horas (00, 06, 12 y 18 UTC). Procesa en forma síncrona: este flujo no requiere un trabajador de colas adicional. Las terminales deben mantenerse abiertas. No se ha configurado un servicio permanente ni un despliegue público.

Para consultar sin guardar: `php artisan clima:consultar -- -28.54 -57.17`. Para actualizar detecciones de todos los establecimientos: `php artisan clima:actualizar` (sí guarda resultados). `php artisan schedule:list` permite ver la programación.

El registro público crea propietarios. Las cuentas de inspector/admin_gobierno deben provisionarse por el procedimiento de seguridad del equipo; no hay una contraseña administrativa compartida en este repositorio.

## Rutas, permisos y pruebas

Ver [RUTAS_Y_PRUEBAS.md](RUTAS_Y_PRUEBAS.md) para el contrato HTTP, campos, roles, caché, errores y la lista de aceptación. Importar [Desafio-Ibera.postman_collection.json](Desafio-Ibera.postman_collection.json) para las 41 solicitudes de prueba. Requiere cuentas locales y un PDF ficticio; contiene operaciones que guardan datos.

```powershell
php artisan test --compact
.\.venv\Scripts\python.exe -m unittest discover -s motor_riesgo -p "test_*.py"
npm run build
```

La suite habitual usa SQLite en memoria y respuestas HTTP controladas. Dos pruebas adicionales de MySQL/API real requieren configuración explícita; no se ejecutan por defecto. También existe una prueba optativa con Newman que ejecuta las 35 solicitudes del recorrido principal de Postman contra un servidor real y una base temporal aislada; ver las instrucciones en RUTAS_Y_PRUEBAS.md.

## Alcance actual

- Login/registro, sesiones y autorización por rol/propietario.
- Alta, consulta y edición de establecimientos; permiso de vuelco por gobierno.
- PDF privado, registro mediante los servicios de extracción y evaluación del equipo, historial y descarga autorizada.
- Dictamen humano con autor, fecha y fundamento, sin sobrescritura desde el formulario.
- Catálogo de límites en caché con vencimiento e invalidación por cambios.
- Consulta climática, detecciones periódicas sin duplicar cada ciclo y revisión por gobierno.

Las operaciones de negocio usan rutas web con sesión; no hay una API REST de negocio paralela bajo `/api`. La alerta climática es preventiva, no un dictamen legal ni un modelo entrenado de inteligencia artificial. El umbral climático debe validarse con especialistas.

El 10 de octubre se integró main en dev/melina y se corrigió la compatibilidad de usuarios y pruebas. La suite integrada aprobó 152 pruebas (558 comprobaciones), con 3 optativas omitidas. El dashboard incluido por el equipo todavía muestra datos fijos de demostración: no deben presentarse como cumplimiento, permisos o alertas reales. Completar la aceptación conjunta con las pantallas definitivas. Si se requiere hosting, también configurar procesos persistentes y programador. El extractor actual trabaja sobre texto del PDF; sus nuevas etiquetas no incorporan reconocimiento de imágenes escaneadas.
