# Rutas y pruebas — entrega de Persona 2

Estado verificado contra la rama `dev/melina`, 9 de octubre de 2026. El PR #4 ya fue integrado en `main`. Este documento describe el código disponible; debe revisarse cuando se integre la autenticación/OCR o el frontend de otras ramas.

## Arquitectura acordada

Laravel sirve vistas Blade y autentica con sesión/cookies. Las operaciones de negocio están en `routes/web.php`, protegidas por autenticación, autorización por rol/propietario y CSRF. No se añadió una segunda API REST para esas operaciones. `routes/api.php` contiene únicamente `GET /api/estado`.

El plan inicial enumeraba `/api/establecimientos`, `/api/establecimientos/{id}/analisis`, `/api/permisos-vuelco` y `/api/alertas-climaticas`. Sus funciones se cubren con las rutas web de abajo, pero **no existen esos contratos REST JSON**. Si la rúbrica exige las URL originales, el equipo debe confirmar esa diferencia antes de presentar.

Python es un servicio separado: Laravel consulta `GET /api/v1/alerta-climatica` con latitud y longitud y recibe JSON. No otorga permisos de vuelco ni decide el dictamen del laboratorio.

## Contrato HTTP actual

En las páginas, GET devuelve HTML. Los guardados correctos devuelven 302 con `Location` y mensajes de sesión. `Accept: application/json` permite recibir errores estructurados; no convierte las respuestas correctas en JSON. Sin ese encabezado, una validación suele redirigir con errores de formulario.

Todas las mutaciones web necesitan cookie de sesión y token CSRF, incluso login y registro. Tras iniciar/cerrar sesión se renueva el token: leer una página con formulario antes del siguiente guardado.

| Método | Ruta | Acceso / propósito | Éxito |
|---|---|---|---|
| GET | `/` | Público, entrada a portales | 200 HTML |
| GET | `/api/estado` | Público, comprobación de API | 200 JSON |
| GET | `/login` | Invitado, formulario y token CSRF | 200 HTML |
| POST | `/login` | Invitado, email y contraseña; 6 intentos/minuto | 302 |
| POST | `/registro` | Invitado; crea exclusivamente propietario; 6/minuto | 302 |
| POST | `/logout` | Usuario autenticado | 302 |
| GET | `/dashboard` | Redirige según rol | 302 |
| GET | `/gestion` | Inspector/admin_gobierno, análisis pendientes | 200 HTML |
| GET | `/establecimientos` | Propietario ve los suyos; gobierno ve todos | 200 HTML |
| GET | `/establecimientos/crear` | Propietario, formulario | 200 HTML |
| POST | `/establecimientos` | Propietario, crear asociado a su usuario | 302 |
| GET | `/establecimientos/{id}` | Dueño o gobierno | 200 HTML |
| GET | `/establecimientos/{id}/editar` | Solo dueño | 200 HTML |
| PUT | `/establecimientos/{id}` | Solo dueño, actualizar | 302 |
| POST | `/establecimientos/{id}/permiso` | Inspector/admin_gobierno | 302 |
| GET | `/establecimientos/{id}/clima` | Dueño o gobierno; 20/minuto | 200 HTML |
| GET | `/analisis` | Propietario, formulario de carga | 200 HTML |
| POST | `/analisis` | Propietario del establecimiento; 10/minuto | 302 |
| GET | `/historial` | Propietario ve los suyos; gobierno ve todos | 200 HTML |
| GET | `/analisis/{id}` | Dueño o gobierno, detalle | 200 HTML |
| GET | `/analisis/{id}/pdf` | Dueño o gobierno, descarga privada | 200 PDF |
| POST | `/analisis/{id}/revision` | Inspector/admin_gobierno, dictamen único | 302 |
| GET | `/alertas-climaticas` | Gobierno; filtro `estado=pendientes/revisadas/todas` | 200 HTML |
| PATCH | `/alertas-climaticas/{id}` | Gobierno, registrar revisión de detección | 302 |
| GET | Python: `/api/v1/alerta-climatica` | Coordenadas válidas; servicio local | 200 JSON / 503 |

No hay eliminación de establecimientos ni de análisis en este flujo. Las rutas por ID ocultan recursos de otro propietario con 404. Gobierno puede consultar establecimientos, pero no editarlos por esa ruta.

## Datos de entrada

