<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class ErgVinculo extends TRecord
{
    const TABLENAME   = 'BANCORH.VINCULOS';
    const PRIMARYKEY  = 'NUMFUNC'; // PK composta
    const IDPOLICY    = 'serial';
    protected static $database = 'banco_oracle';

    protected $funcionario;

    public static $fld_fmt = [
        'NUMFUNC'   => ['caption' => 'Número Funcionário',   'mini_caption' => 'Num.Func'],
        'NUMERO'    => ['caption' => 'Número',               'mini_caption' => 'Número'],
        'DTNOM'     => ['caption' => 'Data Nomeação',        'mini_caption' => 'Dt.Nomeação'],
        'DTPOSSE'   => ['caption' => 'Data Posse',          'mini_caption' => 'Dt.Posse'],
        'DTEXERC'   => ['caption' => 'Data Exercício',      'mini_caption' => 'Dt.Exercício'],
        'DTAPOSENT' => ['caption' => 'Data Aposentadoria',  'mini_caption' => 'Dt.Aposent.'],
        'DTVAC'     => ['caption' => 'Data Vacância',       'mini_caption' => 'Dt.Vacância'],
        'REGIMEJUR' => ['caption' => 'Regime Jurídico',     'mini_caption' => 'Reg.Jurídico'],
        'TIPOVINC'  => ['caption' => 'Tipo Vínculo',        'mini_caption' => 'Tp.Vínculo'],
        'MATRIC'    => ['caption' => 'Matrícula',           'mini_caption' => 'Matrícula'],
    ];


    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('NUMFUNC');
        parent::addAttribute('NUMERO');
        parent::addAttribute('DTNOM');
        parent::addAttribute('DTPOSSE');
        parent::addAttribute('DTEXERC');
        parent::addAttribute('DTAPOSENT');
        parent::addAttribute('DTVAC');
        parent::addAttribute('REGIMEJUR');
        parent::addAttribute('TIPOVINC');
        parent::addAttribute('MATRIC');
    }

    public function getFuncionario()
    {
        if (empty($this->funcionario) && !empty($this->data['NUMFUNC'])) {
            $this->funcionario = new ErgFuncionario($this->data['NUMFUNC']);
        }
        return $this->funcionario;
    }

    public function get_dtposse(){
        return  !empty($this->data['DTPOSSE']) ? TDateUtils::DateUsdMyToBr($this->data['DTPOSSE']) : '';
    }

    public function get_dtexerc(){
        return  !empty($this->data['DTEXERC']) ? TDateUtils::DateUsdMyToBr($this->data['DTEXERC']) : '';
    }

    public function get_dtaposent(){
        return  !empty($this->data['DTAPOSENT']) ? TDateUtils::DateUsdMyToBr($this->data['DTAPOSENT']) : '';
    }

    public function get_dtvac(){
        return  !empty($this->data['DTVAC']) ? TDateUtils::DateUsdMyToBr($this->data['DTVAC']) : '';
    }
}
