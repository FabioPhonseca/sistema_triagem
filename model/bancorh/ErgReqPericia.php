<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;

class ErgReqPericia extends TRecord
{
    const TABLENAME   = 'BANCORH.E_PM_REQPERICIA';
    const PRIMARYKEY  = 'REQPERICIA';
    const IDPOLICY    = 'serial';
    protected static $database = 'banco_oracle';

    // objetos relacionados
    protected $especialidade;
    protected $medico;
    protected $inscrito;

    public static $fld_fmt = [
    'SIGLAEXAME'      => ['caption' => 'Sigla Exame',           'mini_caption' => 'Exame'],
    'NUMFUNC'         => ['caption' => 'Número Funcionário',    'mini_caption' => 'Num.Func'],
    'DATAINI'         => ['caption' => 'Data Início',           'mini_caption' => 'Dt.Início'],
    'DATAFIM'         => ['caption' => 'Data Fim',              'mini_caption' => 'Dt.Fim'],
    'DATAAG'         => ['caption' => 'Data Agendamento',      'mini_caption' => 'Dt.Agend.'],
    'DATAREQ'        => ['caption' => 'Data Requisição',       'mini_caption' => 'Dt.Req.'],
    'GRAUPARENTESCO'  => ['caption' => 'Grau Parentesco',       'mini_caption' => 'Grau Par.'],
    'NOMEPARENTE'     => ['caption' => 'Nome Parente',          'mini_caption' => 'Parente'],
    'CAT'             => ['caption' => 'Categoria',             'mini_caption' => 'Cat.'],
    'CHAVEPRONT'      => ['caption' => 'Chave Prontuário',      'mini_caption' => 'Chave Pront.'],
    'CHAVEPRONTANT'   => ['caption' => 'Chave Prontuário Ant.', 'mini_caption' => 'Chave Ant.'],
    'NUMPROC'         => ['caption' => 'Número Processo',       'mini_caption' => 'Proc.'],
    'NUMVINC_PENS'    => ['caption' => 'Número Vínculo Pensão','mini_caption' => 'Vinc.Pens.'],
    'NUMPENS'         => ['caption' => 'Número Pensão',         'mini_caption' => 'Pens.'],
    'ESPEC'           => ['caption' => 'Especialidade',         'mini_caption' => 'Espec.'],
    'SENHA'           => ['caption' => 'Senha',                 'mini_caption' => 'Senha'],
    'DATAHORARECEP'   => ['caption' => 'Data/Hora Recepção',    'mini_caption' => 'Dt/Hr.Recep.'],
    ];

    public function __construct($id = NULL)
    {
        parent::__construct($id);

        // campos da tabela
        parent::addAttribute('SIGLAEXAME');
        parent::addAttribute('NUMFUNC');
        parent::addAttribute('DATAINI');
        parent::addAttribute('DATAFIM');
        parent::addAttribute('DATAAG');
        parent::addAttribute('DATAREQ');
        parent::addAttribute('GRAUPARENTESCO');
        parent::addAttribute('NOMEPARENTE');
        parent::addAttribute('CAT');
        parent::addAttribute('CHAVEPRONT');
        parent::addAttribute('CHAVEPRONTANT');
        parent::addAttribute('NUMPROC');
        parent::addAttribute('NUMVINC_PENS');
        parent::addAttribute('NUMPENS');
        parent::addAttribute('ESPEC');
        parent::addAttribute('SENHA');
        parent::addAttribute('DATAHORARECEP');
    }

    /**
     * Retorna a especialidade (ESPEC ➞ E_PM_ESPECIALIDADE)
     */
    public function getEspecialidade()
    {
        if (empty($this->especialidade) && !empty($this->data['espec'])) {
            $this->especialidade = new ErgEspecialidade($this->data['espec']);
        }
        return $this->especialidade;
    }

    /**
     * Retorna o médico (NUMFUNC ➞ MEDICOS)
     */
    public function getMedico()
    {
        if (empty($this->medico) && !empty($this->data['numfunc'])) {
            $this->medico = new ErgMedico($this->data['numfunc']);
        }
        return $this->medico;
    }

    /**
     * Retorna o inscrito (INSCRITO ➞ FUNCIONARIOS)
     */
    public function getInscrito()
    {
        if (empty($this->inscrito) && !empty($this->data['inscrito'])) {
            $this->inscrito = new ErgFuncionario($this->data['inscrito']);
        }
        return $this->inscrito;
    }

    public static function getPericiasAgendadasParaOFuncionario($numfunc) : ?array {
        $resultado = [];
        
        $critReq = new TCriteria;
        $critReq->add( new TFilter('NUMFUNC', '=', $numfunc) );
        $reqs = (new TRepository('ErgReqPericia'))->load($critReq);

        if ($reqs){
            foreach ($reqs as $req){
                $critAgm = new TCriteria;
                $critAgm->add( new TFilter('REQPERICIA', '=', $req->REQPERICIA) );

                $agms = (new TRepository('ErgAgendamento'))->load($critAgm);

                if ($agms){
                    foreach ($agms as $agm){
                        // 3) para cada Agendamento, busca a Agenda pai
                        $ag = $agm->getAgendaObj();
                        if ($ag){
                            $dados['agenda'] = $ag->AGENDA;
                            $dados['data'] = $ag->DATA;
                            $dados['horario'] = TDateUtils::TimeCompactToH_i($ag->HORAINI);
                            $dados['reqpericia'] = $req->REQPERICIA;
                            $dados['siglaexame'] = $req->SIGLAEXAME;
                            $resultado[] = $dados;
                        }
                    }
                }
            }
        }
        return $resultado;

    }

}
