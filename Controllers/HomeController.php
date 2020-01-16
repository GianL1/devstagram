<?php
namespace Controllers;

use \Core\Controller;

class HomeController extends Controller 
{
    public function index()
    {
        $array = array(
            "nome" => "Gyan Lima",
            "IDADE" => '22'
        );

        $this->returnJson($array);
    }

    public function testando(){
        echo "Funcionou";
    }

    public function visualizar_usuarios($id){
        echo "ID: ".$id;
    }
}