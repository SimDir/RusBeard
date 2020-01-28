<?php

namespace mvcrb;

defined('ROOT') OR die('No direct script access.');

/**
 * Description of UserModel
 *
 * @author ivank
 */
class UserModel extends Model {

    private $TableName;

    public function __construct() {
        parent::__construct();   
        Session::init();
        Session::set('BrowserHesh', mvcrb::BrouserHash());
        $this->TableName = 'user';
    }
    public function SetExel($ExelFileToRiad) {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(TRUE);
        $spreadsheet = $reader->load($ExelFileToRiad);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn(); 
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        $html = '';
        for ($row = 2; $row <= $highestRow; ++$row) {
            $value = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $html .= '<p>' . $value . '</p>' . PHP_EOL;
        }
//        for ($col = 1; $col <= $highestColumnIndex; ++$col) {
//            $value = $worksheet->getCellByColumnAndRow($col, 2)->getValue();
//            $html .= '<p>' . $value . '</p>' . PHP_EOL;
//        }

        return $html;
    }
    public function GetExel() {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $tempbean = $this->findAll($this->TableName);
        $tempdata = $this->exportAll($tempbean, TRUE);
        $sheet->setCellValue('A1', 'id');
        $sheet->setCellValue('B1', 'Имя');
        $sheet->setCellValue('C1', 'Фамилия');
        $sheet->setCellValue('D1', 'Логин');
        $sheet->setCellValue('E1', 'E-mail');
        $sheet->setCellValue('F1', 'Номер телефона');
        $sheet->setCellValue('G1', 'Пароль');
        $sheet->setCellValue('H1', 'Назначенный уровень доступа');
        $sheet->setCellValue('I1', 'Дата и время добавления на сайт');
        $sheet->setCellValue('J1', 'Последний раз входил на сайт');
        $sheet->setCellValue('K1', 'Данные о используемом последний раз браузере');
        $sheet->setCellValue('L1', 'Последний IP с которого заходил пользователь');
        $Rows=1;
        foreach ($tempdata as $value) {
//            dd($value);
            $Rows++;
            $sheet->setCellValueByColumnAndRow(1, $Rows, $value["id"]);
            $sheet->setCellValueByColumnAndRow(2, $Rows, $value["firstname"]);
            $sheet->setCellValueByColumnAndRow(3, $Rows, $value["lastname"]);
            $sheet->setCellValueByColumnAndRow(4, $Rows, $value["login"]);
            $sheet->setCellValueByColumnAndRow(5, $Rows, $value["email"]);
            
//            $sheet->setCellValueByColumnAndRow(6, $Rows, $value["phone"]);
            $CellByColumnAndRow = $sheet->getCellByColumnAndRow(6, $Rows);
            $CellByColumnAndRow->setDataType(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT)->setValue(str_replace('+7','8',$value["phone"]));
            
            $sheet->setCellValueByColumnAndRow(7, $Rows, $value["password"]);
            $sheet->setCellValueByColumnAndRow(8, $Rows, $value["role"]);
            $sheet->setCellValueByColumnAndRow(9, $Rows, $value["registredatetime"]);
            
            
            $sheet->setCellValueByColumnAndRow(10, $Rows, $value["lastlogin"]);
            $sheet->setCellValueByColumnAndRow(11, $Rows, $value["browser"]);
            $sheet->setCellValueByColumnAndRow(12, $Rows, $value["browserip"]);
        }
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=users.xls");
        $writer->save('php://output');
//        return $writer->save(SITE_DIR.'hello_world.xlsx');
        die();
    }
    public function GetCountUser() {
        return $this->count($this->TableName);
    }

