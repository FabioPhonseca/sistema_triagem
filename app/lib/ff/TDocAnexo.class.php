<?php
/**
 * Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
use Adianti\Registry\TSession;
use Adianti\Widget\Dialog\TMessage;

class TDocAnexo{
	private $file_type_path = '';
    private $file_path = 'tmp/';
	private $file_name = '';
    private $file_temp_to_delete = '';

	public function __construct($file_name = null, $type = null, $moveTempToFolder = ['mover' => false, 'dest_file_name' => null, 'backup' => false]){
		$this->file_path = 'tmp/'.TSession::getValue('login').'/';

        if (is_null($file_name)){
			throw new Exception('Um TAnexo precisa de um nome de arquivo.');
		}

        // caso o tipo de nome enviado seja do filehandling
        if (strpos($file_name, 'tmp')){
            $file_name = str_replace('tmp/', '', json_decode(urldecode($file_name))->fileName);
        }

		if (is_null($type)){
			throw new Exception('Um TAnexo precisa de um tipo.');
		}

		$ini = parse_ini_file('app/config/application.ini', true);
		if (!array_key_exists('path_GED', $ini['operacoes'])){
			throw new Exception('A pasta do GED não está definida em "application".');
		}

		if (!array_key_exists( $type, TTipoDocAnexo::$list)){
			throw new Exception('Um TAnexo precisa de um tipo valido nas configurações.');
		}

		$this->file_type_path = $ini['operacoes']['path_GED'].TTipoDocAnexo::$list[$type]['folder'];
		$this->file_name = $file_name;

		if ($moveTempToFolder['backup']){
            $backup_path = $ini['operacoes']['path_backup_uploaded_docs'].TTipoDocAnexo::$list[$type]['folder'];
			// copia arquivo para o backup
            $this->moveToNewFolder($backup_path, $moveTempToFolder['dest_file_name']);
            
            // copia do backup para a pasta destino real
            // apenas cria o diretorio destino SOB DEMANDA caso ele ainda não exista.
            if (!file_exists($this->file_type_path)){
                mkdir($this->file_type_path, 0777, true);
            }  
            if (!copy($backup_path.$moveTempToFolder['dest_file_name'], $this->file_type_path.$moveTempToFolder['dest_file_name'])){
                throw new Exception('Erro ao copiar o arquivo'.__CLASS__.' '.__METHOD__.' '.$backup_path.$moveTempToFolder['dest_file_name']);
            }   
		}else{
            // se o backup não tiver ativado, copia direto do tmp/usuario para a pasta destino real fsrio...
            if ($moveTempToFolder['mover']){
                $this->moveToNewFolder($this->file_type_path, $moveTempToFolder['dest_file_name']);
            }
        }

	}

	public function getFullFileName(){
		return $this->file_path.$this->file_name;
	}

	public function getFullTypeFileName(){
		return $this->file_type_path.$this->file_name;
	}    

    public function getNewFileName(){        
    	return $this->file_name;
	}

	public function moveToNewFolder($dest_path, $dest_file_name){
        if (is_null($dest_path) || is_null($dest_file_name)){
            throw new Exception('Faltam parametros para a função '.__CLASS__.' '.__METHOD__);
        }
        $this->moveFile($this->file_path, $this->file_name, $dest_path, $dest_file_name, true);
	}	

    public function moveFile($orig_path = null, $orig_file_name = null, $dest_path = null, $dest_file_name = null, $create_dest_path = FALSE)
    {
        try{
            // os parametros de nome de arquivo e destino devem ser preenchidos
            if (is_null($orig_path) || is_null($orig_file_name) || is_null($dest_path) || is_null($dest_file_name)) {
                throw new Exception('Faltam parametros para a função '.__CLASS__.' '.__METHOD__);
            }
            
            // o arquivo original deve existir no diretorio original
            if (!file_exists($orig_path.$orig_file_name)) {
                throw new Exception('Arquivo anexo não encontrado '.__CLASS__.' '.__METHOD__.' - '.$orig_path.$orig_file_name);
            }

            // apenas cria o diretorio destino SOB DEMANDA caso ele ainda não exista.
            if (!file_exists($dest_path) && $create_dest_path){
                mkdir($dest_path, 0777, true);
            }    
            
            $target_path =  $dest_path;
            $target_path = str_replace('//', '/', $target_path);
            
            $source_file = $orig_path.$orig_file_name;
            $target_file = $target_path.$dest_file_name;
            
            // copia o arquivo para a pasta destino
            if (!copy($source_file, $target_file)){
                throw new Exception('Erro ao copiar o arquivo'.__CLASS__.' '.__METHOD__.' '.$target_file);
            }    

            // marca nome do arquivo que deverá ser apagado
            $this->file_temp_to_delete = $this->file_path.$this->file_name;
            // apaga o arquivo da pasta original
            $this->file_path = $this->file_type_path;
            $this->file_name = $dest_file_name;
        }catch(Exception | ErrorException $e){
            new TMessage('error', $e->getMessage());
        }
    }

    public function deleteTemFile(){
        if (file_exists($this->file_temp_to_delete)){
            unlink($this->file_temp_to_delete);
        }
    }
}

?>