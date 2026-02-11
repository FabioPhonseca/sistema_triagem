<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class PessoaList extends TPage
{
    protected $datagrid;
    protected $pageNavigation;
    private $loaded;

    public function __construct()
    {
        parent::__construct();

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width:100%';

        $col_id = new TDataGridColumn('id', 'ID', 'center', '10%');
        $col_nome = new TDataGridColumn('nome', 'Nome', 'left', '40%');
        $col_cpf  = new TDataGridColumn('cpf', 'CPF', 'center', '20%');
        $col_email = new TDataGridColumn('email_institucional', 'Email Inst.', 'left', '30%');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_nome);
        $this->datagrid->addColumn($col_cpf);
        $this->datagrid->addColumn($col_email);

        $this->datagrid->addAction(new TDataGridAction(['PessoaForm', 'onEdit'], ['id'=>'{id}']), 'Editar', 'fa:edit blue');
        $this->datagrid->addAction(new TDataGridAction([\$this, 'onDelete'], ['id'=>'{id}']), 'Excluir', 'fa:trash red');

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([\$this, 'onReload']));

        $panel = new TPanelGroup('Lista de Pessoas');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);

        $container = new TVBox;
        $container->add($panel);
        parent::add($container);
    }

    public function onReload($param = null)
    {
        try {
            TTransaction::open('banco_atendimento');
            \$repo = new TRepository('Pessoa');
            \$crit = new TCriteria;
            \$crit->setProperty('order', 'id DESC');
            \$crit->limit = 10;
            \$crit->offset = \$param['offset'] ?? 0;

            $objects = \$repo->load(\$crit);

            \$this->datagrid->clear();
            if (\$objects) {
                foreach (\$objects as \$obj) {
                    \$this->datagrid->addItem(\$obj);
                }
            }

            \$count = \$repo->count(\$crit);
            \$this->pageNavigation->setCount(\$count);
            \$this->pageNavigation->setProperties(\$param, ['limit','offset']);
            TTransaction::close();
            \$this->loaded = true;
        } catch (Exception \$e) {
            new TMessage('error', \$e->getMessage());
        }
    }

    public function onDelete($param)
    {
        $action = new TAction([\$this, 'DeleteConfirm'], $param);
        new TQuestion('Tem certeza que deseja excluir esta pessoa?', $action);
    }

    public function DeleteConfirm($param)
    {
        try {
            TTransaction::open('banco_atendimento');
            \$obj = new Pessoa($param['id']);
            \$obj->delete();
            TTransaction::close();
            \$this->onReload(['offset'=>0,'limit'=>10]);
            new TMessage('info', 'Registro excluído');
        } catch (Exception \$e) {
            new TMessage('error', \$e->getMessage());
        }
    }
}
?>