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
- **Entrada:** Recibe el objeto `Establecimiento` y un arreglo asociativo con las mediciones reales del laboratorio (ej. `temp` => `38`, `pH` => `7.1`, `DBO5` => `42`, etc.).
- **Proceso:**
  - Consulta los límites según el cuerpo receptor de vuelco del establecimiento (río, laguna, pozo, etc.).
  - Analiza cada parámetro individualmente.
  - Agrupa los parámetros aprobados, en alerta y en incumplimiento.
  - Calcula el porcentaje de cumplimiento `((cumplidos / total) * 100)`.
  - Determina el semáforo final.
- **Salida:** Retorna un arreglo estructurado con el estado global (`cumple`, `alerta` o `incumple`), porcentajes, desglose parámetro por parámetro y un mensaje sintético.

#### 2. Método Privado `obtenerLimitesDestino(?string $destino)`
Define los umbrales normativos aplicables según la Resolución ICAA 312/21 para parámetros clave como:
- **Temperatura:** Máx. 45 °C (alerta desde 40 °C).
- **pH:** Rango 5.5 a 10.0 (alerta si se aproxima a los extremos).
- **Sólidos suspendidos:** Máx. 35 mg/l (alerta desde 28 mg/l).
- **DBO5:** Máx. 50 mg/l (alerta desde 40 mg/l).
- **DQO:** Máx. 250 mg/l (alerta desde 200 mg/l).
- Detergentes e Hidrocarburos.

#### 3. Método Privado `evaluarParametro(string $nombre, int|float $valor, array $limite)`
- Determina la desviación individual de cada parámetro físico-químico.
- Calcula la diferencia matemática faltante para superar un límite o el margen de seguridad disponible.

#### 4. Métodos Privados `determinarEstado()` y `generarMensaje()`
Aplica la jerarquía de prioridad del semáforo ambiental:
- 🔴 **Incumple:** Basta que 1 solo parámetro exceda el límite máximo/mínimo.
- 🟡 **Alerta:** Ningún parámetro incumple, pero al menos 1 parámetro superó el umbral de alerta preventiva.
- 🟢 **Cumple:** Todos los parámetros se encuentran holgadamente dentro de los márgenes permisibles.

---

### 🧪 Verificación y Calidad de Código

- **Estilo de Código (Pint):** Se ejecutó Laravel Pint (`vendor/bin/pint`) asegurando el cumplimiento de los estándares estrictos de PHP 8.4 y la guía de estilo del proyecto.
- **Prueba Unitaria (Pest):** Se desarrolló el test unitario en `tests/Unit/Services/EvaluadorCumplimientoTest.php` para validar los tres escenarios posibles:
  1. Evaluaciones totalmente en cumplimiento (100%).
  2. Evaluaciones con parámetros en zona de alerta (🟡).
  3. Evaluaciones con incumplimientos (🔴).

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