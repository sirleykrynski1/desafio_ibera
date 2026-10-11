<?php

use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Models\LimiteEfluente;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

/** @return array<string, mixed> */
function datosHotelWeb(): array
{
    return ['nombre' => 'Hotel de prueba', 'rubro' => 'hotel', 'ubicacion' => 'Corrientes',
        'tipo_destino_vuelco' => 'cursos_agua', 'latitud' => -28.54, 'longitud' => -57.17,
        'capacidad_maxima' => 40, 'capacidad_biodigestor' => 5000];
}

function pdfWeb(string $texto): UploadedFile
{
    $pdf = new Dompdf;
    $pdf->loadHtml('<html><body>'.$texto.'</body></html>');
    $pdf->render();

    return UploadedFile::fake()->createWithContent('informe.pdf', $pdf->output());
}

test('visitantes deben iniciar sesión antes de operar', function (string $ruta) {
    $this->get($ruta)->assertRedirect(route('login'));
})->with(['/establecimientos', '/analisis', '/historial']);

test('registra propietarios sin aceptar roles enviados y permite cerrar sesión', function () {
    $this->post('/registro', ['nombre' => 'Ana', 'apellido' => 'Prueba', 'email' => 'ana@example.test',
        'password' => 'ClaveLargaParaPrueba123', 'password_confirmation' => 'ClaveLargaParaPrueba123', 'rol' => 'admin_gobierno',
    ])->assertRedirect(route('establecimientos.indice'));
    $user = User::where('email', 'ana@example.test')->firstOrFail();
    expect($user->rol)->toBe('propietario');
    expect(Hash::check('ClaveLargaParaPrueba123', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
    $this->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
});

test('permite iniciar sesión y rechaza contraseñas incorrectas', function () {
    $user = User::factory()->create();
    $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta'])->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('establecimientos.indice'));
    $this->assertAuthenticatedAs($user);
});

test('limita intentos repetidos de ingreso', function () {
    for ($i = 0; $i < 6; $i++) {
        $this->post('/login', ['email' => 'nadie@example.test', 'password' => 'incorrecta']);
    }
    $this->post('/login', ['email' => 'nadie@example.test', 'password' => 'incorrecta'])->assertTooManyRequests();
});

test('propietario crea y edita su hotel sin cambiar el dueño', function () {
    $user = User::factory()->create();
    $otro = User::factory()->create();
    $this->actingAs($user)->post('/establecimientos', datosHotelWeb() + ['user_id' => $otro->id])->assertSessionHasNoErrors();
    $hotel = Establecimiento::firstOrFail();
    expect($hotel->user_id)->toBe($user->id);
    $this->put('/establecimientos/'.$hotel->id, array_replace(datosHotelWeb(), ['nombre' => 'Hotel actualizado', 'user_id' => $otro->id]))->assertRedirect();
    expect($hotel->fresh()->nombre)->toBe('Hotel actualizado');
    expect($hotel->fresh()->user_id)->toBe($user->id);
    $this->get('/establecimientos/'.$hotel->id)->assertSee('Hotel actualizado');
    $this->get('/establecimientos/'.$hotel->id.'/editar')->assertOk();
});

test('rechaza datos de hotel inválidos sin persistir', function () {
    $this->actingAs(User::factory()->create())->post('/establecimientos', array_replace(datosHotelWeb(), ['latitud' => 100, 'capacidad_maxima' => 0, 'tipo_destino_vuelco' => 'desconocido']))
        ->assertSessionHasErrors(['latitud', 'capacidad_maxima', 'tipo_destino_vuelco']);
    $this->assertDatabaseCount('establecimientos', 0);
});

test('oculta hoteles ajenos y bloquea lectura edición y carga cruzadas', function () {
    Storage::fake('local');
    $hotel = Establecimiento::factory()->create(['nombre' => 'Hotel privado ajeno']);
    $this->actingAs(User::factory()->create())->get('/establecimientos')->assertDontSee('Hotel privado ajeno');
    $this->get('/establecimientos/'.$hotel->id)->assertNotFound();
    $this->put('/establecimientos/'.$hotel->id, datosHotelWeb())->assertNotFound();
    $this->post('/analisis', ['establecimiento_id' => $hotel->id, 'pdf' => UploadedFile::fake()->create('informe.pdf', 100, 'application/pdf')])->assertNotFound();
    $this->assertDatabaseCount('analisis_laboratorio', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('política distingue roles de consulta modificación y permisos', function (string $rol, bool $lee, bool $edita, bool $permiso) {
    $user = User::factory()->create(['rol' => $rol]);
    $hotel = Establecimiento::factory()->for($user)->create();
    expect(Gate::forUser($user)->allows('view', $hotel))->toBe($lee);
    expect(Gate::forUser($user)->allows('update', $hotel))->toBe($edita);
    expect(Gate::forUser($user)->allows('gestionarPermiso', $hotel))->toBe($permiso);
})->with([
    ['propietario', true, true, false], ['inspector', true, false, true], ['admin_gobierno', true, false, true],
]);

test('guarda PDF real con parámetros sin aceptar un dictamen enviado por el hotel', function () {
    Storage::fake('local');
    $hotel = Establecimiento::factory()->create();
    LimiteEfluente::factory()->create(['parametro' => 'Temperatura', 'cursos_agua' => 'Hasta 35']);
    $this->actingAs($hotel->user)->post('/analisis', [
        'establecimiento_id' => $hotel->id,
        'pdf' => pdfWeb('<p>Laboratorio Ambiental de Prueba</p><p>Temperatura: 25 C</p>'),
        'resultado_final' => 'Aprobado',
    ])->assertSessionHasNoErrors()->assertRedirect();
    $analisis = AnalisisLaboratorio::firstOrFail();
    expect($analisis->resultado_final)->toBe('Pendiente');
    expect($analisis->estado)->toBe('evaluado');
    expect($analisis->parametros()->count())->toBeGreaterThan(0);
    expect(is_file($analisis->ruta_pdf))->toBeTrue();
    $this->get('/analisis/'.$analisis->getKey())->assertSee('Pendiente');
    $this->actingAs(User::factory()->create())->get('/analisis/'.$analisis->getKey())->assertNotFound();
    $this->get('/historial')->assertDontSee($hotel->nombre);
});

test('PDF sin mediciones se conserva observado y muestra advertencias', function () {
    Storage::fake('local');
    $hotel = Establecimiento::factory()->create();
    $this->actingAs($hotel->user)->post('/analisis', [
        'establecimiento_id' => $hotel->id, 'pdf' => pdfWeb('<p>Informe sin mediciones disponibles para el extractor.</p>'),
    ])->assertSessionHas('advertencias')->assertRedirect();
    $analisis = AnalisisLaboratorio::firstOrFail();
    expect($analisis->estado)->toBe('observado');
    expect($analisis->resultado_sugerido)->toBeNull();
    expect($analisis->parametros()->count())->toBe(0);
    expect(is_file($analisis->ruta_pdf))->toBeTrue();
});

test('rechaza un falso PDF sin guardar archivos ni análisis', function () {
    Storage::fake('local');
    $hotel = Establecimiento::factory()->create();
    $this->actingAs($hotel->user)->post('/analisis', [
        'establecimiento_id' => $hotel->id, 'pdf' => UploadedFile::fake()->create('falso.pdf', 100, 'text/plain'),
    ])->assertSessionHasErrors(['pdf' => 'El informe debe ser un PDF.']);
    $this->assertDatabaseCount('analisis_laboratorio', 0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('solo gobierno registra permisos y valida fechas', function () {
    $hotel = Establecimiento::factory()->create();
    $datos = ['numero_expediente' => 'EXP-123', 'fecha_emision' => '2026-01-01', 'fecha_vencimiento' => '2027-01-01',
        'estado' => 'Activo', 'estado_tramite' => 'resuelto', 'tipo_destino_vuelco' => 'cursos_agua'];
    $this->actingAs($hotel->user)->post('/establecimientos/'.$hotel->id.'/permiso', $datos)->assertForbidden();
    $this->assertDatabaseCount('permiso_vuelco', 0);
    $inspector = User::factory()->create(['rol' => 'inspector']);
    $this->actingAs($inspector)->post('/establecimientos/'.$hotel->id.'/permiso', array_replace($datos, ['fecha_vencimiento' => '2025-01-01']))
        ->assertSessionHasErrors('fecha_vencimiento');
    $this->post('/establecimientos/'.$hotel->id.'/permiso', $datos)->assertRedirect();
    $this->post('/establecimientos/'.$hotel->id.'/permiso', array_replace($datos, ['estado' => 'Revocado']))->assertRedirect();
    $this->assertDatabaseCount('permiso_vuelco', 1);
    expect($hotel->permisoVuelco()->firstOrFail()->estado)->toBe('Revocado');
    $this->get('/establecimientos/'.$hotel->id)->assertSee('Revocado');
});

test('escapa nombres de establecimientos al mostrarlos', function () {
    $hotel = Establecimiento::factory()->create(['nombre' => '<script>alert(1)</script>']);
    $this->actingAs($hotel->user)->get('/establecimientos')->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
});