- **Registro:** `nombre`, `apellido`, `email` único, `password` mínimo 12 caracteres y `password_confirmation`. Nunca se acepta elegir el rol de gobierno desde este formulario.
- **Establecimiento:** `nombre`, `rubro` (`hotel`, `gastronomico`, `comercio`), `ubicacion`, `tipo_destino_vuelco` (`cursos_agua`, `laguna`, `conducto_pluvial`, `absorcion_suelo`), `latitud` [-90,90], `longitud` [-180,180], `capacidad_maxima` y `capacidad_biodigestor` enteros positivos. `cuit` opcional, exactamente 11 dígitos (validación de formato).
- **Análisis:** multipart con `establecimiento_id` y `pdf`, PDF de hasta 10 MB. Fecha, laboratorio y mediciones provienen del servicio extractor. El controlador almacena el archivo privado y llama a `RegistrarAnalisisService` una sola vez. El dictamen final permanece `Pendiente`.
- **Permiso:** `numero_expediente`, `fecha_emision`, `fecha_vencimiento` (YYYY-MM-DD, vencimiento no anterior a emisión), `estado` (`Activo`, `Vencido`, `Revocado`), `estado_tramite` (`iniciado`, `pendiente_documentacion`, `en_evaluacion`, `resuelto`), `tipo_destino_vuelco`. Actualiza el permiso del establecimiento; no genera un historial de renovaciones.
- **Dictamen:** `resultado_final` (`Aprobado` o `Rechazado`) y `observaciones_revision` obligatorias, máximo 2000 caracteres. El servidor asigna autor y fecha. No admite sobrescribir una revisión ya cerrada.
- **Clima Python:** query `latitud` y `longitud` numéricas y finitas. Puede responder 503 si no obtiene un pronóstico completo. La página Laravel sigue mostrando 200 HTML con la indisponibilidad visible.

Un PDF válido pero sin texto legible se registra como `observado` con advertencias y necesita revisión humana. No es un error de validación 422. La extracción de PDF escaneado y las mejoras de autenticación están siendo trabajadas por Catalina.

## Errores y límites

| Estado | Situación |
|---|---|
| 401 | Ruta protegida sin sesión y `Accept: application/json`; en navegación normal redirige al login |
| 403 | Rol sin permiso para la operación |
| 404 | Recurso inexistente, ajeno o PDF privado ausente |
| 405 | Método HTTP no definido para esa ruta |
| 419 | Sesión/token CSRF inválido en una mutación web |
| 422 | Campos inválidos, PDF faltante/excesivo o dictamen ya cerrado, con petición JSON |
| 429 | Se excedió el límite de solicitudes; esperar antes de reintentar |
| 503 | Python sin pronóstico completo o almacenamiento PDF no disponible |

No existe un caso de negocio que fuerce 400: no se inventa un endpoint para completar una lista de códigos. Los errores de formulario usan 422 cuando se solicita JSON. Un 200 solo prueba que la página respondió; no que un pronóstico externo esté disponible.

## Colección Postman

Importar `Desafio-Ibera.postman_collection.json` (formato 2.1, 41 solicitudes). Configurar en valores locales las variables `base_url`, `python_url`, `propietario_email`, `propietario_password`, `inspector_email` e `inspector_password`. Las contraseñas están vacías deliberadamente; no exportarlas ni subirlas a Git. Usar cuentas y datos de prueba en una base local.

1. Mantener activado el administrador de cookies de Postman. Vaciar las cookies del servidor antes del primer recorrido y usar siempre el mismo host (no alternar localhost y 127.0.0.1).
2. Desactivar el seguimiento automático de redirecciones. La colección incluye `followRedirects: false` por solicitud para comprobar los 302 originales.
3. Ejecutar las carpetas **01, 02 y 03 en orden**. Antes de hacerlo, seleccionar un PDF ficticio en la solicitud 19, campo `pdf`. Sin archivo, ese paso y los siguientes de análisis fallarán.
4. La colección extrae tokens de los formularios HTML y guarda los IDs creados desde `Location`. No configurar manualmente `Content-Type` en multipart: Postman añade el separador del archivo.
5. La cuenta inspectora debe existir y tener el rol asignado por el equipo. No se crean credenciales administrativas desde la colección ni se cambia la seguridad de Cata.
6. Carpetas **04 y 05 son manuales y opcionales**, no ejecutar toda la colección con un solo Run. La 04 necesita sesión de inspector y un `alerta_id` existente. La 05 necesita estar sin sesión y `nuevo_email`/`nueva_password`; crea otro propietario.
7. Carpeta 06 verifica Python; requiere que el servicio esté iniciado. Un 503 se reconoce como indisponibilidad, no como pronóstico exitoso.

