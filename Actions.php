<?php 
session_start();
require_once('DBConnection.php');

Class Actions extends DBConnection{
    function __construct(){
        parent::__construct();
    }
    function __destruct(){
        parent::__destruct();
    }
    function login(){
        extract($_POST);
        $sql = "SELECT * FROM admin_list where username = '{$username}' and `password` = '".md5($password)."' ";
        @$qry = $this->query($sql)->fetchArray();
        if(!$qry){
            $resp['status'] = "failed";
            $resp['msg'] = "Usuario o contraseña inválidos";
        }else{
            $resp['status'] = "success";
            $resp['msg'] = "Acceso exitoso";
            foreach($qry as $k => $v){
                if(!is_numeric($k))
                $_SESSION[$k] = $v;
            }
        }
        return json_encode($resp);
    }
    function logout(){
        session_destroy();
        header("location:./admin");
    }
    function save_category(){
        extract($_POST);
        if(empty($id))
            $sql = "INSERT INTO `category_list` (`name`,`status`)VALUES('{$name}','{$status}')";
        else{
            $data = "";
             foreach($_POST as $k => $v){
                 if(!in_array($k,array('id'))){
                     if(!empty($data)) $data .= ", ";
                     $data .= " `{$k}` = '{$v}' ";
                 }
             }
            $sql = "UPDATE `category_list` set {$data} where `category_id` = '{$id}' ";
        }
        @$check= $this->query("SELECT COUNT(category_id) as count from `category_list` where `name` = '{$name}' ".($id > 0 ? " and category_id != '{$id}'" : ""))->fetchArray()['count'];
        if(@$check> 0){
            $resp['status'] ='failed';
            $resp['msg'] = 'Nombre de categoría existe actualmente';
        }else{
            @$save = $this->query($sql);
            if($save){
                $resp['status']="success";
                if(empty($id))
                    $resp['msg'] = "Categoría guardada exitosamente.";
                else
                    $resp['msg'] = "Categorías actualizada exitosamente.";
            }else{
                $resp['status']="failed";
                if(empty($id))
                    $resp['msg'] = "Proceso de generación de nueva categoría falló";
                else
                    $resp['msg'] = "Proceso de actualización de categoría falló.";
                $resp['error']=$this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }
    function delete_category(){
        extract($_POST);

        @$delete = $this->query("DELETE FROM `category_list` where category_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Categoría eliminada exitosamente';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function update_stat_cat(){
        extract($_POST);
        @$update = $this->query("UPDATE `category_list` set `status` = '{$status}' where category_id = '{$id}'");
        if($update){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Categoría eliminada exitósamente';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function save_sub_category(){
        extract($_POST);
        if(empty($id))
            $sql = "INSERT INTO `sub_category_list` (`name`,`category_id`,`status`)VALUES('{$name}','{$category_id}','{$status}')";
        else{
            $data = "";
             foreach($_POST as $k => $v){
                 if(!in_array($k,array('id'))){
                     if(!empty($data)) $data .= ", ";
                     $data .= " `{$k}` = '{$v}' ";
                 }
             }
            $sql = "UPDATE `sub_category_list` set {$data} where `sub_category_id` = '{$id}' ";
        }
        @$check= $this->query("SELECT COUNT(sub_category_id) as count from `sub_category_list` where `name` = '{$name}' and `category_id` = '{$category_id}' ".($id > 0 ? " and sub_category_id != '{$id}'" : ""))->fetchArray()['count'];
        if(@$check> 0){
            $resp['status'] ='failed';
            $resp['msg'] = 'Nombre de subcategoría existe actualmente';
        }else{
            @$save = $this->query($sql);
            if($save){
                $resp['status']="success";
                if(empty($id))
                    $resp['msg'] = "Sub categoría guardada exitosamente";
                else
                    $resp['msg'] = "Sub categoría actualizada exitosamente";
            }else{
                $resp['status']="failed";
                if(empty($id))
                    $resp['msg'] = "Proceso de generación de nueva categoría falló";
                else
                    $resp['msg'] = "No se pudo actualizar la subcategoría.";
                $resp['error']=$this->lastErrorMsg();
            }
        }
        return json_encode($resp);
    }
    function delete_sub_category(){
        extract($_POST);

        @$delete = $this->query("DELETE FROM `sub_category_list` where sub_category_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Subcategoría eliminada exitosamente';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function update_stat_sub_cat(){
        extract($_POST);
        @$update = $this->query("UPDATE `sub_category_list` set `status` = '{$status}' where sub_category_id = '{$id}'");
        if($update){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Subcategoría eliminada exitosamente';
        }else{
            $resp['status']='failed';
            $resp['error']=$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    
    function save_book(){
        extract($_POST);
        @$check= $this->query("SELECT count(book_id) as `count` FROM `book_list` where `isbn` = '{$name}' ".($id > 0 ? " and book_id != '{$id}'" : ''))->fetchArray()['count'];
        if($check> 0){
            $resp['status'] = 'failed';
            $resp['msg'] = "ISBN de libro existe actualmente";
        }else{
            $data = "";
            foreach($_POST as $k =>$v){
                if(!in_array($k,array('id','thumbnail','img','category_id'))){
                    if(empty($id)){
                        $columns[] = "`{$k}`"; 
                        $values[] = "'{$v}'"; 
                    }else{
                        if(!empty($data)) $data .= ", ";
                        $data .= " `{$k}` = '{$v}'";
                    }
                }
            }
            if(isset($columns) && isset($values)){
                $data = "(".(implode(",",$columns)).") VALUES (".(implode(",",$values)).")";
            }
            if(empty($id)){
                $sql = "INSERT INTO `book_list` {$data}";
            }else{
                $sql = "UPDATE `book_list` set {$data} where book_id = '{$id}'";
            } 
            @$save = $this->query($sql);
            if($save){
                $resp['status'] = 'success';
                if(empty($id))
                $resp['msg'] = 'Libro agregado exitosamente';
                else
                $resp['msg'] = 'Libro actualizado exitosamente';
                if(empty($id))
                $last_id = $this->query("SELECT max(book_id) as last_id from `book_list`")->fetchArray()['last_id'];
                $pid = !empty($id) ? $id : $last_id;
                if(isset($_FILES)){
                    foreach($_FILES as $k=>$v){
                        $$k=$v;
                    }
                }
                if(isset($thumbnail) && !empty($thumbnail['tmp_name'])){
                    $thumb_file = $thumbnail['tmp_name'];
                    $thumb_fname = $pid.'.png';
                    $file_type = mime_content_type($thumb_file);
                    list($width, $height) = getimagesize($thumb_file);
                    $t_image = imagecreatetruecolor('350', '350');
                    if(in_array($file_type,array('image/png','image/jpeg','image/jpg'))){
                        $gdImg = ($file_type =='image/png') ? imagecreatefrompng($thumb_file) : imagecreatefromjpeg($thumb_file);
                        imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, '350', '350', $width, $height);
                        if($t_image){
                            if(is_file(__DIR__.'/uploads/thumbnails/'.$thumb_fname))
                                unlink(__DIR__.'/uploads/thumbnails/'.$thumb_fname);
                                imagepng($t_image,__DIR__.'/uploads/thumbnails/'.$thumb_fname);
                                imagedestroy($t_image);
                        }else{
                            $resp['msg'] = 'El libro se guardó correctamente, pero no se pudo cargar la imagen en miniatura.';
                        }
                    }else{
                            $resp['msg'] = 'El libro se guardó correctamente, pero la imagen en miniatura no se pudo cargar debido a un tipo de archivo no válido.';
                    }
                }
                if(isset($img) && count($img['tmp_name']) > 0){
                    if(!is_dir(__DIR__.'/uploads/images/'.$pid))
                    mkdir(__DIR__.'/uploads/images/'.$pid);
                    for($i = 0;$i < count($img['tmp_name']); $i++){
                        if(!empty($img['tmp_name'][$i])){
                            $img_file = $img['tmp_name'][$i];
                            $ex = explode('.',$img['name'][$i]);
                            $_fname = $ex[0];
                            $_i = 1;
                            while(true){
                                $_i++;
                                if(is_file(__DIR__.'/uploads/images/'.$pid.'/'.$_fname.'.png')){
                                    $_fname =$ex[0].'_'.$_i;
                                }else{
                                    break;
                                }
                            }
                            $img_fname = $_fname.'.png';
                            $file_type = mime_content_type($img_file);
                            list($width, $height) = getimagesize($img_file);
                            $t_image = imagecreatetruecolor('350', '350');
                            if(in_array($file_type,array('image/png','image/jpeg','image/jpg'))){
                                $gdImg = ($file_type =='image/png') ? imagecreatefrompng($img_file) : imagecreatefromjpeg($img_file);
                                imagecopyresampled($t_image, $gdImg, 0, 0, 0, 0, '350', '350', $width, $height);
                                if($t_image){
                                    imagepng($t_image,__DIR__.'/uploads/images/'.$pid.'/'.$img_fname);
                                    imagedestroy($t_image);
                                }else{
                                    $resp['msg'] = 'El libro se guardó correctamente, pero no se pudo cargar la imagen del libro.';
                                }
                            }else{
                                $resp['msg'] = 'El libro se guardó correctamente, pero la imagen del libro no se pudo cargar debido a un tipo de archivo no válido.';
                            }

                        }
                    }
                }
            }else{
                $resp['status'] = 'failed';
                $resp['msg'] = 'An error occured. Error: '.$this->lastErrorMsg();
                $resp['sql'] = $sql;
            }
        }
        return json_encode($resp);
    }
    function delete_book(){
        extract($_POST);
        @$delete = $this->query("DELETE FROM `book_list` where book_id = '{$id}'");
        if($delete){
            $resp['status']='success';
            $_SESSION['flashdata']['type'] = 'success';
            $_SESSION['flashdata']['msg'] = 'Book successfully deleted.';
            if(is_file(__DIR__.'/uploads/thumbnails/'.$id.'.png'))
                unlink(__DIR__.'/uploads/thumbnails/'.$id.'.png');
            if(is_dir(__DIR__.'/uploads/images/'.$id)){
                $scan = scandir(__DIR__.'/uploads/images/'.$id);
                foreach($scan as $img){
                    if(!in_array($img,array('.','..'))){
                        unlink(__DIR__.'/uploads/images/'.$id.'/'.$img);
                    }
                }
                rmdir(__DIR__.'/uploads/images/'.$id);
            }
        }else{
            $resp['status']='failed';
            $resp['msg'] = 'Ocurrió un error. Error: '.$this->lastErrorMsg();
        }
        return json_encode($resp);
    }
    function delete_img(){
        extract($_POST);
        if(is_file(__DIR__.$path)){
            unlink(__DIR__.$path);
        }
        $resp['status'] = 'success';
        return json_encode($resp);
    }
}
$a = isset($_GET['a']) ?$_GET['a'] : '';
$action = new Actions();
switch($a){
    case 'login':
        echo $action->login();
    break;
    case 'logout':
        echo $action->logout();
    break;
    case 'save_category':
        echo $action->save_category();
    break;
    case 'delete_category':
        echo $action->delete_category();
    break;
    case 'update_stat_cat':
        echo $action->update_stat_cat();
    break;
    case 'save_sub_category':
        echo $action->save_sub_category();
    break;
    case 'delete_sub_category':
        echo $action->delete_sub_category();
    break;
    case 'update_stat_sub_cat':
        echo $action->update_stat_sub_cat();
    break;
    case 'save_book':
        echo $action->save_book();
    break;
    case 'delete_book':
        echo $action->delete_book();
    break;
    case 'delete_img':
        echo $action->delete_img();
    break;
    default:
    // default action here
    break;
}