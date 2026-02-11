<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
use Adianti\Control\TPage;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Control\TAction;
use Adianti\Control\TWindow;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Database\TTransaction;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Registry\TSession;
use Adianti\Validator\TEmailValidator;
use Adianti\Validator\TNumericValidator;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\THidden;

class PessoaForm extends TWindow
{
    private $form;
    private $onSaveCallback;

    /**
     * @param array $param  — você pode receber um callback para notificar o pai
     */
    public function __construct($param = null)
    {
        parent::__construct();
        $this->form = new BootstrapFormBuilder('form_nova_pessoa');
        //$this->form->setFormTitle('Cadastrar Pessoa');

        // campos
        $nome = new TEntry('nome');
        $nome->forceUpperCase();
        $nome->setMaxLength(Pessoa::$fld_fmt['nome']['max_length']);

        $numfunc = new THidden('numfunc');
        $callerForm = new THidden('callerForm');

        $cpf = new TEntry('cpf');
        $cpf->setMask('999.999.999-99', true);
        $cpf->setMaxLength(Pessoa::$fld_fmt['cpf']['max_length']);
        $cpf->addValidation(Pessoa::$fld_fmt['cpf']['caption'], new TRequiredValidator);
        $cpf->addValidation(Pessoa::$fld_fmt['cpf']['caption'], new TEmailValidator);
        $cpf->setValue(TSession::getValue(__CLASS__ . '_novo_cpf'));

        $epart= new TEntry('email_particular');
        $epart->setMaxLength(Pessoa::$fld_fmt['email_particular']['max_length']);
        $epart->addValidation(Pessoa::$fld_fmt['email_particular']['caption'], new TEmailValidator);

        //$einst  = new TEntry('email_institucional');
        //$einst->setMaxLength(Pessoa::$fld_fmt['email_institucional']['max_length']);
        //$einst->addValidation(Pessoa::$fld_fmt['email_institucional']['caption'], new TEmailValidator);
        //$einst->setEditable(false);

        $tel = new TEntry('telefone');
        $tel->setMaxLength(Pessoa::$fld_fmt['telefone']['max_length']);
        $tel->setMask('(99) 99999-9999', false);
        $tel->addValidation(Pessoa::$fld_fmt['telefone']['caption'], new TRequiredValidator);
        $tel->addValidation(Pessoa::$fld_fmt['telefone']['caption'], new TNumericValidator);
        
        $this->form->addFields([ new TLabel(Pessoa::$fld_fmt['nome']['caption'] ) ], [ $nome ]);
        $this->form->addFields([ new TLabel(Pessoa::$fld_fmt['cpf']['caption']) ], [ $cpf  ]);
        $this->form->addFields([ new TLabel(Pessoa::$fld_fmt['email_particular']['caption'])], [ $epart ]);
        $this->form->addFields([ new TLabel(Pessoa::$fld_fmt['telefone']['caption'])], [ $tel ]);
        //$this->form->addFields([ new TLabel(Pessoa::$fld_fmt['email_institucional']['caption'])], [ $einst ]);
        $this->form->addFields([$numfunc]);
        $this->form->addFields([$callerForm]);

        // ação de salvar
        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fas:save green');

        parent::add($this->form);
    }

    public function onSave($param)
    {
        try {
            TTransaction::open('banco_atendimento');
            $p = new Pessoa;
            $p->nome                = $param['nome'];
            $p->cpf                 = preg_replace('/\D/', '', $param['cpf']);
            //$p->email_institucional = $param['email_institucional'];
            $p->email_particular    = $param['email_particular'];
            $p->telefone            = $param['telefone'];
            $p->numfunc             = $param['numfunc'];
            $p->created_by          = TSession::getValue('userid');
            $p->created_at          = date('Y-m-d H:i:s');

            $p->store();
            TTransaction::close();

            // Preenche dados no formulário principal
            $data = new stdClass;
            $data->id_pessoa = $p->id;
            $data->nome = $p->nome;
            $data->email_particular = $p->email_particular;
            $data->telefone = $p->telefone;
            $data->numfunc = $p->numfunc;
            $data->data_chegada = TDateUtils::DateUsToBr(date('Y-m-d'));
            $data->hora = date('H:i:s');            
            
            $data->name = $p->nome;
            $data->email = $p->email_particular;
            $data->phone = $p->telefone;

            TForm::sendData($param['callerForm'], $data) ;
            TToast::show('info', 'Pessoa cadastrada com sucesso!', 'top right', 'far:check-circle' );

            TWindow::closeWindowByName(__CLASS__);
        }
        catch (Exception $e) {
            TTransaction::rollback();
            // mantém dados e mostra erro sem fechar
            $this->form->setData((object)$param);
            new TMessage('error', AdiantiCoreTranslator::translate($e->getMessage()));
        }
    }


    public function onOpenForm($param){
        try{
            if (array_key_exists('cpf', $param)){
                $cpf = $param['cpf'];
                $obj = new StdClass;
                $obj->cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
                $obj->callerForm = $param['callerForm'];

                // busca a pessoa no base do bancorh para preencher os dados
                TTransaction::openFake('banco_oracle');
                $funcionario = ErgFuncionario::getFuncionarioFromCPF($cpf);
                if (!is_null($funcionario)) {
                    var_dump($funcionario);
                    $obj->numfunc = $funcionario->NUMERO;
                    $obj->nome = $funcionario->NOME;
                    $obj->email_particular = $funcionario->E_MAIL;
                    $obj->telefone = $funcionario->TELEFONE;
                }
                TTransaction::close();

                TForm::sendData(__CLASS__, $obj);
                $this->form->setData($obj);
            }
        }catch (Exception $e){
            new TMessage('error', $e->getMessage());
        }  
    }
}
