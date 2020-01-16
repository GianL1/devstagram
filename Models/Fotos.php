<?php
namespace Models;

use \Core\Model;
use \Models\Users;
use PDO;

class Fotos extends Model 
{
	
	public function create ($id_user, $url) {
		
			$sql = $this->pdo->prepare("INSERT INTO fotos SET id_user = :id_user, url = :url");
            $sql->bindValue(":id_user", $id_user);
            $sql->bindValue(":url", $url);
			$sql->execute();
			
			return true;
	}
	
	
    public function getRandomFotos($perpage, $excludes = array())
    {
            $array = array();

            foreach ($excludes as $key => $item) {
                $excludes[$key] = intval($item);
            }

            if (count($excludes) > 0) {
                $sql = $this->pdo->query("SELECT * FROM fotos WHERE id NOT IN(".implode(",",$excludes).") ORDER BY RAND() LIMIT $perpage");
            } else {
                $sql = $this->pdo->query("SELECT * FROM fotos ORDER BY RAND() LIMIT $perpage");
            }
            

            if ($sql->rowCount() >0)
            {
                $array = $sql->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($array as $key => $item) {
                    $array['url'] = BASE_URL.'media/fotos/'.$item['url'];

                    $array[$key]['like_count'] = $this->getLikeCount($item['id']);
                    $array[$key]['comments'] = $this->getComments($item['id']);
                }
            }
            return $array;
    }

    public function getFeedCollection($ids, $offset, $perpage)
    {   $array = array();
        $users = new Users();

        if(count($ids) > 0){
            $sql = $this->pdo->query("SELECT * FROM fotos WHERE id_user IN(".implode(',', $ids).") ORDER BY id DESC LIMIT $offset, $perpage");

            if ($sql->rowCount() > 0) {
                $array = $sql->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($array as $key => $item) {
                    $user_info = $users->getInfo($item['id_user']);

                    $array[$key]['nome'] = $user_info['nome'];
                    $array[$key]['avatar'] = $user_info['avatar'];
                    $array[$key]['url'] = BASE_URL.'media/photos/'.$item['url'];

                    $array[$key]['like_count'] = $this->getLikeCount($item['id']);
                    $array[$key]['comments'] = $this->getComments($item['id']);
                }
            }
        }

        

        return $array;
    }

    public function getFoto($id_foto)
    {
        $array = array();
        $user = new Users();

        $sql = $this->pdo->prepare("SELECT * FROM fotos WHERE id = :id_foto");
        $sql->bindValue(":id_foto", $id_foto);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);

            $user_info = $user->getInfo($array['id_user']);

