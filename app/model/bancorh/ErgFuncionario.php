<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;

class ErgFuncionario extends TRecord
{
    const TABLENAME   = 'BANCORH.FUNCIONARIOS';
    const PRIMARYKEY  = 'NUMERO';
    const IDPOLICY    = 'serial';
    protected static $database = 'banco_oracle';

    public static $fld_fmt = [
        'CPF'             => ['caption' => 'CPF',                  'mini_caption' => 'CPF'],
        'NOME'            => ['caption' => 'Nome',                'mini_caption' => 'Nome'],
        'SEXO'            => ['caption' => 'Sexo',                'mini_caption' => 'Sexo'],
        'DTNASC'          => ['caption' => 'Data Nascimento',     'mini_caption' => 'Dt.Nasc.'],
        'PAI'             => ['caption' => 'Nome do Pai',         'mini_caption' => 'Pai'],
        'MAE'             => ['caption' => 'Nome da Mãe',         'mini_caption' => 'Mãe'],
        'E_MAIL'          => ['caption' => 'E-mail',              'mini_caption' => 'E-mail'],
        'TELEFONE'        => ['caption' => 'Telefone',            'mini_caption' => 'Tel.'],
        'NUMTEL_CELULAR'  => ['caption' => 'Telefone Celular',    'mini_caption' => 'Celular'],
        'FLEX_CAMPO_20'   => ['caption' => 'E-mail 2',            'mini_caption' => 'E-mail2']
    ];

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('CPF');
        parent::addAttribute('NOME');
        parent::addAttribute('SEXO');
        parent::addAttribute('DTNASC');
        parent::addAttribute('PAI');
        parent::addAttribute('MAE');
        parent::addAttribute('E_MAIL');
        parent::addAttribute('TELEFONE');
        parent::addAttribute('NUMTEL_CELULAR');
        parent::addAttribute('FLEX_CAMPO_20');
    }

    public function get_dtnasc(){
        return  !empty($this->data['DTNASC']) ? TDateUtils::DateUsdMyToBr($this->data['DTNASC']) : '';
    }

    public function set_dtnasc($value){
        throw new Exception('Não é possível alterar dados do funcionário BANCORH.');
    }

    public function get_E_MAIL(){
        return $this->data['E_MAIL'] ?? $this->data['FLEX_CAMPO_20'] ?? '';

    }

    public function set_E_MAIL($value){
        throw new Exception('Não é possível alterar dados do funcionário BANCORH.');
    }
    

    public function get_TELEFONE(){
        return $this->data['NUMTEL_CELULAR'] ?? $this->data['TELEFONE'] ?? '';

    }

    public function set_TELEFONE($value){
        throw new Exception('Não é possível alterar dados do funcionário BANCORH.');
    }

    public static function getFuncionarioFromCPF($cpf): ?ErgFuncionario {
        $retorno = null;
        
        $criteria = new TCriteria;
        $criteria->add(new TFilter('CPF', '=', preg_replace('/\D/', '', $cpf)));
        
        $repository = new TRepository(self::class);
        $objects = $repository->load($criteria);
        
        if (!empty($objects))
            $retorno = $objects[0];

        return $retorno;
    }
}
