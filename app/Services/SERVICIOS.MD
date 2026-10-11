# Registro de Servicios de la Aplicación

Este documento detalla los servicios internos implementados en la arquitectura de la aplicación, su propósito, estructura, métodos principales y el estado de sus pruebas de calidad.

---

## 1. EvaluadorCumplimiento

- **Archivo:** `app/Services/EvaluadorCumplimiento.php`
- **Tipo de Servicio:** Motor de reglas y evaluación ambiental.

### 🎯 ¿Para qué sirve?
El servicio `EvaluadorCumplimiento` actúa como el motor de reglas de evaluación ambiental. Su objetivo primordial es automatizar la comparación entre los resultados devueltos por los análisis de laboratorio y los parámetros normativos fijados por el ICAA (Resolución 312/21).

Permite responder de forma automática a preguntas como:
- ¿El efluente vertido por un establecimiento cumple con la normativa vigente?
- ¿Existen parámetros que, aunque no violan la ley, están en zona de alerta preventiva?
- ¿Cuál es el porcentaje global de cumplimiento ambiental del análisis presentado?

---

### 🧩 Estructura y Funcionamiento Interno

#### 1. Método Público `evaluar(Establecimiento $establecimiento, array $parametros)`
- **Entrada:** Recibe el objeto `Establecimiento` y un arreglo asociativo con las mediciones reales del laboratorio, usando como clave el valor exacto de `limite_efluente.parametro` (ej. `Temperatura` => `38`, `pH` => `7.1`, `Demanda Bioquímica de Oxígeno (DBO5)` => `42`), tal como lo devuelve `OCRExtractor`.
- **Proceso:**
  - Resuelve la columna de límites (`cursos_agua`, `laguna`, `conducto_pluvial`, `absorcion_suelo`) validando `tipo_destino_vuelco` contra una whitelist; si el destino es null o desconocido cae a `cursos_agua` y agrega una entrada en `advertencias`.
  - Lee los límites desde la tabla `limite_efluente` y analiza cada parámetro que exista en la norma.
  - Calcula el porcentaje de cumplimiento sobre los parámetros evaluables: los NDC, las celdas vacías y los parámetros ausentes de la norma quedan fuera del denominador.
  - Determina el semáforo final.
- **Salida:** Arreglo con el estado global (`cumple`, `alerta` o `incumple`), porcentaje, desglose parámetro por parámetro, la lista `advertencias`, el resumen con `total_parametros` y un mensaje sintético. Cada parámetro de la salida expone `evaluable` y `ndc`.

#### 2. Métodos Privados `resolverColumnaDestino()` y `obtenerLimitesDestino()`
- `resolverColumnaDestino()` valida el destino contra la constante `COLUMNAS_DESTINO`; el valor del enum nunca se interpola, solo se compara con la lista y recién ahí se lee la columna.
- `obtenerLimitesDestino()` consulta `LimiteEfluente::all()` y parsea la celda de la columna elegida junto con la unidad.

**Parseo de las celdas (`parsearLimite()`):**
- `NDC` (no debe contener) → no evaluable: se expone `limite_max = 0` y `ndc = true` porque lo esperado es un valor nulo o ~0, y queda fuera del denominador.
- `Hasta 35` → máximo 35.
- `6,5-8,5` → rango con coma decimal (mínimo 6,5 / máximo 8,5).
- `0,5` o `2000` (número suelto) → máximo.
- Celda vacía o texto no parseable → no evaluable sin límite.

#### 3. Banda de Alerta (`ALERTA_PORCENTAJE = 0.2`)
- Límite de un solo lado: banda del 20% del máximo (`Hasta 35` → alerta desde 28).
- Rango: 20% del rango en cada extremo (`6,5-8,5` → alerta fuera de 6,9-8,1).
- La alerta solo se marca cuando el valor cumple el límite.

#### 4. Método Privado `evaluarParametro()`
- Determina si el valor respeta el mínimo/máximo, si cae en la banda de alerta y calcula la desviación individual o el margen de seguridad disponible.

#### 5. Métodos Privados `determinarEstado()` y `generarMensaje()`
Aplica la jerarquía de prioridad del semáforo ambiental:
- 🔴 **Incumple:** Basta que 1 solo parámetro exceda el límite máximo/mínimo.
- 🟡 **Alerta:** Ningún parámetro incumple, pero al menos 1 parámetro está dentro de la banda de alerta.
- 🟢 **Cumple:** Todos los parámetros se encuentran holgadamente dentro de los márgenes permisibles.

---

### 🧪 Verificación y Calidad de Código

- **Estilo de Código (Pint):** Se ejecutó Laravel Pint (`vendor/bin/pint`) asegurando el cumplimiento de los estándares estrictos de PHP 8.4 y la guía de estilo del proyecto.
- **Prueba Feature (Pest):** `tests/Feature/Services/EvaluadorCumplimientoTest.php` corre contra SQLite en memoria con `RefreshDatabase` y datos creados por `LimiteEfluenteFactory`. Cubre los escenarios de cumplimiento, alerta (unilateral y banda del pH), incumplimiento, parseo de `Hasta`/número con coma decimal/NDC/celda vacía, selección de columna según destino y el fallback con advertencia cuando el destino no se conoce.

  ## 2. GeneradorFUR

