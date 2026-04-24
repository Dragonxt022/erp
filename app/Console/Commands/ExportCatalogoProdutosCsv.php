<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportCatalogoProdutosCsv extends Command
{
    protected $signature = 'catalogo:exportar-csv
        {--output= : Caminho completo do arquivo CSV de saída}
        {--delimiter=; : Delimitador do CSV}';

    protected $description = 'Exporta o catálogo de produtos de todas as unidades em CSV usando o insumo_id novo.';

    public function handle(): int
    {
        $outputPath = $this->resolveOutputPath();
        $delimiter = $this->resolveDelimiter();

        $directory = dirname($outputPath);

        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            $this->error('Não foi possível criar o diretório de saída: ' . $directory);

            return self::FAILURE;
        }

        $handle = fopen($outputPath, 'w');

        if ($handle === false) {
            $this->error('Não foi possível abrir o arquivo de saída: ' . $outputPath);

            return self::FAILURE;
        }

        $headers = [
            'registro_id',
            'unidade_id',
            'cidade_unidade',
            'produto_id_legado',
            'insumo_id_novo',
            'nome_produto',
            'categoria_id',
            'fornecedor_id',
            'usuario_id',
            'quantidade',
            'preco_insumo',
            'operacao',
            'unidade_medida',
            'created_at',
            'updated_at',
        ];

        fputcsv($handle, $headers, $delimiter);

        $exportados = 0;

        DB::table('unidade_estoque as ue')
            ->leftJoin('lista_produtos as lp', 'lp.id', '=', 'ue.insumo_id')
            ->leftJoin('infor_unidade as iu', 'iu.id', '=', 'ue.unidade_id')
            ->select([
                'ue.id',
                'ue.unidade_id',
                'iu.cidade',
                'ue.insumo_id as produto_id_legado',
                'lp.insumo_id as produto_insumo_id_novo',
                'lp.id as lista_produto_id',
                'lp.nome as produto_nome',
                'ue.categoria_id',
                'ue.fornecedor_id',
                'ue.usuario_id',
                'ue.quantidade',
                'ue.preco_insumo',
                'ue.operacao',
                'ue.unidade',
                'ue.created_at',
                'ue.updated_at',
            ])
            ->orderBy('ue.unidade_id')
            ->orderBy('lp.nome')
            ->orderBy('ue.id')
            ->chunk(1000, function ($rows) use ($handle, $delimiter, &$exportados) {
                foreach ($rows as $row) {
                    $insumoIdNovo = $row->produto_insumo_id_novo ?: $row->lista_produto_id;

                    fputcsv($handle, [
                        $row->id,
                        $row->unidade_id,
                        $row->cidade,
                        $row->produto_id_legado,
                        $insumoIdNovo,
                        $row->produto_nome,
                        $row->categoria_id,
                        $row->fornecedor_id,
                        $row->usuario_id,
                        $this->formatDecimal($row->quantidade, 3),
                        $this->formatDecimal($row->preco_insumo, 2),
                        $row->operacao,
                        $row->unidade,
                        $row->created_at,
                        $row->updated_at,
                    ], $delimiter);

                    $exportados++;
                }
            });

        fclose($handle);

        $this->info('CSV gerado com sucesso.');
        $this->line('Arquivo: ' . $outputPath);
        $this->line('Registros exportados: ' . $exportados);

        return self::SUCCESS;
    }

    protected function resolveOutputPath(): string
    {
        $output = trim((string) $this->option('output'));

        if ($output !== '') {
            return $output;
        }

        return storage_path('app/exports/catalogo_produtos_unidades_' . now()->format('Y_m_d_His') . '.csv');
    }

    protected function resolveDelimiter(): string
    {
        $delimiter = (string) $this->option('delimiter');

        if ($delimiter === '') {
            return ';';
        }

        return mb_substr($delimiter, 0, 1);
    }

    protected function formatDecimal($value, int $precision): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, $precision, '.', '');
    }
}
