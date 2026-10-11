<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property-read int|null $establecimientos_count
 */
#[Fillable(['nombre', 'apellido', 'email', 'password', 'dni', 'direccion', 'telefono'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @var array<string, string> */
    protected $attributes = ['rol' => 'propietario']; // Por defecto, cuando se crea un usuario es propietario.

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Mantiene el nombre completo del esquema anterior, incluso al sembrar sin eventos.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn (?string $valor, array $atributos): array => [
                'nombre' => $valor,
                'name' => trim(($valor ?? '').' '.($atributos['apellido'] ?? '')),
            ],
        );
    }

    /** @return Attribute<string|null, string|null> */
    protected function apellido(): Attribute
    {
        return Attribute::make(
            set: fn (?string $valor, array $atributos): array => [
                'apellido' => $valor,
                'name' => trim(($atributos['nombre'] ?? '').' '.($valor ?? '')),
            ],
        );
    }

    /**
     * Relación: un propietario puede tener uno o varios establecimientos.
     *
     * @return HasMany<Establecimiento, $this>
     */
    public function establecimientos(): HasMany
    {
        return $this->hasMany(Establecimiento::class);
    }

    /**
     * Indica si el usuario tiene uno de los roles indicados.
     */
    public function es(string ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
