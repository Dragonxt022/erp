<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relacionamento de muitos para muitos com o usuário
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions');
    }
}

