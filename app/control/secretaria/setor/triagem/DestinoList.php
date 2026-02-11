<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class DestinoList extends TPage
{
    use Adianti\Base\AdiantiStandardListTrait;

    public function __construct()
    {
        parent::__construct();

        // configurações de listagem
        $this->setDatabase('banco_atendimento');
        $this->setActiveRecord('Destino');
        $this->addFilterField('id', '=',    'id');
        $this->addFilterField('destino', 'like', 'destino');
        $this->setDefaultOrder('id', 'desc');

        // form de busca
        $this->form = new BootstrapFormBuilder('form_search_Destino');
        $this->form->setFormTitle('Lista de Destinos');

        $id      = new TEntry('id');
        $destino = new TEntry('destino');

        $this->form->addFields([new TLabel('ID:')],      [$id]);
        $this->form->addFields([new TLabel('Destino:')], [$destino]);

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        $this->form->addAction('Find',  new TAction([$this, 'onSearch']),      'fa:search blue');
        $this->form->addActionLink('New', new TAction(['DestinoForm','onClear']), 'fa:plus-circle green');
        $this->form->addActionLink('Clear', new TAction([$this, 'clear']),     'fa:eraser red');

        // DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        $col_id      = new TDataGridColumn('id',      'ID',      'center', '10%');
        $col_destino = new TDataGridColumn('destino', 'Destino', 'left',   '90%');

        $col_id->setAction(new TAction([$this, 'onReload']), ['order'=>'id']);
        $col_destino->setAction(new TAction([$this, 'onReload']), ['order'=>'destino']);

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_destino);

        $action1 = new TDataGridAction(['DestinoForm','onEdit'], ['key'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'],       ['key'=>'{id}']);

        $this->datagrid->addAction($action1, 'Edit',   'fa:edit blue');
        $this->datagrid->addAction($action2, 'Delete', 'fa:trash red');

        $this->datagrid->createModel();

        // paginação
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        // layout
        $vbox = new TVBox;
        $vbox->style = 'width:100%';
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));

        parent::add($vbox);
    }

    public function clear()
    {
        $this->clearFilters();
        $this->onReload();
    }
}
?>
