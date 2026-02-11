<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class Tema extends TRecord
{
    const TABLENAME = 'tema';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';

    protected static $database = 'banco_atendimento';

    public static $fld_fmt_tema = [
        'id'   => ['caption'=>'# ID', 'mini_caption'=>'ID'],
        'tema' => ['caption'=>'Tema', 'mini_caption'=>'Tema'],
    ];    

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('tema');
    }

    public function get_tema()
    {
        return empty($this->data['tema']) ? '' : $this->data['tema'];
    }

    public function set_tema($value){
       $this->data['tema'] = !empty($value) ? mb_strtoupper($value) : null;
    }
}
?>