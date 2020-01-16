<?php
namespace Controllers;

use \Core\Controller;
use \Models\Users;
use \Models\Fotos;

class FotosController extends Controller 
{
    public function index(){     }
	
	

	public function new_foto() {
        
        $array = array("error" => '');

        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();
        
        
        if ($method == 'POST') {

            if (!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {

                if (!empty($data['url'])) {

                    $fotos = new Fotos();

                    if ($fotos->create($users->getId(), $data['url'])) {

                        $array['error'] = 'Foto cadastrada com sucesso';

                    } else {
                        $array['error'] = 'Erro ao cadastrar foto';
                    }

                } else {
                    $array['error'] = 'Foto não enviada';
                }
                
            } else {
                $array['error'] = 'Dados inválidos';
            }
            
        } else {
           $array['error'] = 'Metodo de requisição incompativel';
        }
        
        $this->returnJson($array);
		
		
		
	}
	
    public function random()
    {
        $array = array("error" => '', "logged" => false);

        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();
        $fotos = new Fotos();

        if(!empty($data['jwt']) && $users->validateJwt($data['jwt']))
        {
            $array['logged'] = true;

            if($method == 'GET') {
                $perpage = 10; 
                if(!empty($data['perpage'])) {
                    $perpage = $data['perpage'];
                }

                $excludes = array();
                if(!empty($data['excludes'])) {
                    $excludes = explode(",", $data['excludes']);
                }

                $array['data'] = $fotos->getRandomFotos($perpage, $excludes);
            }
        }else {
            $array['error'] = 'Acesso negado';
        }

        $this->returnJson($array);
    }

    public function view($id_foto)
    {
        $array = array("error" => '', "logged" => false);

        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();
        $fotos = new Fotos();

        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
            $array['logged'] = true;

            switch($method) {
                case 'GET':
                    $array['data'] = $fotos->getFoto($id_foto);
                    break;
                    
                case 'DELETE':
                    $array['error'] = $fotos->deleteFoto($id_foto, $users->getId());
                    break;
                
                default:
                    $array['error'] = "AEEEEEEEE";
                    break;
            }
            
        }else {
            $array['error'] = 'Acesso negado';
        }

        $this->returnJson($array);

    }
    
    public function comment($id_foto)
    {
        $array = array("error" => '', "logged" => false);

        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();
        $fotos = new Fotos();

        if(!empty($data['jwt']) && $users->validateJwt($data['jwt']))
        {
            $array['logged'] = true;

            switch ($method) {
                case 'POST':
                    if(!empty($data['txt'])) {
                        $array['error'] = $fotos->addComment($id_foto, $users->getId(), $data['txt']);
                    }else {
                        $array['error'] = 'Comentário vazio';
                    }
                    break;
                case 'DELETE':
                    $array['error'] = $fotos->deleteFoto($id_foto, $users->getId());
                    break;
                
                default:
                    $array['error'] = "Metodo não disponivel";
                    break;
            }
            
        }else {
            $array['error'] = 'Acesso negado';
        }

        $this->returnJson($array);
    }

    public function delete_comment($id)
    {
        $array = array("error" => '', "logged" => false);

        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();
        $fotos = new Fotos();

        if(!empty($data['jwt']) && $users->validateJwt($data['jwt']))
        {
            $array['logged'] = true;

            switch ($method) {

                case 'DELETE':
                    $array['error'] = $fotos->deleteComment($id, $users->getId());
                    break;
                
                default:
                    $array['error'] = 'Metodo não disponivel';
                    break;
            }
                    
            
        }else {
            $array['error'] = 'Acesso negado';
        }

        $this->returnJson($array);
    }

    public function like($id_foto)
    {
        $array = array("error" => '', "logged" => false);

        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();
        $fotos = new Fotos();

        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
            $array['logged'] = true;

            switch($method) {
                case 'POST':
                    $array['data'] = $fotos->like($id_foto, $users->getId());
                    break;
                    
                case 'DELETE':
                    $array['error'] = $fotos->unlike($id_foto, $users->getId());
                    break;
                
                default:
                    $array['error'] = "AEEEEEEEE";
                    break;
            }
            
        }else {
            $array['error'] = 'Acesso negado';
        }

        $this->returnJson($array);

    }
}