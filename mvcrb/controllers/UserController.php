<?php

namespace mvcrb;

defined('ROOT') OR die('No direct script access.');

/**
 * Description of UserController
 *  
 * @author ivan kolotilkin
 */
class UserController extends Controller {

    private $User;

    public function __construct() {
        parent::__construct();
        $this->User = new UserModel();//$this->GetModel('User'); 
        

//        dd(apacher_request_headers());
//        $this->View->SetWivePath(TEMPLATE_DIR.'UserController'.DS);
    }

    public function IndexAction($id=null) {
        $UserVars = $this->User->GetCurrentUser();
        if ($UserVars['role'] == 0) {
            return mvcrb::Redirect('/user/login');
        }
        $redir = Session::get('UserRedirect');
        if ($redir) {
            Session::set('UserRedirect', null);
            return mvcrb::Redirect($redir);
        }
        $this->View->VarSetArray($UserVars);

        $email = $UserVars['email'];
        $default = "http://agatech.ru/public/img/favicon.png";
        $size = 256;
        $this->View->GravUrl = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default) . "&s=" . $size;
        $this->View->content = $this->View->execute('UserHome.html');
        return $this->View->execute('index.html', TEMPLATE_DIR);
    }

    public function LogoutAction() {
        Session::init();
        Session::DestroyAll();
        return mvcrb::Redirect('/');
    }

    public function LoginAction() {
        if ($this->User->GetCurrentUser()['role'] > 0) {
            return mvcrb::Redirect('/user');
        }
        $this->View->title = 'Вход пользователя';
        if ($this->POST) {
            $user = json_decode($this->REQUEST);
            return $this->User->login($user->email, $user->password);
        }
        $this->View->state = 'Login';
        $this->View->content = $this->View->execute('UserForms.html');
        return $this->View->execute('index.html', TEMPLATE_DIR);
    }

    public function RegistreAction() {
        if ($this->User->GetCurrentUser()['role'] > 0) {
            return mvcrb::Redirect('/user');
        }
        if ($this->POST) {
            $PostUserData = json_decode($this->REQUEST,true);
            if($this->User->ChekUserLogin($PostUserData['login'])){
                return ['Error'=>'Логин уже занят'];
            }
            if($this->User->ChekMail($PostUserData['email'])){
                return ['Error'=>'email уже занят'];
            }
            $success=$this->User->CreateUser($PostUserData['email'], $PostUserData['password'], $PostUserData['login']);
            
            return ['Success'=>$success];
        }
        $this->View->title = 'Регистрация пользователя';
        $this->View->state = 'Registre';
        $this->View->content = $this->View->execute('UserForms.html');
        return $this->View->execute('index.html', TEMPLATE_DIR);
    }

    public function GetAction() {
        if ($this->POST)
            return $this->User->GetCurrentUser();
        return mvcrb::Redirect('/user');
    }

    private function UserPostRegistre() {
//        echo '<pre>';
//        $json = json_decode(file_get_contents('php://input'));
        $ErrorMsg = "";
//        $reCaptchaResponse = filter_input(INPUT_POST, "g-recaptcha-response", FILTER_SANITIZE_STRING);
//        $reCaptchaURl = 'https://www.google.com/recaptcha/api/siteverify';
//        $reCaptchaKey = '6LeWUSIUAAAAADoe0WV2kupo4UeW-9OXAdMXx53f';
//        $reCaptchaPOST = $reCaptchaURl . '?secret=' . $reCaptchaKey . '&response=' . $reCaptchaResponse . '&remoteip=' . $_SERVER['REMOTE_ADDR'];
//
//        $ret = json_decode(file_get_contents($reCaptchaPOST));
//        if ($ret->success == false) {
//            $ErrorMsg .= '<p class="error">reКапча введена не верно</p>';
//        }

        $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);

        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $this->View->email = $email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Not a valid email
            $ErrorMsg .= '<p class="error">Введенный адрес электронной почты не является действительным</p>';
        }

        if ($this->User->ChekMail($email)) {
            $ErrorMsg .= '<p class="error">Введенный адрес электронной почты уже используется</p>';
        }

        $login = filter_input(INPUT_POST, "login", FILTER_SANITIZE_STRING);
        $this->View->login = $login;
        if ($this->User->ChekUserLogin($login)) {
            $ErrorMsg .= '<p class="error">Введенный логин уже используется. попробуйте другой</p>';
        }
        $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING);
        $this->View->name = $name;
        $middlename = filter_input(INPUT_POST, "middlename", FILTER_SANITIZE_STRING);
        $this->View->middlename = $middlename;
        $surname = filter_input(INPUT_POST, "surname", FILTER_SANITIZE_STRING);
        $this->View->surname = $surname;

        $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
        $passwordonf = filter_input(INPUT_POST, "passwordonf", FILTER_SANITIZE_STRING);
        if ($passwordonf != $password) {
            $ErrorMsg .= '<p class="error">Повторный пароль введен не верно!</p>';
        }

        if (empty($ErrorMsg)) {
            //ошибок нет, теперь регистрируем
            $this->View->uid = $this->User->CreateUser($email, $password, $login, $name, $middlename, $surname);

            $this->View->content = $this->View->execute('RegFinish.html');
            return $this->View->execute('index.html');
        }
