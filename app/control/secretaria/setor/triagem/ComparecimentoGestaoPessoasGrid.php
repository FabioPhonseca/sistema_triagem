<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
use Adianti\base\AdiantiStandardCollectionTrait;
use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TFilter;
use Adianti\Database\TCriteria;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Database\TRepository;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Wrapper\TDBCombo;

class ComparecimentoGestaoPessoasGrid extends TPage
{
    private $form;
    private $datagrid;
    private $pageNavigation;

    use AdiantiStandardCollectionTrait;

    public function __construct()
    {
        parent::__construct();

        // Formulário de busca
        $this->form = new BootstrapFormBuilder('form_search_comparecimento_gestao_pessoas');
        $this->form->setFormTitle('');

        $chamado_sn = new TRadioGroup('chamado_sn');
        $chamado_sn->setLayout('horizontal');
        $chamado_sn_items = ['S'=>'SIM', 'N'=>'NÃO'];
        $chamado_sn->addItems($chamado_sn_items);
        $chamado_sn->setValue(TSession::getValue(__CLASS__.'chamado_sn') ?? 'S');
        $this->form->addFields([new TLabel('Mostrar chamados:')], [$chamado_sn]);

        $hora_marcada = new TRadioGroup('hora_marcada');
        $hora_marcada->setLayout('horizontal');
        $hora_marcada->addItems( ['07:00:00' => '7h', '08:00:00' => '8h', '09:00:00' => '9h', '10:00:00' => '10h',
                        '11:00:00' => '11h', '12:00:00' => '12h', '13:00:00' => '13h', '14:00:00' => '14h',
                        '15:00:00' => '15h', '16:00:00' => '16h', '17:00:00' => '17h', '18:00:00' => '18h'] );
        $this->form->addFields([new TLabel('Hora marcada:')], [$hora_marcada]);
        $hora_marcada->setValue(TSession::getValue(__CLASS__.'hora_marcada'));                        

        $destino = new TDBCombo('id_destino', 'banco_atendimento', 'Destino', 'id', 'destino');
        $this->form->addFields([new TLabel('Destino:')], [$destino]);
        $destino->setValue(TSession::getValue(__CLASS__.'id_destino'));

        $hora = new TCombo('hora');
        $hora->addItems( ['07:00:00' => '7h', '08:00:00' => '8h', '09:00:00' => '9h', '10:00:00' => '10h',
                        '11:00:00' => '11h', '12:00:00' => '12h', '13:00:00' => '13h', '14:00:00' => '14h',
                        '15:00:00' => '15h', '16:00:00' => '16h', '17:00:00' => '17h', '18:00:00' => '18h'] );
        $this->form->addFields([new TLabel('Chegada até:')], [$hora]);
        $hora->setValue(TSession::getValue(__CLASS__.'hora'));

        // Campo de filtro por nome
        $nome = new TEntry('nome');
        $this->form->addFields([new TLabel('Nome:')], [$nome]);
        $nome->setValue(TSession::getValue(__CLASS__.'nome_pessoa'));


        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $this->form->addAction('Limpar o formulário', new TAction([$this, 'clear']),     'fa:eraser red');

        // Grid de resultados
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        // Colunas do grid
        $coluna = new TDataGridColumn('data_chegada', 'Data', 'center', '10%');
        $coluna ->setTransformer(array($this, 'formatLinhaChamada'));

        $this->datagrid->addColumn($coluna);
        $this->datagrid->addColumn(new TDataGridColumn('hora', 'Hora', 'center', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('{pessoa->nome}', 'Pessoa', 'left', '30%'));
        $this->datagrid->addColumn(new TDataGridColumn('hora_marcada', 'Hora Marcada', 'center', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('{destino->destino}', 'Destino', 'left', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('{tema->tema}', 'Tema', 'left', '20%'));
        $this->datagrid->addColumn(new TDataGridColumn('{E_horario_agendamento} {E_siglaexame}', 'bancorh', 'left', '30%'));
        //$this->datagrid->addColumn(new TDataGridColumn('chamado', 'Chamado', 'left', '5%'));

        // Ações do grid
        $action1 = new TDataGridAction([$this, 'onCheck'], ['key' => '{id}']);
        $action1->setDisplayCondition( array($this, 'displayColumn') );
        $this->datagrid->addAction($action1, 'Chamar', 'fa:bullhorn black');
        $this->datagrid->createModel();

        // Paginação
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        $this->pageNavigation->setLimit(10);

        $panel2 = new TPanelGroup('Acompanhamento da triagem');
        $panel2->collapse();
        $table2 = new TTable;
        $table2->style = 'border-collapse:collapse';
        $table2->width = '100%';
        $table2->addRowSet($this->form);
        $panel2->add($table2);

        // Monta layout
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel2);
        $vbox->add($this->datagrid);
        $vbox->add($this->pageNavigation);
        parent::add($vbox);

        // Carrega inicialmente
        $this->onReload([]);
    }

    public function formatLinhaChamada($value, $object, $row)
    {   
        if ( $object->chamado ){
            $row->className = 'table-success';       
        }
        return $value;
    }

    public function displayColumn( $object )
    {
        if ( $object->chamado ){
            return FALSE;
        }
        return TRUE;
    }

    public function onSearch()
    {
        $data = $this->form->getData();
        TSession::setValue(__CLASS__ .'nome_pessoa', $data->nome);
        TSession::setValue(__CLASS__ .'hora', $data->hora);
        TSession::setValue(__CLASS__.'chamado_sn', $data->chamado_sn);
        TSession::setValue(__CLASS__.'id_destino', $data->id_destino);
        TSession::setValue(__CLASS__.'hora_marcada', $data->hora_marcada);
        $this->form->setData($data);
        $this->onReload([]);
    }

    public function onReload($param = null)
    {
        try {
            TTransaction::open('banco_atendimento');
            TTransaction::dump('/tmp/log.txt');
            $repository = new TRepository('Comparecimento');
            $criteria   = new TCriteria;

            // mostra somente os registro originados na triagem
            $criteria->add(new TFilter('origem_registro', '=', 'TRIAGEM'));

            // filtro de data (>=)
            //$dia = date('Y-m-d', strtotime('-1 day'));
            $dia = date('Y-m-d');
            $criteria->add(new TFilter('data_chegada', '=', $dia));
            //$criteria->add(new TFilter('chamado', 'is', null));

            // Filtro de hora
            if ($hora = (TSession::getValue(__CLASS__.'hora'))) {
                $criteria->add(new TFilter('hora', '<=', $hora));
            }

            if ($hora_marcada = (TSession::getValue(__CLASS__.'hora_marcada'))) {
                $criteria->add(new TFilter('hora_marcada', '=', $hora_marcada));
            }

            // Filtro de destino
            if ($destino = (TSession::getValue(__CLASS__.'id_destino'))) {
                $criteria->add(new TFilter('id_destino', '=', $destino));
            }

            // Filtro de chamado
            if ($chamado_sn = (TSession::getValue(__CLASS__.'chamado_sn'))) {
                if ($chamado_sn == 'N') {
                    $criteria->add(new TFilter('chamado', 'is', null));
                }
            }

            // Filtra pelo nome da pessoa, usando relacionamento definido em Comparecimento model
            if ($nome = TSession::getValue(__CLASS__ .'nome_pessoa')) {
                $criteria->add(new TFilter('id_pessoa', 'IN', '(SELECT id FROM pessoa WHERE nome LIKE "%' . $nome . '%")'));
            }

            // Ordena pelos mais recentes
            $criteria->setProperty('order', 'data_chegada ASC, hora ASC');

            // Paginação: limit e offset
            $limit  = 15;
            $offset = isset($param['offset']) ? $param['offset'] : 0;
            $criteria->setProperty('limit', $limit);
            $criteria->setProperty('offset', $offset);

            // Conta total de registros (sem limit/offset)
            $countCriteria = clone $criteria;
            $countCriteria->setProperty('limit', null);
            $countCriteria->setProperty('offset', null);
            $total = $repository->count($countCriteria);

            // Carrega objetos e preenche grid
            $objects = $repository->load($criteria);
            $this->datagrid->clear();
            if ($objects) {
                foreach ($objects as $object) {
                    $this->datagrid->addItem($object);
                }
            }

            // Atualiza navegação
            $this->pageNavigation->setCount($total);
            $this->pageNavigation->setProperties($param);

            TTransaction::close();
            $this->loaded = true;
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public static function onCheck($param)
    {
        try {
            TTransaction::open('banco_atendimento');
            $comparecimento = Comparecimento::find($param['key']);
            $comparecimento->chamado = date('d/m/Y H:i:s');
            $comparecimento->store();
            TTransaction::close();
            TScript::create("window.location.reload();");
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function clear()
    {
        $this->clearFilters();
        $this->onReload();
    }

}
