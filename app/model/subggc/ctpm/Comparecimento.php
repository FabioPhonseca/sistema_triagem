<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class Comparecimento extends TRecord
{
    const TABLENAME = 'comparecimento';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';

    public static $hasOne = [
        'pessoa'  => ['class'=>'Pessoa',  'key'=>'id_pessoa'],
        'destino' => ['class'=>'Destino', 'key'=>'id_destino'],
        'tema'    => ['class'=>'Tema',    'key'=>'id_tema']
    ];

    protected $pessoa;
    protected $destino;
    protected $tema = null;

    protected static $database = 'banco_atendimento';

    public static $fld_fmt = [
        'id'            => ['caption'=>'# ID',                   'mini_caption'=>'ID'],
        'data_chegada'  => ['caption'=>'Data de Chegada',        'mini_caption'=>'Dt.Chegada'],
        'hora'          => ['caption'=>'Hora de Chegada',        'mini_caption'=>'Hora'],
        'id_pessoa'     => ['caption'=>'Pessoa',                 'mini_caption'=>'Pessoa'],
        'id_vinculo'    => ['caption'=>'Vínculo',                'mini_caption'=>'Vínculo'],
        'id_destino'    => ['caption'=>'Destino',                'mini_caption'=>'Destino'],
        'id_tema'       => ['caption'=>'Tema',                   'mini_caption'=>'Tema'],
        'observacao'    => ['caption'=>'Observação',             'mini_caption'=>'Obs.', 'max_length'=>500],
        'created_by'    => ['caption'=>'Usuário Criador',        'mini_caption'=>'Usuário'],
        'created_at'    => ['caption'=>'Data de Criação',        'mini_caption'=>'Dt.Criação'],
        'E_agenda'    => ['caption'=>'Número da Agenda',       'mini_caption'=>'Num.Agenda'],
        'E_reqpericia'            => ['caption'=>'Núm.Requisição Perícia',        'mini_caption'=>'Num.ReqPericia'],
        'E_horario_agendamento'   => ['caption'=>'Horario do Agendamento',        'mini_caption'=>'Ag.Horario'],
        'E_sigaexame'             => ['caption'=>'Sigla do Exame',        'mini_caption'=>'SiglaExame'],
        'chamado'                   => ['caption'=>'Chamado',        'mini_caption'=>'Chamado'],
        'hora_marcada'              => ['caption'=>'Hora Marcada',        'mini_caption'=>'Hora Marcada'],
        'origem_registro'                    => ['caption'=>'Origem do Registro',        'mini_caption'=>'Origem Registro'],
        'id_comparecimento_pai'     => ['caption'=>'Registro de Comparecimento Original',        'mini_caption'=>'Comparecimento Original'],
        'finalizado'                => ['caption'=>'Data de Finalização',        'mini_caption'=>'Finalizado']
    ];

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('data_chegada');
        parent::addAttribute('hora');
        parent::addAttribute('id_pessoa');
        //parent::addAttribute('id_vinculo');
        parent::addAttribute('id_destino');
        parent::addAttribute('id_tema');
        parent::addAttribute('observacao');
        parent::addAttribute('created_by');
        parent::addAttribute('created_at');
        parent::addAttribute('E_agenda');
        parent::addAttribute('E_reqpericia');
        parent::addAttribute('E_horario_agendamento');
        parent::addAttribute('E_siglaexame');
        parent::addAttribute('chamado');
        parent::addAttribute('hora_marcada');
        parent::addAttribute('origem_registro');
        parent::addAttribute('id_comparecimento_pai');
        parent::addAttribute('finalizado');
    }

    public function get_pessoa()
    {
        if (empty($this->pessoa)) {
            $this->pessoa = new Pessoa($this->id_pessoa);
        }
        return $this->pessoa;
    }

    public function get_vinculo()
    {
        if (empty($this->vinculo) && $this->id_vinculo) {
            $this->vinculo = new Vinculo($this->id_vinculo);
        }
        return $this->vinculo;
    }

    public function get_destino()
    {
        if (empty($this->destino)) {
            $this->destino = new Destino($this->id_destino);
        }
        return $this->destino;
    }

    public function get_tema()
    {
        if (empty($this->tema) && $this->data['id_tema']) {
            $this->tema = new Tema($this->data['id_tema']);
         }else{
            $this->tema = new stdClass();
            $this->tema->tema = 'n/a';
        }
        return $this->tema;
    }

    public function get_data_chegada(){
        return  !empty($this->data['data_chegada']) ? TDateUtils::DateUsToBr($this->data['data_chegada']) : '';
    }

    public function set_data_chegada($value)
    {

        //if (empty($this->loaded) || $this->loaded === false){
        //    $this->data['data_chegada'] = $value;
        //    var_dump('sdfjlsafjdl');
        //}else{

        if (TDateUtils::isValidDateDMY($value)){
            $this->data['data_chegada'] = !empty($value)
                ? TDateUtils::DateBrToUs($value)
                : null;
        }
    }

    public function get_chamado(){
        if (empty($this->data['chamado'])) {
            return '';
        }

        // tenta converter; mas, se falhar, devolve o valor original,
        // para que o teste !empty() em cima dele ainda passe
        $converted = TDateUtils::DateTimeUsToBr($this->data['chamado']);
        return $converted !== '' 
            ? $converted 
            : $this->data['chamado'];
    }

    public function set_chamado($value){
        $this->data['chamado'] = !empty($value)
            ? TDateUtils::DatetimeBrToUs($value)
            : null;
    }

    public function get_finalizado(){
        // se não veio nada do banco, devolve vazio
        if (empty($this->data['finalizado'])) {
            return '';
        }

        // tenta converter; mas, se falhar, devolve o valor original,
        // para que o teste !empty() em cima dele ainda passe
        $converted = TDateUtils::DateTimeUsToBr($this->data['finalizado']);
        return $converted !== '' 
            ? $converted 
            : $this->data['finalizado'];
    }

    public function set_finalizado($value){
            $this->data['finalizado'] = !empty($value)
                ? TDateUtils::DatetimeBrToUs($value)
                : null;

    }




}
?>