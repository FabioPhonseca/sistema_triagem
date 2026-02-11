<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */

class ErgAgenda extends TRecord
{
    const TABLENAME   = 'BANCORH.E_PM_AGENDA';
    const PRIMARYKEY  = 'AGENDA';
    const IDPOLICY    = 'serial';
    protected static $database = 'banco_oracle';

    protected $equipe;
    protected $especialidade;
    protected $medico;
    protected $profissional;

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('EQUIPE');
        parent::addAttribute('CRM');
        parent::addAttribute('US');
        parent::addAttribute('DATA');
        parent::addAttribute('HORAINI');
        parent::addAttribute('HORAFIM');
        parent::addAttribute('VAGASMEDICO');
        parent::addAttribute('VAGASEQUIPE');
        parent::addAttribute('QUANTAG');
        parent::addAttribute('ESPEC');
        /*for ($i = 1; $i <= 10; $i++) {
            parent::addAttribute("FLEX_CAMPO_0{$i}");
        }*/
        parent::addAttribute('PROFIS');
        parent::addAttribute('VAGASPROFIS');
    }

    public function getEquipe()
    {
        if (empty($this->equipe) && !empty($this->data['EQUIPE'])) {
            $this->equipe = new ErgEquipe($this->data['EQUIPE']);
        }
        return $this->equipe;
    }

    public function getEspecialidade()
    {
        if (empty($this->especialidade) && !empty($this->data['ESPEC'])) {
            $this->especialidade = new ErgEspecialidade($this->data['ESPEC']);
        }
        return $this->especialidade;
    }

    public function getMedico()
    {
        if (empty($this->medico) && !empty($this->data['CRM'])) {
            $this->medico = new ErgMedico($this->data['CRM']);
        }
        return $this->medico;
    }

    public function getProfissional()
    {
        if (empty($this->profissional) && !empty($this->data['PROFIS'])) {
            $this->profissional = new ErgFuncionario($this->data['PROFIS']);
        }
        return $this->profissional;
    }

    
    public function get_data(){
        return  !empty($this->data['DATA']) ? TDateUtils::DateUsdMyToBr($this->data['DATA']) : '';
    }

    public function set_data($value){
        throw new Exception('Não é possível alterar dados da agenda BANCORH.');
    }  
}
