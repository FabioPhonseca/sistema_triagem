<?php
/**
 * Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 */
class TListAnos{
	
	public static function lista_anos($order = 'decrescente', $qtd_anos_antes = 2,  $ano = null){
		$lista = array();

		$ano_atual = is_null($ano) ? date('Y') : $ano;
		$ano_aux = $ano_atual;
		if ($order == 'decrescente'){
			while (((int) $ano_aux) >= (((int) $ano_atual) - $qtd_anos_antes)){
		    	$lista[$ano_aux] = $ano_aux;
				$ano_aux -= 1;
			}
		}
		return $lista;
	}
}
?>