- **Archivo:** `app/Services/GeneradorFUR.php`
- **Tipo de Servicio:** Generación de documentos PDF.

### 🎯 ¿Para qué sirve?
El servicio `GeneradorFUR` automatiza la creación del Formulario Único de Registro (FUR). Su objetivo es tomar la información fragmentada en el sistema y unificarla en un documento PDF pre-llenado y listo para su uso administrativo o presentación formal.

### 🧩 Estructura y Funcionamiento Interno
- **Recopilación de Datos:** Extrae dinámicamente la información del establecimiento (incluyendo los nuevos campos de `cuit`, `ubicacion` y `tipo_destino_vuelco`).
- **Historial y Permisos:** Anexa los detalles históricos del permiso de vuelco y las renovaciones.
- **Integración de Evaluaciones:** Incorpora el resultado del semáforo ambiental (evaluación de cumplimiento) en el documento final para dar contexto visual sobre el estado del establecimiento.

### 🧪 Verificación y Calidad de Código
- **Prueba Feature (Pest):** Se desarrolló la suite de pruebas en `tests/Feature/Services/GeneradorFURTest.php`.
- **Cobertura:** Cuenta con **5 pruebas** que validan la correcta inyección de datos en la vista, el manejo de establecimientos sin historial y la generación íntegra del archivo PDF.

---

## 3. OCRExtractor

- **Archivo:** `app/Services/OCRExtractor.php`
- **Tipo de Servicio:** Procesamiento de archivos y normalización de datos.

### 🎯 ¿Para qué sirve?
Este servicio es el encargado de leer e interpretar los informes de laboratorio subidos en formato PDF. Transforma datos no estructurados en información estructurada que el sistema puede comprender y procesar para su evaluación ambiental.

### 🧩 Estructura y Funcionamiento Interno
- **Lectura Óptica:** Procesa el texto contenido dentro de los archivos PDF de los laboratorios.
- **Normalización de Nombres:** Mapea e iguala las variables encontradas en el documento con los nombres canónicos que maneja el sistema en el catálogo `limite_efluente` (ej. transforma variantes de texto como "Demanda Bioquímica" o "DBO" al estándar "DBO5" esperado por la base de datos).
- **Estructuración:** Devuelve un arreglo asociativo limpio con parámetros y valores numéricos exactos listos para ser consumidos por el `EvaluadorCumplimiento`.
### 🧪 Verificación y Calidad de Código

- **Prueba Unitaria (Pest):** Se desarrolló la suite de pruebas en `tests/Unit/Services/OCRExtractorTest.php`.
- **Cobertura:** Cuenta con **9 pruebas** exhaustivas que garantizan la correcta lectura de distintos formatos de PDF, el éxito en la normalización de sinónimos de parámetros químicos y el manejo de excepciones ante archivos corruptos o ilegibles.

---

## 4. RegistrarAnalisisService

- **Archivo:** `app/Services/RegistrarAnalisisService.php`
- **Tipo de Servicio:** Orquestación del alta de análisis de laboratorio.

### 🎯 ¿Para qué sirve?
Unifica en un único flujo el registro de un análisis de laboratorio para la web y la API: extrae el informe PDF con `OCRExtractor`, lo evalúa contra la norma con `EvaluadorCumplimiento` y persiste todo de forma atómica.

### 🧩 Estructura y Funcionamiento Interno
- **Entrada:** `registrar(Establecimiento $establecimiento, string $rutaPdf)` recibe la ruta del informe ya almacenado en storage.
- **Extracción:** Lee el PDF fuera de la transacción y arma las advertencias del extractor.
- **Evaluación:** Si hubo lectura de parámetros, invoca al evaluador con los valores numéricos (clave = `limite_efluente.parametro`) y suma sus advertencias (ej. `destino_desconocido`).
- **Persistencia (`DB::transaction`):** Crea el `AnalisisLaboratorio` con `resultado_final = 'Pendiente'`, `resultado_sugerido` del evaluador, `estado` (`evaluado` con lectura, `observado` sin ella) y `ruta_pdf`; crea los `ParametroAnalisis[]` con `detectado_por`; y sincroniza `limitesEfluentes()` por el pivote solo con los límites de los parámetros presentes en el informe y en la norma.
- **Respaldos:** Cuando el PDF no identifica laboratorio o fecha, se registran `No determinado` y la fecha de hoy (columnas NOT NULL) y se agrega la advertencia `laboratorio_no_identificado`.
- **Salida:** `['analisis' => AnalisisLaboratorio con parámetros y límites, 'advertencias' => list<string>]`.

### 🧪 Verificación y Calidad de Código

- **Prueba Feature (Pest):** `tests/Feature/Services/RegistrarAnalisisServiceTest.php` contra SQLite en memoria. Cubre el alta evaluada con sincronización del pivote, el PDF escaneado persistido como `observado` y la unión de advertencias del extractor con las del evaluador usando la fecha de hoy de respaldo.