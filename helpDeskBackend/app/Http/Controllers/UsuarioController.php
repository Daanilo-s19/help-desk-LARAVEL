<?php

namespace App\Http\Controllers;

use App\Models\talteracao;
use App\Models\tchamado;
use App\Models\tsetor;
use App\Models\tsituacao;
use App\Models\tusuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    private $chamado;
    private $alteracao;
    private $setor;
    private $situacao;
    public function __construct(tchamado $chamado, talteracao $alteracao, tsetor $setor, tsituacao $situacao)
    {
        $this->chamado = $chamado;
        $this->alteracao = $alteracao;
        $this->setor = $setor;
        $this->situacao = $situacao;
    }



    public function cadastrarChamado(Request $request)
    {

        if ($request->all())
            return response()->json($this->chamado->create($request->all()), 201);
        else
            return response()->json('Error', 404);
    }



    public function buscarChamado(Request $request)
    {
        $called = [];
        $chamadoJSON = $this->chamado->where('id_usuario', $request->id_usuario)->get();


        foreach ($chamadoJSON->all() as $chamado) {
            array_push($called, [
                'chamado' => $chamado,
                'ultimaAlteracao' =>  $this->alteracao->where('id_chamado', $chamado->id)->get()->max('data'),
                'setor' => $this->setor->where('id', $chamado->id_setor)->get()->max('nome'),

                $sit = DB::table('talteracao')
                    ->join('tsituacao', 'talteracao.id_situacao', '=', 'tsituacao.id')
                    ->select('tsituacao.nome')
                    ->where('id_chamado', $chamado->id)
                    ->orderBy('tsituacao.id')->get()->first(),
                'situacao' =>   $sit ?   $sit : '',
            ]);
        }
        if ($request->id_usuario)
            return response()->json($called, 200);
        else
            return  response()->json('Achei nao pvt', 404);
    }
    public function detalharChamado(Request $request)
    {
        $called = [];
        $information = DB::table('tchamado')
            ->join('tusuario', 'tchamado.id_usuario', '=', 'tusuario.cpf')
            ->join('tsetor', 'tchamado.id_setor', '=', 'tsetor.id')
            ->join('talteracao', 'tchamado.id', '=', 'talteracao.id_chamado')
            ->join('tsituacao', 'talteracao.id_situacao', '=', 'tsituacao.id')
            ->select(
                'tusuario.email as email',
                'tsetor.nome as setor',
                'tchamado.data as data',
                'tsituacao.nome as situacao'
            )
            ->orderBy('talteracao.id', 'desc')
            ->where('tchamado.id', $request->id)->get()->first();

        $log = DB::table('talteracao')
            ->join('tsituacao', 'tsituacao.id', '=', 'talteracao.id_situacao')
            ->join('tprioridade', 'talteracao.id_prioridade', '=', 'tprioridade.id')
            ->select(
                'talteracao.descricao as descricao',
                'talteracao.data as data',
                'talteracao.id_tecnico as tecnico',
                'tsituacao.nome as situacao',
                'tprioridade.descricao as prioridade'
            )
            ->where('id_chamado', $request->id)->get();

        $calledInformation = $information ? $information : DB::table('tchamado')
            ->join('tusuario', 'tchamado.id_usuario', '=', 'tusuario.cpf')
            ->join('tsetor', 'tchamado.id_setor', '=', 'tsetor.id')
            ->select(
                'tusuario.email as email',
                'tsetor.nome as setor',
                'tchamado.data as data'
            )->where('tchamado.id', $request->id)->get()->first();
        array_push($called, ['chamado' => $calledInformation, 'movimentacao' => $log]);
        if ($request->id)
            return response()->json($called, 200);
        else
            return  response()->json('Achei nao pvt', 404);
    }


    public function alterarChamado(Request $request, $id)
    {
        if ($request->all())
            return response()->json($this->chamado->find($id)->update($request->all()), 200);
        else
            return response()->json('Without Changes', 404);
    }


    public function cancelarChamado(Request $request, $id)
    {

        return response()->json($this->chamado->find($id)->delete(), 204);
    }

    //
    //
}
