<?php
/**
 * Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 */
class TListMesesCompetencia{
	
	public static function lista_competencias($order = 'decrescente', $qtd_meses_antes = 12, $qtd_meses_depois = 1){
		$lista = array();

		$mes_atual = strtotime(date('Y-m-01'));
		$mes_limite_inferior = strtotime('-'.$qtd_meses_antes.' months', $mes_atual);
		$mes_limite_superior = strtotime('+'.$qtd_meses_depois.' months', $mes_atual);

		if ($mes_limite_inferior >= $mes_limite_superior){
			$lista['1'] = 'Valores de limite informados estão incorretos';
			return $lista;
		}

		while ($mes_limite_superior >= $mes_limite_inferior){
			$operador = '-';
			if($order == 'crescente'){
				$operador = '+';
				$aux = $mes_limite_inferior;
				$mes_limite_inferior = $mes_limite_superior;
				$mes_limite_superior = $aux;
			}

			$lista[(string) date('m/Y', $mes_limite_superior)] = (string) date('m/Y', $mes_limite_superior);
			$mes_limite_superior = strtotime($operador.'1'.' months', $mes_limite_superior);

			if($order == 'crescente'){
				$operador = '+';
				$aux = $mes_limite_inferior;
				$mes_limite_inferior = $mes_limite_superior;
				$mes_limite_superior = $aux;
			}
		}
		return $lista;
	}
}
?>