<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class ErgAgendamento extends TRecord
{
    const TABLENAME   = 'BANCORH.E_PM_AGENDAMENTO';
    const PRIMARYKEY  = 'REQPERICIA'; // PK composta
    const IDPOLICY    = 'serial';
    protected static $database = 'banco_oracle';

    protected $reqPericia;
    protected $agendaObj;
    protected $pontoPublico;

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        parent::addAttribute('REQPERICIA');
        parent::addAttribute('AGENDA');
        parent::addAttribute('PONTPUBL');
        /*for ($i = 1; $i <= 10; $i++) {
            parent::addAttribute("flex_campo_0{$i}");
        }*/
    }

    public function getReqPericia()
    {
        if (empty($this->reqPericia) && !empty($this->data['REQPERICIA'])) {
            $this->reqPericia = new ReqPericia($this->data['REQPERICIA']);
        }
        return $this->reqPericia;
    }

    public function getAgendaObj()
    {
        if (empty($this->agendaObj) && !empty($this->data['AGENDA'])) {
            $this->agendaObj = new ErgAgenda($this->data['AGENDA']);
        }
        return $this->agendaObj;
    }

    // se existir model PontoPublico, descomente:
    // public function getPontoPublico()
    // {
    //     if (empty($this->pontoPublico) && !empty($this->data['pontpubl'])) {
    //         $this->pontoPublico = new PontoPublico($this->data['pontpubl']);
    //     }
    //     return $this->pontoPublico;
    // }
}
