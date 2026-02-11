<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class ErgEspecialidade extends TRecord
{
    const TABLENAME   = 'BANCORH.E_PM_ESPECIALIDADE';
    const PRIMARYKEY  = 'ESPEC';
    const IDPOLICY    = 'serial';
    protected static $database = 'banco_oracle';

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('DESCRICAO');
        for ($i = 1; $i <= 10; $i++) {
            parent::addAttribute("FLEX_CAMPO_0{$i}");
        }
    }
}
