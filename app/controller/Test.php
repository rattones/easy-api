<?php
namespace app\controller;

use app\model\DaoCadastro;
use core\system\Controller;
use stdClass;


class Test extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function Method()
    {
        $obj= new stdClass();
        $obj->message= 'Ok, controler Teste e método Method executados com sucesso';

        self::response(200, (array)$obj);
    }

    public function createCadastro()
    {
        $post= self::_POST();
        $obj= new stdClass();
            $obj->nome=              $post['nome'];
            $obj->documento=         $post['documento'];
            $obj->email=             $post['email'];
            $obj->razaoSocial=       $post['razaoSocial'];
            $obj->responsavel=       $post['responsavel'];
            $obj->emailResponsavel=  $post['emailResponsavel'];
            $obj->cep=               $post['cep'];
            $obj->logradouro=        $post['logradouro'];
            $obj->numero=            $post['numero'];
            $obj->complemento=       $post['complemento'];
            $obj->bairro=            $post['bairro'];
            $obj->cidade=            $post['cidade'];
            $obj->uf=                $post['uf'];
            $obj->pais=              $post['pais'];

        $cad= new DaoCadastro();
        $obj= $cad->create($obj);

        self::response(200, (array)$obj);
    }
}