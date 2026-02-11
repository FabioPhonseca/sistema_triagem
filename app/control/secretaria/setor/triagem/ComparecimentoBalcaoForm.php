<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Wrapper\TDBCombo as WrapperTDBCombo;
use Adianti\Wrapper\BootstrapFormBuilder;

class ComparecimentoBalcaoForm extends TPage
{
    private $datagrid;
    
    public function __construct()
    {
        parent::__construct();
        
        // creates one datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(450);
        $this->datagrid->makeScrollable();
        
        // add the columns
        $this->datagrid->addColumn(new TDataGridColumn('hora', Comparecimento::$fld_fmt['hora']['caption'], 'center', '5%'));
        $this->datagrid->addColumn(new TDataGridColumn('hora_marcada', Comparecimento::$fld_fmt['hora_marcada']['caption'], 'left', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('{pessoa->nome}', Comparecimento::$fld_fmt['id_pessoa']['caption'], 'left', '35%'));
        $this->datagrid->addColumn(new TDataGridColumn('{tema->tema}', Comparecimento::$fld_fmt['id_tema']['caption'], 'left', '50%'));
        //$this->datagrid->addColumn(new TDataGridColumn('data_chegada', Comparecimento::$fld_fmt['data_chegada']['caption'], 'center', '5%'));
        
        // add the actions
        $action2 = new TDataGridAction([$this, 'onFinalizaAtendimento'],   ['id' => '{id}', 'nome' => '{pessoa->nome}', 'id_pessoa' => '{id_pessoa}']);
        $this->datagrid->addAction($action2, 'Finalizar atendimento', 'fas:clipboard-check green');
        $action1 = new TDataGridAction([$this, 'onInputEncaminharDialog'],   ['id' => '{id}', 'nome' => '{pessoa->nome}', 'id_pessoa' => '{id_pessoa}']);
        $this->datagrid->addAction($action1, 'Encaminhar', 'fas:external-link-alt orange');
        
        // creates the datagrid model
        $this->datagrid->createModel();

        $input_search = new TEntry('input_search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        $this->datagrid->enableSearch($input_search, 'hora, hora_marcada, pessoa->nome, tema->tema');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add(TPanelGroup::pack('Fila de atendimento do Balcão',$input_search, $this->datagrid, ''));

        parent::add($vbox);
    }
    
    /**
     * Load the data into the datagrid
     */
    function onReload(){
        try {
            $this->datagrid->clear();

            TTransaction::open('banco_atendimento');  // ajuste o nome da conexão

            $repository = new TRepository('Comparecimento');
            $criteria   = new TCriteria;

            // mostra somente os registro destinados ao balcao na triagem
            $criteria->add(new TFilter('id_destino', '=', 1));
            $criteria->add(new TFilter('finalizado', 'is', NULL));
            // sempre filtra data de hoje
            $criteria->add(new TFilter('data_chegada', '=', date('Y-m-d')));

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

            TTransaction::close();
            //$this->loaded = true;
        }
        catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public static function onFinalizaAtendimento($param)
    {
        // Cria a ação que vai realmente finalizar
        $action = new TAction([__CLASS__, 'finalizarAtendimento']);
        $action->setParameters($param); // repassa os parâmetros da linha

        new TQuestion("Deseja realmente finalizar o atendimento de {$param['nome']} ?", $action);
    }

    public static function FinalizarAtendimento( $param )
    { 
     try {
            TTransaction::open('banco_atendimento');
                
            $cmpOriginal = new Comparecimento($param['id']);
            
            if (empty($cmpOriginal->chamado)){
                $cmpOriginal->chamado = date('d/m/Y H:i:s');
            }

            $cmpOriginal->finalizado = date('d/m/Y H:i:s');
            $cmpOriginal->store();
            $action = new TAction(['ComparecimentoBalcaoForm', 'onReload']);
            new TMessage('info', 'Atendimento finalizado para '.$param['nome'], $action);
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }


    /**
     * Open an input dialog
     */
    public static function onInputEncaminharDialog( $param )
    {
        $nome = new TEntry('nome');
        $nome->setValue($param['nome']);
        $nome->setEditable(false);

        $id_pessoa = new THidden('id_pessoa');
        $id_pessoa->setValue($param['id_pessoa']);

        $id_comparecimento_pai = new THidden('id_comparecimento_pai');
        $id_comparecimento_pai->setValue($param['id']);

        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', 'IN', [2, 3 ,4]));
        $id_destino = new WrapperTDBCombo('id_destino', 'banco_atendimento', 'Destino', 'id', 'destino', null, $criteria);

        $id_tema = new WrapperTDBCombo('id_tema', 'banco_atendimento', 'Tema', 'id', 'tema', null);
        $id_tema->enableSearch();

        $observacao = new TEntry('observacao');


        $form = new BootstrapFormBuilder('input_form');
        $form->addFields( [Comparecimento::$fld_fmt['id_pessoa']['caption']],     [$nome] );
        $form->addFields( [Comparecimento::$fld_fmt['id_destino']['caption']],     [$id_destino] );
        $form->addFields( [Comparecimento::$fld_fmt['id_tema']['caption']],     [$id_tema] );
        $form->addFields( [Comparecimento::$fld_fmt['observacao']['caption']],     [$observacao] );
        $form->addFields( [$id_comparecimento_pai, $id_pessoa] );
        
        // form action
        $form->addAction('Encaminhar', new TAction(array(__CLASS__, 'onSaveEncaminhar')), 'fa:save green');
        
        // show input dialot
        new TInputDialog('Encaminhamento', $form);
    }
    
    /**
     * Show the input dialog data
     */
    public static function onSaveEncaminhar( $param ){
      
       try {
            TTransaction::open('banco_atendimento');

            if (!isset($param['id_pessoa']) || empty($param['id_pessoa'])){
                throw new Exception('Pessoa não informada');
            }
            
            if (!isset($param['id_destino']) || empty($param['id_destino'])){
                throw new Exception('Destino não informado');
            }
            
            if (!isset($param['id_comparecimento_pai']) || empty($param['id_comparecimento_pai'])){
                throw new Exception('Registro inicial não informado');
            }
                
            $cmpOriginal = new Comparecimento($param['id_comparecimento_pai']);

            $cmp = new Comparecimento;
            $cmp->id_pessoa = $param['id_pessoa'];
            $cmp->data_chegada = $cmpOriginal->data_chegada;
            $cmp->hora         = $cmpOriginal->hora;        
            $cmp->id_destino = $param['id_destino'];
            $cmp->id_tema = $param['id_tema'];
            $cmp->hora_marcada = $cmpOriginal->hora_marcada;
            $cmp->observacao = $param['observacao'];
            $cmp->id_comparecimento_pai = $param['id_comparecimento_pai'];
            $cmp->created_by   = TSession::getValue('userid');
            $cmp->created_at   = date('Y-m-d H:i:s');
            $cmp->origem_registro = 'BALCAO';
            $cmp->store();     

            if (empty($cmpOriginal->chamado)){
                $cmpOriginal->chamado = date('d/m/Y H:i:s');
            }
            $cmpOriginal->finalizado = date('d/m/Y H:i:s');
            $cmpOriginal->store();
            
            if ($cmp->id > 0){    
                $action = new TAction(['ComparecimentoBalcaoForm', 'onReload']);
                new TMessage('info', $param['nome'] . ' foi encaminhado para ' . $cmp->destino->destino, $action);
            }
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * shows the page
     */
    function show()
    {
        $this->onReload();
        parent::show();
    }
}