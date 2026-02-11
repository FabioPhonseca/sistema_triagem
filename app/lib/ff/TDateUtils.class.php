<?php
/**
 * Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */

class TDateUtils {

   public static function isValidUsDateTime($date, $format = 'Y-m-d H:i:s'){
       $d = DateTime::createFromFormat($format, $date);
       return $d && $d->format($format) == $date;
   }

   public static function isValidBrDateTime($date, $format = 'd-m-Y H:i:s'){
       $d = DateTime::createFromFormat($format, $date);
       return $d && $d->format($format) == $date;
   }

   public static function isValidUsDate($date, $format = 'Y-m-d'){
       $d = DateTime::createFromFormat($format, $date);
       return $d && $d->format($format) == $date;
   }

   public static function isValidBrDate($date, $format = 'd-m-Y'){
       $d = DateTime::createFromFormat($format, $date);
       return $d && $d->format($format) == $date;
   }

   public static function DateUsToBr($value){
	   if (!(empty($value) && !is_null($value))) {
		   $value = DateTime::createFromFormat('Y-m-d', $value);
		   $value = $value->format('d/m/Y');
	   } else {
		   $value = NULL;
	   }
	   return $value;  
   }

   public static function DateBrToUs($value){
	   if (!(empty($value) && !is_null($value))) {
		    $value = DateTime::createFromFormat('d/m/Y', $value);
			$value = $value->format('Y-m-d');
	   }else{
		   $value = NULL;
	   }
	   return $value;
   }

	public static function DateTimeUsToBr($value){
		if (!(empty($value) && !is_null($value))) {
			$value = DateTime::createFromFormat('Y-m-d H:i:s', $value);
			$value = $value->format('d/m/Y H:i:s');
		}else{
			$value = NULL;
		}
		return $value;
	}

	public static function DateTimeBrToUs($value){
		$regex = '/^(19|20)\d\d-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])\s(0\d|1\d|2[0-3]):([0-5]\d):([0-5]\d)$/';
		if (preg_match($regex, $value)) {
			return $value;
		}
		if (!(empty($value) && !is_null($value))) {
			$value = DateTime::createFromFormat('d/m/Y H:i:s', $value);
			$value = $value->format('Y-m-d H:i:s');
	   }else{
		   $value = NULL;
	   }
	   return $value;
   }

  public static function secondsToTime($seconds) {
	 	$tempo = '';
	  	if(($seconds/86400) >= 1){
	  		$tempo = '%a dias, %h horas, %i minutos e %s segundos';
	  	}elseif(($seconds/3600) >= 1){
	  		$tempo = '%h horas, %i minutos e %s segundos';
	  	}elseif(($seconds/60) >= 1){
	  		$tempo = '%i minutos e %s segundos';
	  	}else{
	  		$tempo = '%s segundos';
	  	}
	  	
	    $dtF = new \DateTime('@0');
	    $dtT = new \DateTime("@$seconds");
	    return $dtF->diff($dtT)->format($tempo);
	}
	
	// return date type
	public static function DtTimeAddDays($data, $days) {
			return date('Y-m-d H:i:s', strtotime('+'.$days.' days', strtotime($data)));
	}

	// return date type
	public static function DtTimeRemoveDays($data, $days) {
		return date('Y-m-d H:i:s', strtotime('-'.$days.' days', strtotime($data)));
	}

	// return date type
	public static function DtAddDays($data, $days) {
		return date('Y-m-d', strtotime('+'.$days.' days', strtotime($data)));
	}

	// return date type
	public static function DtRemoveDays($data, $days) {
		return date('Y-m-d', strtotime('-'.$days.' days', strtotime($data)));
	}

	public static function DateUsResetDay($value){
		if (!(empty($value) && !is_null($value))) {
			 $value = DateTime::createFromFormat('Y-m-d', $value);
			 $value = $value->format('Y-m-01');
		}else{
			$value = NULL;
		}
		return $value;
	}

	public static function DateBrResetDay($value){
		if (!(empty($value) && !is_null($value))) {
			 $value = DateTime::createFromFormat('d/m/Y', $value);
			 $value = $value->format('01/m/Y');
		}else{
			$value = NULL;
		}
		return $value;
	}

	public static function DateBrToMonthYearUs($value){
		if (!(empty($value) && !is_null($value))) {
			 $value = DateTime::createFromFormat('d/m/Y', $value);
			 $value = $value->format('Y-m');
		}else{
			$value = NULL;
		}
		return $value;
	}

	public static function DateUsToMonthYearUs($value){
		if (!(empty($value) && !is_null($value))) {
			 $value = DateTime::createFromFormat('Y-m-d', $value);
			 $value = $value->format('Y-m');
		}else{
			$value = NULL;
		}
		return $value;
	}

	public static function MonthYearBrToDateBr($value){
		if (!(empty($value) && !is_null($value))) {
			 $value = DateTime::createFromFormat('m/Y', $value);
			 $value = $value->format('01/m/Y');
		}else{
			$value = NULL;
		}
		return $value;
	}

	// formato 22-JUL-25 d-M-y
	public static function DateUsdMyToBr($value){
		$resultado = null;
		if (!empty($value)) {
			$value = strtoupper($value); // garante que o mês esteja em maiúsculas
			$date = DateTime::createFromFormat('d-M-y', $value);
			if ($date !== false) {
				$resultado = $date->format('d/m/Y');
			}
		}
		return $resultado;
	}

	public static function TimeCompactToH_i($value) {
		$resultado = null;
		if (!empty($value)) {
			// Verifica se tem 3 ou 4 dígitos
			if (preg_match('/^(\d{1,2})(\d{2})$/', $value, $matches)) {
				$hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
				$minute = $matches[2];
				$resultado = "{$hour}:{$minute}";
			}
		}
		return $resultado;
	}

	public static function isValidDateDMY($value): bool
	{
		// Deve ser string
		if (!is_string($value)) {
			return false;
		}

		// Tenta criar a data no formato desejado
		$d = DateTime::createFromFormat('d/m/Y', $value);

		// Verifica se parsing ocorreu sem erros e se o formato bate exatamente
		return $d
			&& $d->format('d/m/Y') === $value
			&& empty(DateTime::getLastErrors()['warning_count'])
			&& empty(DateTime::getLastErrors()['error_count']);
	}	
}