            $array['nome'] = $user_info['nome'];
            $array['avatar'] = $user_info['avatar'];
            $array['url'] = BASE_URL.'media/fotos/'.$array['url'];
            $array['like_count'] = $this->getLikeCount($array['id']);
            $array['comments'] = $this->getComments($array['id']);
        }

        return $array;
            
    }

    public function deleteFoto($id_foto, $id_user){
        $sql = $this->pdo->prepare("SELECT id FROM fotos WHERE id =:id AND id_user = :id_user");
        $sql->bindValue(":id", $id_foto);
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();

        if($sql->rowCount() > 0) {

            $sql = $this->pdo->prepare("DELETE FROM fotos WHERE id = :id");
            $sql->bindValue(":id", $id_foto);
            $sql->execute();
    
    
            $sql = $this->pdo->prepare("DELETE FROM fotos_comments WHERE id_foto = :id_foto");
            $sql->bindValue(":id_foto", $id_foto);
            $sql->execute();
    
            $sql = $this->pdo->prepare("DELETE FROM fotos_likes WHERE id_foto = :id_foto");
            $sql->bindValue(":id", $id_foto);
            $sql->execute();

            return '';
        }else {
            return "Esta foto não existe ou não é sua";
        }
    }

    public function getComments($id_foto)
    {
        $array = array();
        $sql = $this->pdo->prepare("SELECT fotos_comments.*, users.nome FROM fotos_comments LEFT JOIN users 
        ON users.id = fotos_comments.id_user WHERE fotos_comments.id_foto = :id_foto");
        $sql->bindValue(":id_foto", $id_foto);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $array;
    }

    public function getLikeCount($id_foto)
    {
      $sql = $this->pdo->prepare("SELECT COUNT(*) AS c FROM fotos_likes WHERE id_foto = :id_foto");
      $sql->bindValue(":id_foto", $id_foto);
      $sql->execute();
      $info = $sql->fetch();
      
      return $info['c'];
    }

    public function getFotosCount($id_user)
    {
        
        $sql = $this->pdo->prepare("SELECT COUNT(*) as c FROM fotos WHERE id_user = :id_user");
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();
        
        $info=$sql->fetch();

        return $info['c'];
    }

    public function getFotosFromUser($id_user, $offset, $perpage)
    {
        $array = array();
        $sql = $this->pdo->prepare("SELECT * FROM fotos WHERE id_user = :id_user ORDER BY id DESC LIMIT $offset, $perpage");
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();

        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($array as $key => $item) {
                $array[$key]['url'] = BASE_URL.'media/photos/'.$item['url'];
                $array[$key]['like_count'] = $this->getLikeCount($item['id']);
                $array[$key]['comments'] = $this->getComments($item['id']);
            }
        }
        return $array;
    }

    public function deleteAll($id){
        $sql = $this->pdo->prepare("DELETE FROM fotos WHERE id_user = :id_user");
        $sql->bindValue(":id_user", $id);
        $sql->execute();


        $sql = $this->pdo->prepare("DELETE FROM fotos_comments WHERE id_user = :id_user");
        $sql->bindValue("id_user", $id);
        $sql->execute();

        $sql = $this->pdo->prepare("DELETE FROM fotos_likes WHERE id_user = :id_user");
        $sql->bindValue("id_user", $id);
        $sql->execute();


    }

    public function addComment($id_foto, $id_user, $txt)
    {
        if(!empty($txt)){
        $sql = $this->pdo->prepare("INSERT INTO fotos_comments (id_user, id_foto, data, txt) VALUES (:id_user, :id_foto, NOW(), :txt)");
        $sql->bindValue(":id_foto", $id_foto); 
        $sql->bindValue(":id_user", $id_user);
        $sql->bindValue(":txt", $txt);  
        $sql->execute();

        return '';
        }else {
            return "comentário vazio";
        }
    }

    public function deleteComment($id_comment, $id_user)
    {

        $sql = $this->pdo->prepare("SELECT fotos.id_user FROM fotos_comments 
        LEFT JOIN fotos ON fotos.id = fotos_comments.id_foto WHERE fotos.id_user =:id_user");
        $sql->bindValue(":id_user", $id_user);
        $sql->execute();

        if ($sql->rowCount() > 0) {
            $criador_post = $sql->fetchAll(\PDO::FETCH_COLUMN);
            
        }

        
        if($id_user == $criador_post[0]){
            
            $sql = $this->pdo->prepare("SELECT id FROM fotos_comments LEFT JOIN fotos 
            ON fotos.id = fotos_comments.id_foto WHERE fotos.id_user =:id_user");

            $sql->bindValue(":id_user", $id_user);
            $sql->execute();
            
            if($sql->rowCount() > 0) {

                $dono_post = $sql->fetchAll(\PDO::FETCH_COLUMN);
                
                if (in_array($id_comment, $dono_post)) {
                    
                    $sql = $this->pdo->prepare("DELETE FROM fotos_comments WHERE id = :id");
                    $sql->bindValue(":id", $id_comment);
                    $sql->execute();

                    return 'COMENTARIO DELETADO DO SEU POST';
                }

                
            }
        }else {

            $sql = $this->pdo->prepare("SELECT id FROM fotos_comments WHERE id_user = :id_user AND id = :id");
            $sql->bindValue(":id_user", $id_user);
            $sql->bindValue(":id", $id_comment);
            $sql->execute();

            

            if($sql->rowCount() > 0) {
                
                $sql = $this->pdo->prepare("DELETE FROM fotos_comments WHERE id = :id");
                $sql->bindValue(":id", $id_comment);
                $sql->execute();

                return '';
            } else {
                return "Esse comentário/post não é seu";
            }
        }
            
    }

    public function like($id_foto, $id_user)
    {
        $sql = $this->pdo("SELECT * FROM fotos_likes WHERE id_user = :id_user AND id_foto =:id_foto");
        $sql->bindValue(":id_user", $id_user);
        $sql->bindValue(":id_foto", $id_foto);
        $sql->execute();

        if($sql->rowCount() > 0)
        {
            return "Você não pode dar like numa foto já curtida";

        }else {
            $sql = $this->pdo->prepare("INSERT INTO fotos_likes (id_user, id_foto) VALUES (:id_user, :id_foto)"); 
            $sql->bindValue(":id_user", $id_user);
            $sql->bindValue(":id_foto", $id_foto);
            $sql->execute();

            return '';
        }
    }

    public function unlike($id_foto, $id_user)
    {
        $sql = $this->pdo->prepare("DELETE FROM fotos_likes WHERE id_user = :id_ser AND id_foto = :id_foto");
        $sql->bindValue(":id_user", $id_user);
        $sql->bindValue(":id_foto", $id_foto);
        $sql->execute();

        return '';
    }
}