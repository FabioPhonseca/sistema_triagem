<?php
/**
 * TFFlog
 * Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * 
 * Date: 11/08/16
 * Time: 14:36
 * Classe para gravar logs
 *
 * Para funcionar é preciso que no php.ini a variavel error_log deve estar habilitada
 * arquivos depuracao.log e producao.log devem existir e ter  permissao de escrita
 */

class TFFLog {

    public static function writeLogArray($array, $arqDestino = "./tmp/depuracao.log"){
        if (is_array($array)){
            error_log(TSession::getValue('login').': '.date("Y-m-d H:i:s").' -> '.implode(';', $array)." \n", 3, $arqDestino);
        }else if (is_string($array)){
            self::writeLog($array);
        }else{
            error_log(TSession::getValue('login').': '.date("Y-m-d H:i:s").' -> '.serialize($array)." \n", 3, $arqDestino);
        }
   }
    
    public static function writeLog($texto, $arqDestino = "./tmp/depuracao.log"){
         error_log(TSession::getValue('login').': '.date("Y-m-d H:i:s").' -> '.json_encode($texto)." \n", 3, $arqDestino);
    }

    static public function writeLogDep($variavel = null, $texto = null , $metodo = null, $classe = null){
       if ((!is_null($texto) || (!is_null($variavel)))){
           $classe = is_null($classe) ? null : $classe.':';
           $metodo = is_null($metodo) ? null : $metodo.':';
         $variavel = var_export($variavel, true);
         error_log(TSession::getValue('login').': '.date("Y-m-d H:i:s")." $classe $metodo $texto = $variavel \n", 3, "/usr/share/{$_SERVER['SERVER_NAME']}/tmp/depuracao.log");
       }
    }

    static public function writeLogPro($variavel = null, $texto = null , $metodo = null, $classe = null){
        if (!is_null($texto)){
            $classe = is_null($classe) ? null : $classe.':';
            $metodo = is_null($metodo) ? null : $metodo.':';
            error_log(TSession::getValue('login').': '.date("Y-m-d H:i:s")." $classe $metodo $texto = $variavel \n", 3, "/usr/share/{$_SERVER['SERVER_NAME']}/tmp/producao.log");
        }
    }

}