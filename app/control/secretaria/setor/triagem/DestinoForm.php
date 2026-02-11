<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class DestinoForm extends TPage
{
    use Adianti\Base\AdiantiStandardFormTrait;

    public function __construct()
    {
        parent::__construct();

        // configurações de formulário
        $this->setDatabase('banco_atendimento');
        $this->setActiveRecord('Destino');

        $this->form = new BootstrapFormBuilder('form_Destino');
        $this->form->setFormTitle('Cadastro de Destino');

        $id      = new TEntry('id');
        $destino = new TEntry('destino');

        $id->setEditable(false);

        $this->form->addFields(['ID'],      [$id]);
        $this->form->addFields(['Destino'], [$destino]);

        $this->form->addAction('Save', new TAction([$this, 'onSave']), 'fa:save');
        $this->form->addAction('Back', new TAction(['DestinoList','onReload']), 'fa:arrow-left');

        $this->form->setData( TSession::getValue(__CLASS__.'_form_data') );
        parent::add($this->form);
    }
}
?>
