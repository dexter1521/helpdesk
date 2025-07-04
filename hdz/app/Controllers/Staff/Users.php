<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Controllers\Staff;


use App\Controllers\BaseController;
use Config\Services;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Users extends BaseController
{
    public function manage()
    {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if($this->request->getPost('do') == 'remove'){
            $validation = Services::validation();
            $validation->setRule('user_id',lang('Admin.form.user'), 'required|is_natural_no_zero');
            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            }elseif (defined('HDZDEMO')){
                $error_msg = 'This is not possible in demo version.';
            }else{
                $this->client->deleteAccount($this->request->getPost('user_id'));
                $this->session->setFlashdata('form_success',lang('Admin.users.userRemoved'));
                return redirect()->to(current_url());
            }
        }
        $pager = $this->client->manage();
        return view('staff/users',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'users_list' => $pager['result'],
            'pager' => $pager['pager']
        ]);
    }

    public function newAccount()
    {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if($this->request->getPost('do') == 'submit')
        {
            $validation = Services::validation();
            $validation->setRule('fullname', lang('Admin.form.fullName'),'required',[
                'required' => lang('Admin.error.enterFullName')
            ]);
            $validation->setRule('email','Email','required|valid_email|is_unique[users.email]',[
                'required' => lang('Admin.error.enterValidEmail'),
                'valid_email' => lang('Admin.error.enterValidEmail'),
                'is_unique' => lang('Admin.error.emailTaken'),
            ]);
            $validation->setRule('password','Password','required|min_length[6]|max_length[16]',[
                'required' => lang('Admin.error.enterPassword'),
                'min_length' => lang('Admin.error.enterPassword')
            ]);
            $validation->setRule('status','Status','required|in_list[0,1]',[
                'required' => lang('Admin.error.invalidStatus'),
                'in_list' => lang('Admin.error.invalidStatus'),
            ]);
            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            }elseif (defined('HDZDEMO')){
                $error_msg = 'This is not possible in demo version.';
            }else{
                $client_id = $this->client->createAccount($this->request->getPost('fullname'), $this->request->getPost('email'), $this->request->getPost('password'), ($this->request->getPost('notify') ? true : false));
                if($this->request->getPost('status') == '0'){
                    $this->client->update(['status' => 0], $client_id);
                }
                $this->session->setFlashdata('form_success', lang('Admin.users.accountCreated'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/users_form',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
        ]);
    }

    public function editAccount($user_id)
    {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if(!$user = $this->client->getRow(['id' => $user_id])){
            return redirect()->route('staff_users');
        }
        if($this->request->getPost('do') == 'submit')
        {
            $validation = Services::validation();
            $validation->setRule('fullname', lang('Admin.form.fullName'),'required',[
                'required' => lang('Admin.error.enterFullName')
            ]);
            if($user->email != $this->request->getPost('email')){
                $validation->setRule('email','Email','required|valid_email|is_unique[users.email]',[
                    'required' => lang('Admin.error.enterValidEmail'),
                    'valid_email' => lang('Admin.error.enterValidEmail'),
                    'is_unique' => lang('Admin.error.emailTaken'),
                ]);
            }
            if($this->request->getPost('password')){
                $validation->setRule('password','Password','required|min_length[6]|max_length[16]',[
                    'required' => lang('Admin.error.enterPassword'),
                    'min_length' => lang('Admin.error.enterPassword')
                ]);
            }
            $validation->setRule('status','Status','required|in_list[0,1]',[
                'required' => lang('Admin.error.invalidStatus'),
                'in_list' => lang('Admin.error.invalidStatus'),
            ]);
            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            }elseif (defined('HDZDEMO')){
                $error_msg = 'This is not possible in demo version.';
            }else{
                $this->client->update([
                    'fullname' => esc($this->request->getPost('fullname')),
                    'email' => esc($this->request->getPost('email')),
                    'password' => ($this->request->getPost('password') ? password_hash($this->request->getPost('password'), PASSWORD_BCRYPT) : $user->password),
                    'status' => $this->request->getPost('status')
                ], $user->id);
                $this->session->setFlashdata('form_success', lang('Admin.users.accountUpdated'));
                return redirect()->to(current_url());
            }
        }
        if(defined('HDZDEMO')){
            $user->email = '[Hidden in demo]';
        }
        return view('staff/users_form',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'user' => $user
        ]);
    }

    public function importUsers()
    {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        // Verificar que PhpSpreadsheet esté disponible
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            $error_msg = 'Error: PhpSpreadsheet no está disponible. Ejecuta: composer install';
            return view('staff/users_import',[
                'error_msg' => $error_msg
            ]);
        }

        if($this->request->getPost('do') == 'upload')
        {
            $validation = Services::validation();
            $validation->setRule('excel_file', 'Archivo Excel', 'uploaded[excel_file]|max_size[excel_file,5120]|ext_in[excel_file,xlsx,xls,csv]',[
                'uploaded' => 'Debes seleccionar un archivo Excel',
                'max_size' => 'El archivo no puede superar los 5MB',
                'ext_in' => 'El archivo debe ser de tipo Excel (.xlsx, .xls) o CSV'
            ]);

            if($validation->withRequest($this->request)->run() == false){
                $error_msg = $validation->listErrors();
            }else{
                $file = $this->request->getFile('excel_file');
                if($file && $file->isValid() && !$file->hasMoved()){
                    try {
                        $result = $this->processExcelFile($file);
                        $success_msg = "Importación completada: {$result['imported']} usuarios importados, {$result['errors']} errores.";
                        if($result['error_details']){
                            $error_msg = "Errores encontrados:\n" . implode("\n", $result['error_details']);
                        }
                    } catch (\Exception $e) {
                        $error_msg = 'Error al procesar el archivo: ' . $e->getMessage();
                    }
                }else{
                    $error_msg = 'Error al subir el archivo';
                }
            }
        }

        return view('staff/users_import',[
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => isset($success_msg) ? $success_msg : null,
        ]);
    }

    private function processExcelFile($file)
    {
        $imported = 0;
        $errors = 0;
        $error_details = [];

        // Cargar el archivo Excel
        $spreadsheet = IOFactory::load($file->getTempName());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Saltar la primera fila si contiene encabezados
        $header_row = array_shift($rows);
        
        // Validar que el archivo tenga al menos las columnas necesarias
        if(count($header_row) < 3){
            throw new \Exception('El archivo debe tener al menos 3 columnas: Nombre Completo, Email, Estado');
        }

        foreach($rows as $index => $row){
            $row_number = $index + 2; // +2 porque empezamos en la fila 2 (después del header)
            
            // Saltar filas vacías
            if(empty(array_filter($row))){
                continue;
            }

            $fullname = trim(isset($row[0]) ? $row[0] : '');
            $email = trim(isset($row[1]) ? $row[1] : '');
            $status = trim(isset($row[2]) ? $row[2] : '1');
            $password = trim(isset($row[3]) ? $row[3] : '');
            if(empty($password)){
                $password = $this->generateRandomPassword();
            }

            // Validaciones
            if(empty($fullname)){
                $errors++;
                $error_details[] = "Fila {$row_number}: Nombre completo es requerido";
                continue;
            }

            if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errors++;
                $error_details[] = "Fila {$row_number}: Email inválido o vacío ({$email})";
                continue;
            }

            // Verificar si el email ya existe
            if($this->client->getRow(['email' => $email])){
                $errors++;
                $error_details[] = "Fila {$row_number}: El email {$email} ya existe";
                continue;
            }

            // Validar estado (debe ser 0 o 1)
            $status = in_array($status, ['0', '1', 0, 1]) ? (int)$status : 1;

            try {
                // Crear el usuario
                $client_id = $this->client->createAccount($fullname, $email, $password, false);
                
                // Actualizar el estado si es diferente a 1
                if($status == 0){
                    $this->client->update(['status' => 0], $client_id);
                }

                $imported++;
            } catch (\Exception $e) {
                $errors++;
                $error_details[] = "Fila {$row_number}: Error al crear usuario - " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
            'error_details' => $error_details
        ];
    }

    private function generateRandomPassword($length = 8)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }

    public function downloadTemplate()
    {
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        // Verificar que PhpSpreadsheet esté disponible
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $error_msg = 'Error: PhpSpreadsheet no está disponible. Ejecuta: composer install';
            return view('staff/users_import',[
                'error_msg' => $error_msg
            ]);
        }

        // Crear archivo Excel de ejemplo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->setCellValue('A1', 'Nombre Completo');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Estado (1=Activo, 0=Inactivo)');
        $sheet->setCellValue('D1', 'Contraseña (opcional)');

        // Datos de ejemplo
        $sheet->setCellValue('A2', 'Juan Pérez');
        $sheet->setCellValue('B2', 'juan.perez@ejemplo.com');
        $sheet->setCellValue('C2', '1');
        $sheet->setCellValue('D2', 'mipassword123');

        $sheet->setCellValue('A3', 'María García');
        $sheet->setCellValue('B3', 'maria.garcia@ejemplo.com');
        $sheet->setCellValue('C3', '1');
        $sheet->setCellValue('D3', '');

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);

        // Crear el writer
        $writer = new Xlsx($spreadsheet);

        // Configurar headers para descarga
        $filename = 'plantilla_usuarios_' . date('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit();
    }
}