El recorrido crea un establecimiento, un análisis y un permiso ficticios, y registra un dictamen que luego no puede sobrescribirse. No ejecutarlo sobre datos reales. Se puede repetir con nuevos recursos; no elimina los de ejecuciones anteriores. Si cambia el login del equipo, actualizar primero las solicitudes de sesión y extracción de token.

## Caché del catálogo normativo

`LimiteEfluente::catalogoParaEvaluacion()` conserva arrays de los límites durante 3600 segundos. La clave incluye la conexión y la base para separar MySQL de SQLite. El evaluador elige la columna correspondiente al destino sobre ese catálogo.

Los eventos de creación/guardado/eliminación olvidan esa entrada, también después de confirmar una transacción. Las lecturas dentro de una transacción van a la base y no publican datos sin confirmar. El seeder invalida expresamente para funcionar incluso con eventos desactivados. No se vacía la caché de toda la aplicación.

Las modificaciones por SQL directo o actualizaciones masivas omiten eventos de Eloquent: deben ejecutar `LimiteEfluente::olvidarCatalogo()` después de confirmar el cambio, o esperar el vencimiento de una hora. El caché no constituye un historial normativo; las modificaciones legales y su versionado requieren un acuerdo del equipo. La implementación no cambia umbrales, reglas NDC ni las decisiones del servicio de Cata.

## Verificación y entrega

Pruebas deterministas, con base temporal configurada en `phpunit.xml`:

```powershell
php artisan test --compact
python -m unittest discover -s motor_riesgo -p "test_*.py"
npm run build
php artisan route:list --except-vendor
php artisan schedule:list
```

Las dos pruebas de MySQL/Python real son optativas y están separadas para evitar depender de la red o tocar la base local en cada ejecución.

### Recorrido HTTP automatizado con Newman

El 9 de octubre se ejecutaron correctamente las **35 solicitudes de las carpetas 01–03** con Newman 6.2.3, el ejecutor de colecciones Postman. Se verificaron sesión real, cookies, rechazo por CSRF, creación y actualización del establecimiento, carga/descarga de PDF, historial, permiso y dictamen. La prueba también comprueba los datos persistidos, el propietario y el inspector que revisó. No se ejecutaron en esta corrida las carpetas opcionales 04–06 ni la aplicación gráfica de Postman.

`tests/Feature/EntregaPostmanTest.php` crea una base SQLite en archivo, cuentas ficticias, un PDF y un servidor en un puerto libre, todo temporal. El almacenamiento queda aislado; al terminar detiene el servidor y elimina la base, los archivos y credenciales de prueba. No utiliza MySQL ni las cuentas locales del usuario. La llamada climática apunta deliberadamente a un servicio no disponible para que la prueba no dependa de Internet; las respuestas climáticas válidas y las detecciones persistidas tienen sus propias pruebas.

Esta prueba es optativa porque necesita Node.js y Newman instalados fuera de las dependencias del proyecto. Indicar las rutas reales de los ejecutables y correrla así:

```powershell
$env:IBERA_NODE_BINARY = 'C:\Program Files\nodejs\node.exe'
$env:IBERA_NEWMAN_CLI = 'C:\ruta\a\node_modules\newman\bin\newman.js'
php artisan test --compact tests/Feature/EntregaPostmanTest.php
Remove-Item Env:IBERA_NODE_BINARY
Remove-Item Env:IBERA_NEWMAN_CLI
```

En la computadora de Melina, Newman se instaló en `C:\Users\melis\AppData\Local\Temp\ibera-newman`; esa ubicación es temporal y no se sube a Git. Su instalación no modificó `package.json`, `package-lock.json` ni Composer. Para uso manual de Postman siguen siendo necesarios las cuentas locales y el PDF elegidos por el equipo.

Aceptación conjunta antes de entregar:

- [ ] Abrir la interfaz final en escritorio y móvil.
- [ ] Entrar como propietario y crear/editar su establecimiento.
- [ ] Subir el PDF ficticio, ver sus datos y descargarlo con autorización.
- [ ] Entrar como inspector, dictaminar y comprobar que el propietario no puede hacerlo.
- [ ] Consultar clima disponible y comprobar el mensaje cuando Python no responde.
- [ ] Ver detecciones guardadas, revisarlas y recordar que son históricas.
- [ ] Integrar y volver a probar los cambios finales de OCR/login/frontend del equipo.
- [ ] Confirmar si la entrega es local o requiere despliegue; en servidor debe mantenerse activo el programador.

El comando `clima:actualizar` recorre los establecimientos y guarda detecciones activas sin duplicar el ciclo de seis horas. `schedule:work` mantiene el programador local: se detiene al cerrar el proceso/apagar la computadora. No equivale a un despliegue persistente ni envía correo.
