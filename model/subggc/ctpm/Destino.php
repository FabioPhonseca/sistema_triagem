<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class Destino extends TRecord
{
    const TABLENAME = 'destino';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';

    protected static $database = 'banco_atendimento';

    public static $fld_fmt_destino = [
        'id'      => ['caption'=>'# ID',    'mini_caption'=>'ID'],
        'destino' => ['caption'=>'Destino', 'mini_caption'=>'Dest.'],
    ];    
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('destino');
    }
}
?>