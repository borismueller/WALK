<?php

require_once '../repository/UserRepository.php';
require_once '../repository/FileRepository.php';
require_once '../repository/UserFileRepository.php';

/**
 * Siehe Dokumentation im DefaultController.
 */
class UserController
{
    public function index()
    {
        $userRepository = new UserRepository();

        $view = new View('user_index');
        $view->title = 'Benutzer';
        $view->heading = 'Benutzer';
        $view->users = $userRepository->readAll();
        $view->display();
    }

    public function create()
    {
        $view = new View('user_create');
        $view->title = 'Register';
        $view->display();
    }

    public function doCreate()
    {
        if ($_POST['Submit']) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $userRepository = new UserRepository();
            $userRepository->create($username, $password);
        }

        // Anfrage an die URI /user weiterleiten (HTTP 302)
        //header('Location: /user');
    }

    public function delete()
    {
        $userRepository = new UserRepository();
        $userRepository->deleteById($_GET['id']);

        // Anfrage an die URI /user weiterleiten (HTTP 302)
        header('Location: /user');
    }

    public function login() {
      $view = new View('user_login');
      $view->title = 'Login';
      $view->display();
    }

    public function doLogin() {
      if ($_POST['Submit']) {
          $username = $_POST['username'];
          $password = $_POST['password'];

          $userRepository = new UserRepository();

          if ($userRepository->login($username, $password)){
              //login korrekt
              $_SESSION['username'] = $username;
              echo "gj";
              echo $_SESSION['username'];
              //TODO: goto user-area
          } else {
              //Fehler
              echo "shit";
              //TODO:
          }

          //header('Location: /user');
      }
    }

    public function upload() {
      $view = new View('user_upload');
      $view->title = 'Upload';
      $view->display();
    }

    public function doUpload() {
      //TODO: evtl. FileController ??
      //username, speicherung (in Repository?)
      if ($_POST['Submit']) {
          $name = $_POST['name'];
          $tags = $_POST['tags'];
          $acces = $_POST['acces'];
          $file = $_FILES['file'];

          if (!isset($_SESSION['username'])){
              echo "not logged in";
          }
          $username = $_SESSION['username'];

          $path = "../data/files/".$username."/".$file['name'];

          echo $path;

          if (!is_dir("../data/files/".$username)) {
              //create dir if it doesnt exist
              mkdir("../data/files/".$username, 0777, true);
          }

          if (move_uploaded_file($file["tmp_name"], $path)){
            $fileRepository = new FileRepository();
            $fileRepository->create($name, $tags, $path);

            $file_id = $fileRepository->getId($name);
            $file_id = $file_id->id;
            var_dump($file_id);
            echo "<br>";

            $userRepository = new userRepository();
            $user_id = $userRepository->getId($username);
            $user_id = $user_id->id;

            var_dump($user_id);

            $userFileRepository = new UserFileRepository();
            $userFileRepository->create($user_id, $file_id, $tags);
          }
          else {
            echo "fkc";
          }
      }
    }
}
