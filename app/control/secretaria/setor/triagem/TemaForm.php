<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class TemaForm extends TPage
{
    use Adianti\Base\AdiantiStandardFormTrait;

    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('banco_atendimento');
        $this->setActiveRecord('Tema');

        $this->form = new BootstrapFormBuilder('form_Tema');
        $this->form->setFormTitle('Cadastro de Tema');

        $id   = new TEntry('id');
        $tema = new TEntry('tema');

        $id->setEditable(false);

        $this->form->addFields(['ID'],   [$id]);
        $this->form->addFields(['Tema'], [$tema]);

        $this->form->addAction('Save', new TAction([$this, 'onSave']), 'fa:save');
        $this->form->addAction('Back', new TAction(['TemaList','onReload']), 'fa:arrow-left');

        $this->form->setData( TSession::getValue(__CLASS__.'_form_data') );
        parent::add($this->form);
    }
}
?>
