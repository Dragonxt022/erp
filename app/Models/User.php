<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'unidade_id',  // Novo campo de unidade
        'cargo_id',    // Novo campo de cargo
        'pin',         // Novo campo para PIN
        'cpf',         // Novo campo para CPF
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

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

    /**
     * Relacionamento com a tabela de historico
     */

    public function historicoPedidos()
    {
        return $this->hasMany(HistoricoPedido::class, 'usuario_responsavel_id');
    }


    /**
     * Relacionamento com a tabela de permissões
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    /**
     * Relacionamento com a tabela de UserDetails
     */
    public function userDetails()
    {
        return $this->hasOne(UserDetails::class);
    }

    /**
     * Relacionamento com a tabela de Unidade
     */
    public function unidade()
    {
        return $this->belongsTo(InforUnidade::class, 'unidade_id');
    }

    /**
     * Relacionamento com a tabela de Cargo
     */
    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id'); // Referência à chave estrangeira cargo_id
    }

    /**
     * Acessor para obter o nome completo do cargo.
     */
    public function getCargoNameAttribute()
    {
        return $this->cargo ? $this->cargo->name : null;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
