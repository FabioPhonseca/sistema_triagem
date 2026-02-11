<?php
/**
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 *
 * Permite buscar Funcionários pelo CPF ou por parte do nome (>=5 caracteres),
 * e mostra os dados do Funcionario, seus vínculos e agendamentos.
 */
class FuncionarioConsultaView extends TPage
{
    protected $form;      // formulário de busca
    protected $datagrid;  // grade de resultados

    public function __construct()
    {
        parent::__construct();

        // --- 1) monta o formulário de busca ---
        $this->form = new BootstrapFormBuilder('form_funcionario');
        $this->form->setFormTitle('Consulta de Funcionário');

        // campos: CPF e Nome
        $cpf  = new TEntry('cpf');
        $nome = new TEntry('nome');
        $nome->setProperty('placeholder', 'mínimo 5 caracteres');

        $this->form->addFields( [ new TLabel('CPF') ],  [ $cpf ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );

        // ação de pesquisa
        $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search');

        // --- 2) monta a datagrid para exibir resultados ---
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        // colunas da Funcionario
        $this->datagrid->addColumn( new TDataGridColumn('NUMERO',  'Número',   'center',  50) );
        $this->datagrid->addColumn( new TDataGridColumn('CPF',     'CPF',      'center', 100) );
        $this->datagrid->addColumn( new TDataGridColumn('NOME',    'Nome',     'left',   200) );
        $this->datagrid->addColumn( new TDataGridColumn('SEXO',    'Sexo',     'center',  50) );
        $this->datagrid->addColumn( new TDataGridColumn('DTNASC',  'Data Nasc.','center', 100) );
        // coluna de vínculos
        $colVinc = new TDataGridColumn('vinculos', 'Vínculos', 'left', 200);
        $this->datagrid->addColumn($colVinc);
        // coluna de agendamentos
        $colAg = new TDataGridColumn('agendamentos', 'Agendamentos', 'left', 200);
        $this->datagrid->addColumn($colAg);

        $this->datagrid->createModel();

        // --- 3) monta a página ---
        $vbox = new TVBox;
        $vbox->style = 'width:100%';
        $vbox->add( new TXMLBreadCrumb('menu.xml', __CLASS__) );
        $vbox->add( $this->form );
        $vbox->add( $this->datagrid );
        parent::add($vbox);
    }

    /**
     * Executa a pesquisa e preenche a datagrid
     */
    public function onSearch($param)
    {
        try
        {
            // mantém dados no formulário
            $data = (object) $this->form->getData();
            $this->form->setData((array) $data);

            // valida: CPF ou nome >=5 chars
            if ( empty($data->cpf) AND ( empty($data->nome) OR strlen($data->nome) < 5 ) )
            {
                throw new Exception('Informe o CPF ou ao menos 5 caracteres do nome');
            }

            // abre transação (usa o mesmo database da Model Funcionario)
            TTransaction::openFake('banco_oracle');
            //TTransaction::dump( '/tmp/log.txt');
            // prepara repositório e critérios
            $repo     = new TRepository('ErgFuncionario');
            $criteria = new TCriteria;

            if (!empty($data->cpf))
            {
               $criteria->add( new TFilter('CPF', '=', $data->cpf) );
            }
            else
            {
                $criteria->add( new TFilter('NOME', 'like', "%{$data->nome}%") );
            }

            // carrega funcionarios
            $funcionarios = $repo->load($criteria);

            // limpa grade
            $this->datagrid->clear();

            if ($funcionarios)
            {
                foreach ($funcionarios as $func)
                {
                    // --- carrega vínculos ---
                    $critV = new TCriteria;
                    $critV->add( new TFilter('NUMFUNC', '=', $func->NUMERO) );
                    $vinculos = (new TRepository('ErgVinculo'))->load($critV);

                    $listaV = [];
                    if ($vinculos)
                    {
                        foreach ($vinculos as $v)
                        {
                            $listaV[] = "{$v->TIPOVINC} ({$v->NUMERO}) / {$v->MATRIC}";
                        }
                    }
                    $func->vinculos = implode(', ', $listaV);

                    // --- carrega agendamentos ---
                    $listaA = [];

                    // 1) busca todas as pericias onde este funcionário
                    $critReq = new TCriteria;
                    $critReq->add( new TFilter('NUMFUNC', '=', $func->NUMERO) );
                    $reqs = (new TRepository('ErgReqPericia'))->load($critReq);

                    if ($reqs)
                    {
                        foreach ($reqs as $req)
                        {
                            // 2) para cada ReqPericia, carrega os agendamentos (AGMTO)
                            $critAgm = new TCriteria;
                            $critAgm->add( new TFilter('REQPERICIA', '=', $req->REQPERICIA) );

                            $agms = (new TRepository('ErgAgendamento'))->load($critAgm);

                            if ($agms)
                            {
                                foreach ($agms as $agm)
                                {
                                    // 3) para cada Agendamento, busca a Agenda pai
                                    $ag = $agm->getAgendaObj();
                                    if ($ag){
                                        $listaA[] = $ag->DATA. ' '. TDateUtils::TimeCompactToH_i($ag->HORAINI) . ' - ' . TDateUtils::TimeCompactToH_i($ag->HORAFIM);
                                    }
                                }
                            }
                        }
                    }

                    // preenche a coluna "agendamentos"
                    $func->agendamentos = implode('<br>', $listaA);


                    // adiciona na grade
                    $this->datagrid->addItem($func);
                }
            }

            TTransaction::close();
        }
        catch (Exception $e)
        {
            // mostra erro e reverte transação
            new TMessage('error', $e->getMessage());
        }
    }
}
