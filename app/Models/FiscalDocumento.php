<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalDocumento extends Model
{
    protected $table = 'fiscal_documentos';

    protected $fillable = [
        'loja_id', 'cliente_id', 'fornecedor_id',
        'tipo', 'modelo', 'serie', 'numero', 'chave_acesso',
        'status', 'xml_path', 'pdf_path',
        'valor_total',
        'emitida_at',
        'motivo_cancelamento',
    ];

    protected function casts(): array
    {
        return [
            'valor_total' => 'float',
            'emitida_at' => 'datetime',
        ];
    }

    public function itens()
    {
        return $this->hasMany(FiscalDocumentoItem::class, 'fiscal_documento_id');
    }

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }
}
