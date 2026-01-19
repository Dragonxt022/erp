<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
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
        'unidade_id',
        'pin',
        'cpf',
        'profile_photo_path',
        'cargo_id',
        'setor_id',
        'salario',
        'franqueado',
        'franqueadora',
        'colaborador',
        
        'last_visited_url',
        'status'
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

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path && (str_starts_with($this->profile_photo_path, 'http://') || str_starts_with($this->profile_photo_path, 'https://'))) {
            return $this->profile_photo_path;
        }

        return $this->profile_photo_path
            ? Storage::url($this->profile_photo_path)
            : $this->defaultProfilePhotoUrl();
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
            'franqueado' => 'boolean',
            'franqueadora' => 'boolean',
            'colaborador' => 'boolean',
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



    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function setor()
    {
        return $this->belongsTo(Operacional::class, 'setor_id');
    }

    /**
     * Sistema de permissões (Simplificado para Flags)
     * Mantemos o método hasPermission por compatibilidade, mas ele deve checar flags se possível,
     * ou podemos removê-lo se o grep mostrar que ninguém usa além dos middlewares que vamos apagar.
     * O usuário pediu "que apagar esse sistema da aplicação inteira".
     * Então vou remover os métodos getPermissions e userPermission.
     * O hasPermission pode ser mantido retornando true temporariamente até limparmos tudo, ou removido.
     * Vou remover tudo.
     */

    /**
     * Notificações
     */

    /**
     * Notificações
     */

    public function notificacoes()
    {
        return $this->hasMany(Notificacao::class);
    }
}
