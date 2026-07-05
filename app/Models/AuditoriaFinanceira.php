<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaFinanceira extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'auditoria_financeira';

    /**
     * Indicates if the model should be timetamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lancamento_id',
        'tipo_lancamento',
        'valor',
        'tipo_entidade',
        'entidade_id',
        'entidade_descricao',
        'usuario_logado_id',
        'usuario_logado_nome',
        'aprovado_por_id',
        'aprovado_por_nome',
        'descricao_curta',
        'payload',
        'criado_em',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'payload' => 'array',
            'criado_em' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::updating(function ($model) {
            throw new \Exception('Auditoria financeira é somente leitura e não pode ser editada.');
        });

        static::deleting(function ($model) {
            throw new \Exception('Auditoria financeira é somente leitura e não pode ser excluída.');
        });
    }
}
