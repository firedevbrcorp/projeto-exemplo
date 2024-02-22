<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empregado extends Model
{
    protected $table = 'empregados';
    protected $fillable = ['nome', 'cargo', 'limiteReembolsoMensal'];
}
