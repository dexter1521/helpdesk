<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */
namespace App\Libraries;
use Config\Database;

class Settings
{
    protected $vars;
    public function config($var)
    {
        if(!$this->vars)
        {
            $db = Database::connect();
            $builder = $db->table('hdzfv_config');
            $this->vars = $builder->get()->getRow();
        }
        return (isset($this->vars->$var) ? $this->vars->$var : '');
    }

    public function save($field,$value=''){
        if(!is_array($field)){
            $field = [$field => $value];
        }
        $db = Database::connect();
        $result = $db->table('hdzfv_config')
            ->set($field)
            ->where('id', 1)
            ->update();
        
        return $result;
    }

    public function getLogo()
    {
        if($logo = $this->config('logo')){
            return base_url('upload/'.$logo);
        }else{
            return base_url('assets/helpdeskz/images/logo.png');
        }
    }
}