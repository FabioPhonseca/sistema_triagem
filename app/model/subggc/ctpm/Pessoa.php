<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
use Adianti\Database\TRecord;
use Adianti\Widget\Form\THidden;

class Pessoa extends TRecord
{
    const TABLENAME = 'pessoa';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';

    protected static $database = 'banco_atendimento';

    // campos da tabela pessoa
    public static $fld_fmt = [
        'id'                  => ['caption'=>'# ID',                   'mini_caption'=>'ID'],
        'nome'                => ['caption'=>'Nome',                   'mini_caption'=>'Nome', 'max_length'=>120],
        'email_institucional' => ['caption'=>'Email Institucional',    'mini_caption'=>'Email Inst.', 'max_length'=>120],
        'email_particular'    => ['caption'=>'Email Particular',       'mini_caption'=>'Email Part.', 'max_length'=>120],
        'telefone'            => ['caption'=>'Telefone',               'mini_caption'=>'Tel.', 'max_length'=>20],
        'cpf'                 => ['caption'=>'CPF',                    'mini_caption'=>'CPF', 'max_length'=>11],
        'numfunc'             => ['caption'=>'Num.Funcionario bancorh',  'mini_caption'=>'NUMFUNC', 'max_length'=>11],
        'created_by'          => ['caption'=>'Usuário Criador',        'mini_caption'=>'Criador'],
        'created_at'          => ['caption'=>'Data de Criação',        'mini_caption'=>'Dt.Criação'],
        'updated_by'          => ['caption'=>'Usuário Atualização',    'mini_caption'=>'Usu.Atual.'],
        'updated_at'          => ['caption'=>'Data de Atualização',    'mini_caption'=>'Dt.Atual.'],
    ];

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
        parent::addAttribute('email_institucional');
        parent::addAttribute('email_particular');
        parent::addAttribute('telefone');
        parent::addAttribute('cpf');
        parent::addAttribute('numfunc');
        parent::addAttribute('created_by');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_by');
        parent::addAttribute('updated_at');
    }

    public function get_nome(){
        return  !empty($this->data['nome']) ? mb_strtoupper(trim($this->data['nome'])) : '';
    }

    public function set_nome($value){
        $this->data['nome'] = (!empty($value) && isset($value)) ? mb_strtoupper(trim($value)) : null;
    }

    public function get_cpf(){
        return  !empty($this->data['cpf']) ? mb_strtoupper(trim($this->data['cpf'])) : '';
    }

    public function set_cpf($value){
        if (TCnpjCpfUtils::validarCPF(trim($value)) == 0) {
            throw new Exception('CPF inválido');
        }
        $this->data['cpf'] = (!empty($value) && isset($value)) ? trim($value) : null;
    }
    
    public function get_email_particular(){
        return  !empty($this->data['email_particular']) ? mb_strtolower(trim($this->data['email_particular'])) : '';
    }

    public function set_email_particular($value){
        $this->data['email_particular'] = (!empty($value) && isset($value)) ? mb_strtolower(trim($value)) : null;
    }
}
?>