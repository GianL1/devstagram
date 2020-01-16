<?php

namespace Models;

use \Core\Model;
use \Models\Jwt;
use \Models\Fotos;

class Users extends Model 
{
    private $id_user; 


    public function getInfo($id)
    {
        $array = array();

        $sql = $this->pdo->prepare("SELECT id, nome, email, avatar FROM users WHERE id = :id");
        $sql->bindValue(":id", $id);
        $sql->execute();


        if ($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);

            $fotos = new Fotos();

            if(!empty($array['avatar'])) {
                $array['avatar'] = BASE_URL.'media/avatar/'.$array['avatar'];
            }else {
                $array['avatar'] = BASE_URL.'media/avatar/default.jpg';
            }

            $array['followers'] = $this->getFollowersCount($id);
            $array['following'] = $this->getFollowingCount($id);
            $array['fotos_count'] = $fotos->getFotosCount($id);

        }

        return $array;
    }

    public function getFeed($offset = 0, $perpage = 10){
        //1- Pegar os seguidores
        //2- Fazer uma lista das ultimas fotos desses seguidores

        $followingUsers = $this->getFollowing($this->getId());

        $f = new Fotos();

        return $f->getFeedCollection($followingUsers, $offset, $perpage);
    }

    public function getFollowing($id_user){
        $array = array();

        $sql = $this->pdo->prepare("SELECT id_user_passive FROM users_following WHERE id_user_active = :id");
        $sql->bindValue(":id", $id_user);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $data = $sql->fetchAll();

            foreach ($data as $item) {
                $array[] = intval($item['id_user_passive']);
            }
        }

        return $array;
    }

    public function getFollowingCount($id_user)
    {
        $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM users_following WHERE id_user_active = :id_user");
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();
        
        $info=$sql->fetch();

        return $info['c'];
    }

    public function getFollowersCount($id_user)
    {
        
        $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM users_following WHERE id_user_passive = :id_user");
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();
        
        $info=$sql->fetch();

        return $info['c'];
    }
    public function getId(){
        return $this->id_user;
    }

    public function checkCredentials($email, $senha)
    {
        $sql = $this->pdo->prepare("SELECT id, senha FROM users WHERE email = :email");
        $sql->bindValue(":email", $email);
        $sql->execute();
        
        if ($sql->rowCount() > 0) 
        {
            $info = $sql->fetch();

            if(password_verify($senha, $info['senha'])) { 

                $this->id_user = $info['id'];
                return true;

            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function follow($id_user)
    {
        $array = array();
        $sql = $this->pdo->prepare("SELECT * FROM users_following WHERE id_user_active = :id_user_active AND id_user_passive = :id_user_passive");
        $sql->bindValue(":id_user_active", $this->getId());
        $sql->bindValue(":id_user_passive", $id_user);
        $sql->execute();

        if($sql->rowCount() === 0) {
            $sql = "INSERT INTO users_following (id_user_active, id_user_passive) VALUES (:id_user_active, :id_user_passive)";
            $sql->bindValue(":id_user_active", $this->getId());
            $sql->bindValue(":id_user_passive", $id_user);
            $sql->execute();
        }
        return $array;
    }

    public function unfollow($id_user)
    {
            $sql = $this->pdo->prepare("DELETE FROM users_following WHERE id_user_active =- :id_user_active AND id_user_passive = :id_user_passive");
            $sql->bindValue(":id_user_active", $this->getId());
            $sql->bindValue(":id_user_passive", $id_user);
            $sql->execute();

    }
    public function createJwt()
    {
        $jwt = new Jwt();

        return $jwt->create(array("id_user" => $this->id_user));
    }

    public function validateJwt($token)
    {
        $jwt = new Jwt();
        $info = $jwt->validate($token);

        if(isset($info->id_user)) {
            $this->id_user = $info->id_user;
            return true;
        }else {
            return false;
        }
    }

    public function cadastro($email, $senha){
        $sql = $this->pdo->prepare("INSERT INTO users SET email = :email, senha =:senha");
        $sql->bindValue(":email", $email);
        $sql->bindValue(":senha", $senha);
        $sql->execute();
    }
    

    public function create($nome, $email, $senha)
    {
        if (!$this->emailExists($email)) {

            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = $this->pdo->prepare("INSERT INTO users SET email = :email, senha =:senha, nome = :nome");
            $sql->bindValue(":email", $email);
            $sql->bindValue(":senha", $hash);
            $sql->bindValue(":nome", $nome);
            $sql->execute();

            $this->id_user = $this->pdo->lastInsertId();

            return true;

        } else {
            return false;
        }
    }

    public function editInfo($id, $data)
    {
        if($this->getId() === $id) {
            $toChange = array();

            if(!empty($data['nome'])) {
                $toChange['nome'] = $data['nome'];
            } 

            if(!empty($data['email'])) {
                if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    if (!$this->emailExists($data['email'])) {
                        $toChange['email'] = $data['email'];
                    } else {
                        return "Email já existente";
                    }
                    
                }else {
                    return "Email inválido";
                }
                
            }

            if (!empty($data['senha'])) {
                $toChange['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
            }

            if(count($toChange) > 0) { 
                $fields = array();
                foreach ($toChange as $k =>$v) {
                    $fields[] = $k.'= :'.$k;    
                }

                $sql = $this->pdo->prepare("UPDATE users SET ".implode(',', $fields)." WHERE id = :id");
                $sql->bindValue(":id", $id);
                
                foreach ($toChange as $k => $v) {
                    $sql->bindValue(":".$k, $v);
                }

                $sql->execute();
                return '';
            }
        } else {
            return "Não é permitido editar outro usuário";
        }
    }

    public function delete($id)
    {
        if ($id === $this->getId()) {
            $f = new Fotos();
            $f->deleteAll($id);

            $sql = $this->pdo->prepare("DELETE FROM users_following WHERE id_user_active = :id OR id_user_passive = :id" );
            $sql->bindValue(":id", $id);
            $sql->execute();

            $sql = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
            $sql->bindValue(":id", $id);
            $sql->execute();
        } else {
            return "Não é permitido excluir outro usuário";
        } 
    }


    private function emailExists($email)
    {
        $sql = $this->pdo->prepare("SELECT email FROM users WHERE email =:email");
        $sql->bindValue(":email", $email);
        $sql->execute();

        if($sql->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}