//        echo $ErrorMsg;
        $this->View->ErrorMsg = $ErrorMsg;
        $this->View->content = $this->View->execute('FormRegistre.html');
        return $this->View->execute('index.html');
    }

    public function ListAction($CamId = 0) {
        $View = $this->View;
        $User = $this->User;
        $u = $User->GetCurrentUser();
        if ($u['role'] < 400) {
            return;
//            $View->content = $View->execute('UserList.html');
        }

        $View->SetWivePath($this->Config()['App_Templates_Dir'] . 'UserController' . DS);

        $View->content = $View->execute('UsersList.html');

        return $this->View->execute('index.html');
    }

    public function ActionSave($UserNew = false) {
        $log = new LogModel();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $UserArr = json_decode($_POST['User'], true);
            $User = $this->User;
            if ($User->GetCurrentUser()['role'] < $UserArr['role']) {
                $UserArr['role'] = $User->GetCurrentUser()['role'];
            }

            if ($UserNew) {
                if ($User->ChekUserLogin($UserArr['login'])) {
                    $uret = $UserArr['login'];
                    $res = array('RetMsg' => "Пользователь с логином $uret уже существует");
                    return json_encode($res);
                }
                if ($UserArr['email'] and $User->ChekMail($UserArr['email'])) {
                    $uret = $UserArr['email'];
                    $res = array('RetMsg' => "Пользователь с почтой $uret уже существует");
                    return json_encode($res);
                }

                $uret = $User->CreateUser($UserArr['email'], $UserArr['password'], $UserArr['login'], $UserArr['role'],
                        $UserArr['name'], $UserArr['middlename'], $UserArr['surname'], $UserArr['phone']);
                $res = array('RetMsg' => "Пользователь с id=$uret добавлен");
                $log->Log("Пользователь с id=$uret добавлен");
            } else {
                $uret = $User->SaveUser($UserArr['id'], $UserArr['email'], $UserArr['password'],
                        $UserArr['login'], $UserArr['name'], $UserArr['middlename'], $UserArr['surname'],
                        $UserArr['role'], $UserArr['phone']);
                $res = array('RetMsg' => "Пользователь с id=$uret сохранен");
                $log->Log("Пользователь с id=$uret сохранен");
            }
            return json_encode($res);
        }
        return json_encode(['RetMsg' => "Ошибка сохранения Пользователя"]);
    }

    public function ActionDelete($Id = 0) {
        $log = new LogModel();
        $user = $this->User;
        $user->DellUser(intval($Id));
        $res = array('RetMsg' => "Пользователь с id = $Id безвозвратно удален из системы. поманям павшего героя сервера");
        $log->Log("Пользователь с id=$Id безвозвратно удален из системы.");
        return json_encode($res);
    }

    public function ApiListAction($start = 0, $limit = 100, $s = null) {
//        return json_encode(urldecode($s));
        $User = $this->User;
        $u = $User->GetCurrentUser();
        if ($u['role'] < 400) {
            return;
//            $View->content = $View->execute('UserList.html');
        }
        $UC = $User->GetCountUser();
        if ($s) {
            $order = array('search' => urldecode($s));
            $u = $User->GetListUser($start, $limit, $order);
        } else {
            $u = $User->GetListUser($start, $limit);
        }
        if ($u) {
            $CU = count($u);
            if ($s) {
                $order = array('search' => urldecode($s));
                $SCU = $User->GetListUser(0, $UC, $order);
                $SCU = count($SCU);
            } else {
                $SCU = 0;
            }
        } else {
            $CU = 0;
            $SCU = 0;
        }
        $ret = array('User' => $u,
            'UserCount' => $UC,
            'AllCount' => $CU,
            'SCount' => $SCU
        );
        return json_encode($ret);
    }

}