    public function GetList($PostData = null) {
        $start = $PostData->start ? $PostData->start : 0;
        $limit = $PostData->limit ? $PostData->limit : 10;
        $List['count'] = $this->count($this->TableName);
        if (isset($PostData->data) && $PostData->data !== '') {
            $order['data'] = $PostData->data;
            $order['dir'] = $PostData->dir;
        } else {
            $order = null;
        }

        if (is_array($order)) {
            $tempbean = $this->findAll($this->TableName, 'ORDER BY ' . $order['data'] . ' ' . $order['dir'] . ' LIMIT ' . $start . ', ' . $limit);
        } else {
            $tempbean = $this->findAll($this->TableName, 'LIMIT ' . $start . ', ' . $limit);
        }
        if ($tempbean) {

            $tempdata = $this->exportAll($tempbean, TRUE);
            foreach ($tempdata as $key => $value) {
                // удаляю пароли из массива
                // нехуя их ваще посылать кудато. да они захешированы но всеравно нннааадаа
                unset($tempdata[$key]['password']);
            }
            $List['data'] = $tempdata;
            return $List;
        }
        return FALSE;
    }

    public function GetUserID($id) {
        $u = $this->findOne($this->TableName, 'id = ?', array($id));
        if ($u) {
            return $u->export();
        }
        return FALSE;
    }

    public function DellUser($id = 0) {
        $User = $this->load($this->TableName, $id);
        return $this->trash($User);
    }

    public function GetCurrentUser() {
        $var = Session::get('LoggedUser');
        $AnonimUser = ['Name' => 'anonim', 'login' => 'anonim', 'role' => 0];
//        dd($var);
        if ($var) {
            $user = $this->findOne($this->TableName, 'email = ?', array($var['email']));

            if ($user) {
                $ret = $user->export();
                unset($ret["password"]);
                return $ret;
            }
            return $AnonimUser;
        }

        return $AnonimUser;
    }

    public function ChekMail($mail) {

        if ($this->count($this->TableName, "email = ?", array($mail)) > 0) {
//            $errors[] = 'Пользователь с таким Email уже существует!';
            return TRUE;
        }
        return FALSE;
    }

    public function ChekUserLogin($login) {
        //проверка на существование одинакового логина
        if ($this->count($this->TableName, "login = ?", [$login]) > 0) {
//            $errors[] = 'Пользователь с таким логином уже существует!';
            return TRUE;
        }
        return FALSE;
    }

    public function CreateUser($email, $password, $login, $role = 100, $firstname = '', $lastname = '', $phone = '', $registredatetime = '') {

        $user = $this->Dispense($this->TableName);
        $user->firstname = $firstname;
//        $user->middlename = $middlename;
        $user->lastname = $lastname;

        $user->login = $login;
        $user->email = $email;
        $user->phone = $phone;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        //пароль нельзя хранить в открытом виде, 
        //мы его шифруем при помощи функции password_hash для php > 5.6

        $user->role = empty($role) ? 100 : $role;

//        $user->activation = TRUE;
        if ($registredatetime == '') {
            $registredatetime = date('Y-m-d H:i:s');
        }
        $user->registredatetime = $registredatetime;

        return $this->store($user);
    }

    public function EditUser($email, $password, $login, $role = 100, $firstname = '', $lastname = '', $phone = '', $id = 0) {

//        $user = $this->dispense($this->TableName);
        $user = $this->findOne($this->TableName, 'id = ?', array($id));
        $user->firstname = $firstname;
//        $user->middlename = $middlename;
        $user->lastname = $lastname;

        $user->login = $login;
        $user->email = $email;
        $user->phone = $phone;
        //пароль нельзя хранить в открытом виде, 
        //мы его шифруем при помощи функции password_hash для php > 5.6
        if ($password <> '') {
            $user->password = password_hash($password, PASSWORD_DEFAULT);
        }

        $user->role = $role;

        return $this->store($user);
    }

    public function login($email, $password) {
        $user = $this->findOne($this->TableName, 'email = ?', array($email));
        if (!$user) {
            $user = $this->findOne($this->TableName, 'login = ?', array($email));
        }
        if ($user) {
            //логин существует
            if (password_verify($password, $user->password)) {
                //если пароль совпадает, то нужно авторизовать пользователя

                $user->lastlogin = date('Y-m-d H:i:s');
                $user->browser = $_SERVER['HTTP_USER_AGENT'];
                $user->ip = $_SERVER['REMOTE_ADDR'];
                $user->session = mvcrb::BrouserHash();
                $this->store($user);
                $VarUser = $user->export();
                unset($VarUser['password']); // убираем хеш пароля.
                Session::set('LoggedUser', $VarUser);
                return TRUE;
            }
        }

        return FALSE;
    }

}
