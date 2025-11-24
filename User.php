<?php
class User
{
    private $id;
    public $login;
    public $email;
    public $firstname;
    public $lastname;
    private $isConnected = false;
    private $mysqli;

    //Fonction construct
    public function __construct()
    {
        $this->mysqli = new mysqli('localhost', 'root', '', 'classes');
        if ($this->mysqli->connect_error) {
            die('Erreur de connexion : ' . $this->mysqli->connect_error);
        }

        if (isset($_SESSION['user_data']['id']) && $_SESSION['user_data']['id'] > 0) {
            $this->id = $_SESSION['user_data']['id'];
            $this->login = $_SESSION['user_data']['login'];
            $this->email = $_SESSION['user_data']['email'];
            $this->firstname = $_SESSION['user_data']['firstname'];
            $this->lastname = $_SESSION['user_data']['lastname'];
            $this->isConnected = true;
        }
    }

    public function __set($name, $value)
    {
        if ($name === "id" && $value > 0) {
            // dès que l’index injecte l’id, on force la reconnexion
            $this->isConnected = true;
        }

        // continue la logique normale
        $this->$name = $value;
    }

    //Inscrit un nouvel utilisateurs
    public function register($login, $password, $email, $firstname, $lastname)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO utilisateurs (login, password, email, firstname, lastname)
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('sssss', $login, $passwordHash, $email, $firstname, $lastname);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $this->connect($login, $password); // connecte automatiquement après inscription
            return $this->getAllInfos();
        } else {
            return false;
        }
    }

    //Connecte l'utilisateur
    public function connect($login, $password)
    {
        $query = "SELECT * FROM utilisateurs WHERE login = ?";
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $this->id = $user['id'];
            $this->login = $user['login'];
            $this->email = $user['email'];
            $this->firstname = $user['firstname'];
            $this->lastname = $user['lastname'];
            $this->isConnected = true;
            $_SESSION['user_id'] = $this->id;
            return true;
        }
        return false;
    }

    //Déconnecte l'utilisateur
    public function disconnect()
    {
        $this->id = null;
        $this->login = null;
        $this->email = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->isConnected = false;
    }

    //Supprime l'utilisateur
    public function delete()
    {
        if ($this->isConnected && $this->id) {
            $stmt = $this->mysqli->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $this->disconnect();
        }
    }

    //Met à jour l'utilisateur
    public function update($login, $password, $email, $firstname, $lastname,)
    {
        if ($this->isConnected = true) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->mysqli->prepare("
            UPDATE utilisateurs
            SET login = ?, password = ?, email = ?, firstname = ?, lastname = ?
            WHERE id = ?
        ");
            $stmt->bind_param('sssssi', $login, $passwordHash, $email, $firstname, $lastname, $this->id);
            $stmt->execute();

            $this->login = $login;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
        }
    }

    //Vérifie si l'utilisateur est connecté
    public function isConnected()
    {
        if ($_SESSION['user_id'] > 0) {
            return $this->isConnected = true;
        }
    }

    //Récupère toutes les infos utilisateur
    public function getAllInfos()
    {
        if ($this->id > 0 && $this->login !== null) {
            $this->isConnected = true;
        }

        if (!$this->isConnected) {
            return [
                'id' => 0,
                'login' => null,
                'email' => null,
                'firstname' => null,
                'lastname' => null
            ];
        }

        return [
            'id' => $this->id,
            'login' => $this->login,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname
        ];
    }
}



/*
 Test Objets


    //Initialisation d'un nouvel objet

    $testSubjectXX = new User($login, $email, $firstname, $lastname, $password);
    $testSubject->login = '';
    $testSubject->email = '';
    $testSubject->firstname = '';
    $testSubject->lastname = '';
    //$testSubject->password = '';

    $testSubject01 = new User($login, $email, $firstname, $lastname, $password);
    $testSubject01->login = 'NightGuard';
    $testSubject01->email = 'johnsnow@westeros.com';
    $testSubject01->firstname = 'John';
    $testSubject01->lastname = 'Snow';
    //$testSubject01->password = 'youdontknowanythingjohnsnow';

    $testSubject02 = new User($login, $email, $firstname, $lastname, $password);
    $testSubject02->login = 'ResistanceLeader';
    $testSubject02->email = 'leiaorgana@aldeeran.com';
    $testSubject02->firstname = 'Leia';
    $testSubject02->lastname = 'Organa';
    //$testSubject02->password = 'didijustkissmybrother?';

    $testSubject03 = new User($login, $email, $firstname, $lastname, $password);
    $testSubject03->login = 'HogwartsDirector';
    $testSubject03->email = 'ablusdumbledore@hogwarts.com';
    $testSubject03->firstname = 'Albus';
    $testSubject03->lastname = 'Dumbledore';
    //$testSubject03->password = '50pointstogryffindor';

    $testSubject04 = new User($login, $email, $firstname, $lastname, $password);
    $testSubject04->login = 'ChapterMaster';
    $testSubject04->email = 'vulkanhestan@nocturne.com';
    $testSubject04->firstname = 'Vulkan';
    $testSubject04->lastname = 'He\'stan';
    //$testSubject04->password = 'illneverletanyonebombardarmaggedoncivilians';

    $testSubject05 = new User($login, $email, $firstname, $lastname, $password);
    $testSubject05->login = 'TravelerFromTheFuture';
    $testSubject05->email = 'martymcfly@hillvalley.com';
    $testSubject05->firstname = 'Marty';
    $testSubject05->lastname = 'Mcfly';
    //$testSubject05->password = 'areyoutellingmeyoumadeatimemachineoutofadelerean?';

*/