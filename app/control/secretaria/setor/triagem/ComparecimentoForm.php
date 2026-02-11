<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
use Adianti\Base\AdiantiStandardListTrait;
use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TTime;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Container\TVBox;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TCriteria;
use Adianti\Database\TExpression;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TEmailValidator;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Dialog\TToast;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBCombo as WrapperTDBCombo;
use Adianti\Wrapper\BootstrapDatagridWrapper as WrapperBootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class ComparecimentoForm extends TPage
{
    use AdiantiStandardListTrait;

    protected $form;
    protected $filterForm;
    protected $datagrid;
    protected $pageNavigation;

    public static $hasOne = [
        'pessoa'  => ['Pessoa','id_pessoa','id'],
        'destino' => ['Destino','id_destino','id'],
        'tema'    => ['Tema','id_tema','id'],
    ];

    public function __construct()
    {
        parent::__construct();

        // configurações de listagem
        $this->setDatabase('banco_atendimento');
        $this->setActiveRecord('Comparecimento');
        $this->setDefaultOrder('data_chegada', 'DESC');
        //$this->setLimit(10);
        $this->addFilterField('data_chegada', '=', 'filter_data_chegada');
        $this->addFilterField('hora', 'like', 'filter_hora');
        $this->addFilterField('pessoa', 'like', 'filter_pessoa');

        // Formulário principal de cadastro
        $this->form = new BootstrapFormBuilder ('form_comparecimento');
        $this->form->setFormTitle('Cadastro de Comparecimento');

        // Campo de busca de pessoa por CPF
        $this->cpf = new TEntry('cpf_search');
        $this->cpf->setMask('999.999.999-99', true);
        $this->cpf->placeholder = 'Informe o CPF do visitante';

        $this->numfunc = new THidden('numfunc');
        
        // essa linha é uma forma de fazer o clear do formulario todo usando js
        //$this->cpf->setProperty('onfocus', 'document.getElementById("form_comparecimento").reset();'); 

        // método abortado por enquanto
        $action = new TAction([__CLASS__, 'onFieldFocus']);
        $action->setParameter('form_name', 'form_comparecimento');
        $onFocusJs = $action->serialize();
        $this->cpf->setProperty('onfocus',  "__adianti_load_page('{$onFocusJs}');"); 

        $btnFind = new TButton('btn_find');
        $btnFind->setAction(new TAction([$this, 'onFindPerson']), 'Buscar Pessoa');
        $btnFind->setImage('fas:search');

        $btnUpdatePessoa = new TButton('btn_update_pessoa');
        $btnUpdatePessoa->setAction(new TAction([$this, 'onUpdatePessoa']), 'Atualizar dados Pessoa');
        $btnUpdatePessoa->setImage('fas:save');

        // Botão para abrir modal de nova pessoa
        /*
        $btnNew = new TButton('btn_new');
        $btnNew->setLabel('Nova Pessoa');
        $btnNew->setImage('fas:plus-circle');
        $btnNew->setAction(new TAction([$this, 'onOpenNewPerson']), 'Nova Pessoa');
        */

        $this->form->addFields([new TLabel(Pessoa::$fld_fmt['cpf']['caption'])], [$this->cpf],[$btnFind], [$btnUpdatePessoa]);

        // Campos de pessoa (preenchidos após busca)
        $this->id_pessoa = new THidden('id_pessoa');
        $this->id_pessoa->setEditable(FALSE);
        $this->id_pessoa->addValidation(Comparecimento::$fld_fmt['id_pessoa']['caption'], new TRequiredValidator);

        $this->nome = new TEntry('nome');
        $this->nome->setEditable(FALSE);

        $email = new TEntry('email_particular');
        $email->placeholder = 'Informe o e-mail Particular do visitante';
        $email->addValidation(Pessoa::$fld_fmt['email_particular']['caption'], new TEmailValidator);

        $telefone = new TEntry('telefone');
        $telefone->placeholder = 'Informe o telefone do visitante';
        $telefone->setMask('(99) 99999-9999', false);
        $telefone->addValidation(Pessoa::$fld_fmt['telefone']['caption'], new TRequiredValidator);
        //$telefone->addValidation(Pessoa::$fld_fmt['telefone']['caption'], new TNumericValidator);

        $this->form->addFields([new TLabel([Pessoa::$fld_fmt['nome']['caption']])], [$this->nome]);
        $this->form->addFields([new TLabel(Pessoa::$fld_fmt['email_particular']['caption'])], [$email]);
        $this->form->addFields([new TLabel(Pessoa::$fld_fmt['telefone']['caption'])], [$telefone]);
        $this->form->addFields([], [$this->id_pessoa]);
        $this->form->addFields([], [$this->numfunc]);

        // Campos de comparecimento
        $data = new THidden('data_chegada');
        /*
        $data = new TDate('data_chegada');
        $data->setMask('dd/mm/yyyy');    
        $data->setEditable(FALSE);
        $data->addValidation(Comparecimento::$fld_fmt['data_chegada']['caption'], new TRequiredValidator);
        */

        $hora = new THidden('hora');
        /*
        $hora = new TTime('hora');
        $hora->setEditable(FALSE);
        $hora->addValidation(Comparecimento::$fld_fmt['hora']['caption'], new TRequiredValidator);
        */
        
        $hora_marcada = new TRadioGroup('hora_marcada');
        $hora_marcada->setLayout('horizontal');
        $hora_marcada->addItems( ['07:00:00' => '7h', '08:00:00' => '8h', '09:00:00' => '9h', '10:00:00' => '10h',
                        '11:00:00' => '11h', '12:00:00' => '12h', '13:00:00' => '13h', '14:00:00' => '14h',
                        '15:00:00' => '15h', '16:00:00' => '16h', '17:00:00' => '17h', '18:00:00' => '18h'] );

        $destino = new WrapperTDBCombo('id_destino', 'banco_atendimento', 'Destino', 'id', 'destino');
        $destino->placeholder = 'Selecione o Destino';
        $destino->enableSearch();
        $destino->addValidation(Comparecimento::$fld_fmt['id_destino']['caption'], new TRequiredValidator);
        //$change_destino_action = new TAction(array($this, 'onChangeDestinoAction'));
        //$destino->setChangeAction($change_destino_action);

        $tema = new WrapperTDBCombo('id_tema', 'banco_atendimento', 'Tema', 'id', 'tema');
        $tema->placeholder = 'Selecione o Tema';
        $tema->enableSearch();
        
        $observacao = new TText('observacao', 50, 3);
        $observacao->setMaxLength(Comparecimento::$fld_fmt['observacao']['max_length']);
        $this->form->addFields([$data]);
        $this->form->addFields([$hora]);
        $this->form->addFields([new TLabel(Comparecimento::$fld_fmt['id_destino']['caption'])], [$destino]);
        $this->form->addFields([new TLabel(Comparecimento::$fld_fmt['id_tema']['caption'])], [$tema]);
        $this->form->addFields([new TLabel(Comparecimento::$fld_fmt['hora_marcada']['caption'])], [$hora_marcada]);
        $this->form->addFields([new TLabel(Comparecimento::$fld_fmt['observacao']['caption'])], [$observacao]);

        // Ação de salvar comparecimento
        $this->form->addAction('Registrar Comparecimento', new TAction([$this, 'onSaveAttendance']), 'fas:save green');


        // Formulário de filtro
        $this->filterForm = new BootstrapFormBuilder('form_filtros');
        $this->filterForm->setFormTitle('');
        $fi = new TDate('filter_data');
        $fh = new TTime('filter_hora');
        $fn = new TEntry('filter_pessoa');
        $fn->forceUpperCase();
        $fn->setMaxLength(Pessoa::$fld_fmt['nome']['max_length']);
        $this->filterForm->addFields([new TLabel('Data:')], [$fi]);
        $this->filterForm->addFields([new TLabel('Hora:')], [$fh]);
        $this->filterForm->addFields([new TLabel('Nome:')], [$fn]);
        $this->filterForm->addAction('Filtrar', new TAction([$this, 'onSearch']), 'fas:search blue');
        $this->filterForm->addAction('Limpar', new TAction([$this, 'onClearFilters']), 'fas:eraser red');

        $panel2 = new TPanelGroup('Filtros de Registros de Comparecimento');
        $panel2->collapse();
        $table2 = new TTable;
        $table2->style = 'border-collapse:collapse';
        $table2->width = '100%';
        $table2->addRowSet($this->filterForm);
        //$table2->addRowSet('b1','b2');
        $panel2->add($table2);
        //$panel2->addFooter('Panel group footer');

        // Datagrid
        $this->datagrid = new WrapperBootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->setHeight(300);
        $this->datagrid->addColumn(new TDataGridColumn('data_chegada', 'Data', 'center', '5%'));
        $this->datagrid->addColumn(new TDataGridColumn('hora', 'Hora', 'center', '5%'));
        $this->datagrid->addColumn(new TDataGridColumn('{destino->destino}', 'Destino', 'left', '5%'));
        $this->datagrid->addColumn(new TDataGridColumn('{pessoa->nome}', 'Pessoa', 'left', '45%'));
        $this->datagrid->addColumn(new TDataGridColumn('{tema->tema}', 'Tema', 'left', '20%'));
        $this->datagrid->addColumn(new TDataGridColumn('hora_marcada', 'Hora Marcada', 'left', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('{E_horario_agendamento} {E_siglaexame}', 'bancorh', 'left', '10%'));
        $this->datagrid->createModel();

        // Monta layout
        $vbox = new TVBox;
        $vbox->style = 'width:100%';
        $vbox->add($this->form);
        $vbox->add($panel2);
        $vbox->add($this->datagrid);
        $vbox->add($this->pageNavigation);

        parent::add($vbox);

        // Carrega lista inicial
        $this->onReload();
    }

    // no foco do campo CPF, limpa formulário
    public static function onFieldFocus($param)
    {
        $formName = $param['form_name'];

        $form = TForm::getFormByName($formName);
        if ($form) {
            $form->clear();
        }

        // 2) prepara um objeto com todas as propriedades zeradas
        $data = new stdClass;
        $data->id_pessoa          = '';
        $data->nome               = ''; 
        $data->cpf_search         = '';
        $data->email_particular   = '';
        $data->telefone           = '';
        $data->data_chegada       = '';
        $data->hora               = '';
        $data->id_destino         = '';
        $data->id_tema            = '';
        $data->observacao         = '';
        TForm::sendData($formName, $data);
    }

    public static function onChangeDestinoAction($param)
    {
        //if ($param['id_destino'] == 3) {
        //    new TMessage('info', 'PERÍCIA');
       // }
        
        /*$obj = new StdClass;
        $obj->response_c = 'Resp. for opt "'.$param['combo_change'] . '" ' .date('H:m:s');
        TForm::sendData('form_interaction', $obj);
        
        $options = array();
        $options[1] = $param['combo_change'] . ' - one';
        $options[2] = $param['combo_change'] . ' - two';
        $options[3] = $param['combo_change'] . ' - three';
        TCombo::reload('form_interaction', 'response_b', $options);
        */
    }


    public function onFindPerson($param)
    {
        try {
            if (empty($param['cpf_search'])) {
                throw new Exception('Informe o CPF da pessoa.');
            }

            TTransaction::open('banco_atendimento');
            $cpf = preg_replace('/\D/', '', $param['cpf_search']);
            $criteria = new TCriteria;
            $criteria->add(new TFilter('cpf', '=', $cpf), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('cpf', '=', ltrim($cpf, '0')), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('cpf', '=', str_pad($cpf, 11, '0', STR_PAD_LEFT)), TExpression::OR_OPERATOR);
            $persons = Pessoa::getObjects($criteria);
            TTransaction::close();

            $data = new stdClass;
            if (!empty($persons)) {
                // se existir o cadastro da pessoa em Pessoa mas não tiver preenchido o numfunc, o sistema procura nos registros
                // do bancorh para saber se aquela pessoa já faz parte do bancorh agora.
                // Se encontrar, busca o NUMERO que é o numfunc, sobrescreve o nome em Pessoa com o nome do bancorh
                // e salva.
                $person = $persons[0];
                TTransaction::openFake('banco_oracle');
                $funcionario = ErgFuncionario::getFuncionarioFromCPF($cpf);
                if (!is_null($funcionario)){
                    $data->numfunc = $funcionario->NUMERO;
                    $data->nome = $funcionario->NOME;
                }else{
                    $data->nome = $person->nome;
                }
                TTransaction::close();

                // preenche os dados para popular o formulário
                $data->id_pessoa = $person->id;
                $data->cpf = $person->cpf;
                $data->email_particular = $person->email_particular;
                $data->telefone = $person->telefone;
                $data->data_chegada = date('d/m/Y');
                $data->hora = date('H:i:s');
                $this->form->setData($data);
            } else {
                //TSession::setValue(__CLASS__ . '_novo_cpf', $cpf);
                //new TMessage('warning', 'Pessoa não encontrada. Cadastre uma nova pessoa.');
                //TToast::show('warning', 'Pessoa nao encontrada. Cadastre uma nova pessoa.', 'top-right');
                $this->onOpenNewPerson($cpf);
            }

        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onOpenNewPerson($cpf)
    {
       // $cpf = TSession::getValue(__CLASS__ . '_novo_cpf');

        $params = [
            'cpf'         => $cpf,
            'callerForm'  => 'form_comparecimento',        // que vai gerar "form_ComparecimentoForm"
            'callerField' => 'id_pessoa'       // por exemplo, o nome do campo no ComparecimentoForm
        ];
        
        // abre o PessoaForm passando tudo junto
        AdiantiCoreApplication::loadPage(
            'PessoaForm',
            'onOpenForm', 
            $params
        );
    }

    /** Salva registro de comparecimento */
    public function onSaveAttendance($param)
    {
        try {
            $data = $this->form->getData();
            $this->form->validate();

            if (empty($data->id_pessoa)) {
                throw new Exception('Informe ou cadastre uma pessoa primeiro.');
            }

            if ($data->id_destino == 3 && empty($data->hora_marcada)) {
                throw new Exception('Quando o DESTINO for Perícia, informe a HORA MARCADA.');
            }

            // antes de salvar o comparecimento, procura informacoes sobre o agendamento no bancorh
            // Procura um agendamento para o dia que está sendo registrado o comparecimento
            $registroHoje = null;
            if (!empty($data->numfunc)){
                TTransaction::openFake('banco_oracle');
                //TTransaction::dump('/tmp/log_BANCORH.txt');
                $agendas = ErgReqPericia::getPericiasAgendadasParaOFuncionario($data->numfunc);
                TTransaction::close();
                if ($agendas) {
                    $hoje = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('d/m/Y');
                    $hojeRecords = array_filter($agendas, function($item) use ($hoje) { return $item['data'] === $hoje; });
                    $registroHoje = reset($hojeRecords);
                }
            }

            TTransaction::open('banco_atendimento');
            $cmp = new Comparecimento;
            $cmp->id_pessoa    = $data->id_pessoa;
            $cmp->data_chegada = $data->data_chegada;
            $cmp->hora         = $data->hora;
            $cmp->id_destino   = $data->id_destino;
            $cmp->id_tema      = $data->id_tema;
            $cmp->hora_marcada = $data->hora_marcada;
            $cmp->observacao   = $data->observacao;
            $cmp->created_by   = TSession::getValue('userid');
            $cmp->created_at   = date('Y-m-d H:i:s');
            $cmp->origem_registro = 'TRIAGEM';
            
            if (!empty($registroHoje)){
                $cmp->E_agenda                = $registroHoje['agenda'];
                $cmp->E_horario_agendamento   = $registroHoje['horario'];
                $cmp->E_reqpericia            = $registroHoje['reqpericia'];
                $cmp->E_siglaexame            = $registroHoje['siglaexame'];
            }
            $cmp->store();
            
            $pessoa = new Pessoa($data->id_pessoa);
            $pessoa->nome             = $data->nome;
            $pessoa->numfunc          = $data->numfunc ?? null;
            $pessoa->email_particular = $data->email_particular;
            $pessoa->telefone         = $data->telefone;
            $pessoa->updated_by       = TSession::getValue('userid');
            $pessoa->updated_at       = date('Y-m-d H:i:s');
            $pessoa->store();
                        
            TTransaction::close();
            $this->form->clear();
            new TMessage('info', 'Comparecimento registrado! <br /> '. $data->nome);

            $this->onReload();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            $this->form->setData($data);
        }
    }

 public function onUpdatePessoa($param)
    {
        try {
            $data = $this->form->getData();
            if (empty($data->id_pessoa)) {
                throw new Exception('Informe ou cadastre uma pessoa primeiro.');
            }

            TTransaction::open('banco_atendimento');         
            $pessoa = new Pessoa($data->id_pessoa);
            $pessoa->nome             = $data->nome;
            $pessoa->numfunc          = $data->numfunc ?? null;
            $pessoa->email_particular = $data->email_particular;
            $pessoa->telefone         = $data->telefone;
            $pessoa->updated_by       = TSession::getValue('userid');
            $pessoa->updated_at       = date('Y-m-d H:i:s');
            $pessoa->store();
                        
            TTransaction::close();
            TToast::show('info', 'Dados da pessoa foram atualizados! <br /> '. $data->nome, 'top right', 'far:check-circle' );

            $this->onReload();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    /** Limpa filtros e recarrega lista */
    public function onClearFilters($param)
    {

        $data_data = date('Y-m-d');
        $data_hora = new DateTime();
        $data_hora->sub(new DateInterval('PT2H'));
        $data_hora_diminuida = $data_hora->format('H:i:s');

        $data = new stdClass;
        $data->filter_data = $data_data;
        $data->filter_hora = $data_hora_diminuida;
        

        TSession::setValue(__CLASS__ . '_filter_data',    $data_data);
        TSession::setValue(__CLASS__ . '_filter_hora',    $data_hora_diminuida);
        TSession::setValue(__CLASS__ . '_filter_pessoa', null);
       
        // limpa os campos do formulário
        $this->filterForm->clear();
        $this->filterForm->setData($data);
    }

    function onSearch() // botao filtrar registros de comparecimento
    {
        // obtém valores do formulário
        $data  = $this->filterForm->getData();

        // valida: data deve ser >= hoje
        /*if (!empty($data->filter_data) && $data->filter_data < $today) {
            new TMessage('error', 'A data deve ser igual ou maior que hoje.');
            return;
        }*/

        // grava filtros na sessão
        TSession::setValue(__CLASS__ . '_filter_data',    $data->filter_data);
        TSession::setValue(__CLASS__ . '_filter_hora',    $data->filter_hora);
        TSession::setValue(__CLASS__ . '_filter_pessoa',  $data->filter_pessoa);

        // mantém os valores selecionados no próprio formulário
        $this->filterForm->setData($data);

        // recarrega o DataGrid a partir da primeira página
        $this->onReload(['offset' => 0, 'first_page' => 1]);
    }

    public function onReload($params = null)
    {
        try {
            //$dataComparecimentoForm = $this->form->getData();
            $dataFilterForm  = $this->filterForm->getData();
            $objFieldsFilter = new stdClass;

            TTransaction::open('banco_atendimento');  // ajuste o nome da conexão
            TTransaction::dump('/tmp/log_BANCORH.txt');
            $repository = new TRepository('Comparecimento');
            $criteria   = new TCriteria;

            // mostra somente os registro originados na triagem
            $criteria->add(new TFilter('origem_registro', '=', 'TRIAGEM'));

            // filtro de data (>=)
            if ($filter_data = TSession::getValue(__CLASS__ . '_filter_data')) {
                $criteria->add(new TFilter('data_chegada', '=', $filter_data));
                $objFieldsFilter->filter_data = $filter_data;
            }else{
                $criteria->add(new TFilter('data_chegada', '=', date('Y-m-d')));
                $objFieldsFilter->filter_data = date('Y-m-d');
            }

            // filtro de hora (>=)
            if ($filter_hora = TSession::getValue(__CLASS__ . '_filter_hora')) {
                $criteria->add(new TFilter('hora', '>=', $filter_hora));
                $objFieldsFilter->filter_hora = $filter_hora;
            }else{
                $data_hora = new DateTime();
                $data_hora->sub(new DateInterval('PT2H'));
                $data_hora_diminuida = $data_hora->format('H:i:s');
                $criteria->add(new TFilter('hora', '>=', $data_hora_diminuida));
                $objFieldsFilter->filter_hora = $data_hora_diminuida;
            }

            // filtro por nome da pessoa (LIKE)
            if ($filter_pessoa = TSession::getValue(__CLASS__ . '_filter_pessoa')) {
                if (strlen(trim($filter_pessoa)) >= 7) {
                    // 2) busca na tabela Pessoa todos os IDs cujo nome bata com o LIKE
                    $repoPessoa    = new TRepository('Pessoa');
                    $critPessoa    = new TCriteria;
                    $critPessoa->add( new TFilter('nome', 'LIKE', "{$filter_pessoa}%") );
                    $pessoas       = $repoPessoa->load($critPessoa);

                    // 3) extrai só os IDs num array
                    $ids = array_map(fn($p) => $p->id, $pessoas);

                    // 4) se encontrou ao menos um, adiciona filtro IN em comparecimento.pessoa_id
                    if (!empty($ids)) {
                        // OBS: 'pessoa_id' é o campo FK em comparecimento
                        $criteria->add( new TFilter('id_pessoa', 'IN', $ids) );
                    }
                    // 5) mantém o valor no formulário
                    $objFieldsFilter->filter_pessoa = $filter_pessoa;
                }
            }

            // --- limita aos 20 registros mais recentes ---
            $criteria->setProperty('order', 'data_chegada desc, hora desc');
            $criteria->setProperty('limit', 15);
            $criteria->setProperty('offset', 0);

            // carrega objetos e preenche DataGrid
            $objects = $repository->load($criteria);
            $this->datagrid->clear();

            if ($objects) {
                foreach ($objects as $obj) {
                    $this->datagrid->addItem($obj);
                }
            }
            //$this->filterForm->setData($objFieldsFilter);

            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        } finally {
            $this->filterForm->setData($objFieldsFilter);
        }
    }

}
