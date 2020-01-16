<?php
namespace Controllers;

use \Core\Controller;
use \Models\Users;
use \Models\Fotos;

class UsersController extends Controller
{
    public function index(){}

    public function login()
    {
        $array = array('error' => '');

        $method = $this->getMethod();
        $data = $this->getRequestData();

        if($method == 'POST') {
            if(!empty($data['email']) && !empty($data['senha'])) {
                $users = new Users();

                if ($users->checkCredentials($data['email'], $data['senha']) ) {

                    $array['jwt'] = $users->createJwt();

                } else {
                    $array['error'] = 'Erro de credenciais';
                }

            } else {
                $array['error'] = "Email e/ou senha não preenchido";
            }
        } else {
            $array['error'] = "Metodo de requisição incompativel";
        }
        $this->returnJson($array);
    }

    public function new_record()
    {

        $array = array("error" => '');

        $method = $this->getMethod();
        $data = $this->getRequestData();
        
        if ($method == 'POST') {
            if (!empty($data['nome']) && !empty($data['email']) && !empty($data['senha'])) {
                if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $users = new Users();

                    if ($users->create($data['nome'], $data['email'], $data['senha'])) {
                        $array['jwt'] = $users->createJwt();
                    } else {
                        $array['error'] = 'Email já existente';
                    }

                } else {
                    $array['error'] = 'Email inválido';
                }
                
            } else {
                $array['error'] = 'Dados incompletous e/ou já existentes';
            }
            
        } else {
           $array['error'] = 'Metodo de requisição incompativel';
        }
        
        $this->returnJson($array);
    }

    public function feed()
    {
        $array = array('error' => '', 'logged' => false);

        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();

        if (!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
            $array['logged'] = true;
            
            if($method == 'GET') {
                $offset = 0;
                if(!empty($data['offset'])) {
                    $offset = intval($data['offset']);
                }

                $perpage = 10;

                if (!empty($data['perpage'])) {
                    $perpage = intval($data['perpage']);
                }

                $array['data'] = $users->getFeed($offset, $perpage);
                
            } else {
              $array['error'] = 'Metodo '.$method. ' não disponivel';
            }

            
        }else {
            $array['error'] = 'Acesso negado';
        }
        $this->returnJson($array);
    }
	
    
	
     public function view($id)
    {
        $array = array('error' => '', 'logged' => false);

        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();

        if (!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
            $array['logged'] = true;
            $array['is_me'] = false;

            if ($id == $users->getId()) {
                $array['is_me'] = true;
            }

            switch ($method) {
                case 'GET':
                    $array['data'] = $users->getInfo($id);
                    
                    if(count($array['data']) === 0) {
                        $array['error'] = 'Usuário não existe';
                    }
                    break;
                case 'PUT':
                    $array['error'] = $users->editInfo($id, $data);
                    break;
                case 'DELETE':
                    $array['error'] = $users->delete($id);
                    break;

                default:
                    $array['error'] = 'Metodo '.$method. ' não disponivel';
                    break;
            }
        }else {
            $array['error'] = 'Acesso negado';
        }
        $this->returnJson($array);
    }

    public function fotos($id_user)
    {
        $array = array('error' => '', 'logged' => false);
        
        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();
        $f = new Fotos();

        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
            $array['logged'] = true;
            $array['is_me'] = false;

            if($id_user == $users->getId()) {
                $array['is_me'] = true;
            }

            if($method == 'GET') {
                $offset = 0;

                if(!empty($data['offset'])) {
                    $offset = intval($data['offset']);
                }

                $perpage = 10;

                if(!empty($data['perpage'])) {
                    $perpage = intval($data['perpage']);
                }

                $array['data'] = $f->getFotosFromUser($id_user, $offset, $perpage);


            }else {
                $array['error'] = 'Acesso Negado 1';
            }
        } else {
            $array['error'] = 'Acesso Negado 2';
        }

        $this->returnJson($array);
    }

    public function follow()
    {
        $array = array('error' => '', 'logged' => false);
        
        $method = $this->getMethod();
        $data = $this->getRequestData();

        $users = new Users();

        if(!empty($data['jwt']) && $users->validateJwt($data['jwt'])) {
            $array['logged'] = true;
            
            switch ($method) {
                case 'POST':
                    $users->follow($data['id_user']);

                    break;
                case 'DELETE':
                    $users->unfollow($data['id_user']);
                    
                    break;
                
                default:
                    $array['error'] = 'METODO NÃO ENCONTRADO';
                    break;
            }
        } else {
            $array['error'] = 'Acesso Negado 2';
        }

        $this->returnJson($array);

    }
}