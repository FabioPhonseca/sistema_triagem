<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class Equipe extends TRecord
{
    const TABLENAME   = 'BANCORH.E_PM_EQUIPE';
    const PRIMARYKEY  = 'EQUIPE';
    const IDPOLICY    = 'serial';
    protected static $database = 'banco_oracle';

    protected $tipoEquipe;

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('NOME');
        parent::addAttribute('US');
        parent::addAttribute('TIPOEQUIPE');
        for ($i = 1; $i <= 10; $i++) {
            parent::addAttribute("FLEX_CAMPO_0{$i}");
        }
    }

    // se houver model TipoEquipe:
    // public function getTipoEquipe()
    // {
    //     if (empty($this->tipoEquipe) && !empty($this->data['tipoequipe'])) {
    //         $this->tipoEquipe = new TipoEquipe($this->data['tipoequipe']);
    //     }
    //     return $this->tipoEquipe;
    // }
}
