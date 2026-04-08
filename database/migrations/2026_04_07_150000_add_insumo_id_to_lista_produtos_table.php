<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddInsumoIdToListaProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lista_produtos', function (Blueprint $table) {
            $table->unsignedBigInteger('insumo_id')->nullable()->after('id');
            $table->index('insumo_id');
        });

        $this->popularInsumoIds();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lista_produtos', function (Blueprint $table) {
            $table->dropIndex(['insumo_id']);
            $table->dropColumn('insumo_id');
        });
    }

    private function popularInsumoIds(): void
    {
        $csvPath = base_path('doc/insumos_insumo.csv');

        if (! is_file($csvPath)) {
            return;
        }

        $csvRows = $this->carregarCsv($csvPath);

        if (empty($csvRows)) {
            return;
        }

        $csvPorNome = [];

        foreach ($csvRows as $row) {
            $csvPorNome[$this->normalizarNome($row['nome'])] = (int) $row['id'];
        }

        $aliases = [
            'alga nori gold maki 140g' => 'alga nori gold 140g',
            'gas butano 227g' => 'gas butano',
            'sal branco 1kg' => 'sal branco',
            'coca cola zero lata' => 'coca cola zero acucar',
            'embalagem yakisoba 500 unid' => '500x embalagem yakisoba',
            'embalagem gaveta 200 unid' => '200x embalagem gaveta',
            'embalagem poke p 540mls caixa com 200 unidades base e tampa' => '200x embalagem poke p',
            'embalagem poke g 750 mls caixa com 200 unidades base e tampa' => '200x embalagem poke g',
            'embalagem m 500 unid' => '500x embalagem m',
            'embalagem g 500 unid' => '500x embalagem g',
            'embalagem p 700 unid' => '700x embalagem p',
            'sacola kraft g 400 unid' => '400x sacola kraft g',
            'sacola kraft p 600 unid' => '600x sacola kraft p',
            'poupa maracuja natural' => 'poupa maracuja',
            'sweet chilli suace pantai 700ml' => 'sweet chilli sauce pantai 700ml',
        ];

        DB::table('lista_produtos')
            ->select('id', 'nome')
            ->orderBy('id')
            ->get()
            ->each(function ($produto) use ($csvPorNome, $aliases) {
                $nomeNormalizado = $this->normalizarNome($produto->nome);
                $nomeReferencia = $aliases[$nomeNormalizado] ?? $nomeNormalizado;
                $insumoId = $csvPorNome[$nomeReferencia] ?? null;

                if ($insumoId === null) {
                    return;
                }

                DB::table('lista_produtos')
                    ->where('id', $produto->id)
                    ->update(['insumo_id' => $insumoId]);
            });
    }

    private function carregarCsv(string $csvPath): array
    {
        $handle = fopen($csvPath, 'r');

        if ($handle === false) {
            return [];
        }

        $rows = [];
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return [];
        }

        while (($data = fgetcsv($handle)) !== false) {
            if (! isset($data[0], $data[1])) {
                continue;
            }

            $rows[] = [
                'id' => $data[0],
                'nome' => $data[1],
            ];
        }

        fclose($handle);

        return $rows;
    }

    private function normalizarNome(string $nome): string
    {
        $nome = Str::ascii(mb_strtolower(trim($nome)));
        $nome = preg_replace('/[^a-z0-9]+/', ' ', $nome);

        return preg_replace('/\s+/', ' ', trim($nome));
    }
}
