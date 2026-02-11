<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class TemaList extends TPage
{
    use Adianti\Base\AdiantiStandardListTrait;

    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('banco_atendimento');
        $this->setActiveRecord('Tema');
        $this->addFilterField('id',   '=',    'id');
        $this->addFilterField('tema', 'like', 'tema');
        $this->setDefaultOrder('id', 'desc');

        $this->form = new BootstrapFormBuilder('form_search_Tema');
        $this->form->setFormTitle('Lista de Temas');

        $id   = new TEntry('id');
        $tema = new TEntry('tema');

        $this->form->addFields([new TLabel('ID:')],   [$id]);
        $this->form->addFields([new TLabel('Tema:')], [$tema]);

        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        $this->form->addAction('Find',  new TAction([$this, 'onSearch']),   'fa:search blue');
        $this->form->addActionLink('New', new TAction(['TemaForm','onClear']), 'fa:plus-circle green');
        $this->form->addActionLink('Clear', new TAction([$this, 'clear']),    'fa:eraser red');

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        $col_id   = new TDataGridColumn('id',   'ID',   'center', '10%');
        $col_tema = new TDataGridColumn('tema', 'Tema', 'left',   '90%');

        $col_id->setAction(new TAction([$this, 'onReload']), ['order'=>'id']);
        $col_tema->setAction(new TAction([$this, 'onReload']), ['order'=>'tema']);

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_tema);

        $action1 = new TDataGridAction(['TemaForm','onEdit'], ['key'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onDelete'],     ['key'=>'{id}']);

        $this->datagrid->addAction($action1, 'Edit',   'fa:edit blue');
        $this->datagrid->addAction($action2, 'Delete', 'fa:trash red');

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

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
