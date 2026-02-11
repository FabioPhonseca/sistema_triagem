<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class ErgMedico extends TRecord
{
    const TABLENAME   = 'BANCORH.MEDICOS';
    const PRIMARYKEY  = 'CRM';
    const IDPOLICY    = 'serial';
    protected static $database = 'banco_oracle';

    protected $funcionario;

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('NOME');
        parent::addAttribute('NUMFUNC');
        parent::addAttribute('DATAINATIV');
        parent::addAttribute('ID_PESSOA');
        for ($i = 1; $i <= 15; $i++) {
            parent::addAttribute("FLEX_CAMPO_0{$i}");
        }
    }

    public function getFuncionario()
    {
        if (empty($this->funcionario) && !empty($this->data['NUMFUNC'])) {
            $this->funcionario = new ErgFuncionario($this->data['NUMFUNC']);
        }
        return $this->funcionario;
    }
}
