<?php
/**
 * Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
class TCnpjCpfUtils{

    public static function validarCNPJ(string $CNPJ) : int {
        $INDICE = 1;
        $SOMA = 0;
        $DIGITO_1 = 0;
        $DIGITO_2 = 0;
        $VAR1 = 5;
        $VAR2 = 0;
        $DIGITO_1_CNPJ = "";
        $DIGITO_2_CNPJ = "";
        $NR_DOCUMENTO_AUX = trim($CNPJ);

        // Remove caracteres não numéricos e verifica tamanho
        $nr = preg_replace('/\D/', '', $NR_DOCUMENTO_AUX);
        if (strlen($nr) != 14) {
            return 0;
        }
        // Elimina CNPJs formados por dígitos repetidos
        if (str_repeat($nr[0], 14) === $nr) {
            return 0;
        }

        // Cálculo primeiro dígito verificador
        for ($i = 0; $i < 4; $i++) {
            $SOMA += ((int)$nr[$i]) * $VAR1;
            $VAR1--;
        }
        for ($i = 4; $i < 12; $i++) {
            $SOMA += ((int)$nr[$i]) * $VAR2;
            $VAR2 = ($VAR2 == 0) ? 9 : $VAR2 - 1;
        }
        $resto = $SOMA % 11;
        $DIGITO_1 = ($resto < 2) ? 0 : 11 - $resto;

        // Cálculo segundo dígito verificador
        $SOMA = 0;
        $VAR1 = 6;
        for ($i = 0; $i < 5; $i++) {
            $SOMA += ((int)$nr[$i]) * $VAR1;
            $VAR1--;
        }
        for ($i = 5; $i < 13; $i++) {
            $SOMA += ((int)$nr[$i]) * $VAR2;
            $VAR2 = ($VAR2 == 0) ? 9 : $VAR2 - 1;
        }
        $resto = $SOMA % 11;
        $DIGITO_2 = ($resto < 2) ? 0 : 11 - $resto;

        // Valida dígitos verificadores
        if ($DIGITO_1 === (int)$nr[12] && $DIGITO_2 === (int)$nr[13]) {
            return 1;
        }
        return 0;
    }

    public static function validarCPF(string $CPF) : int {
        // Remove caracteres não numéricos e verifica tamanho
        $nr = preg_replace('/\D/', '', trim($CPF));
        if (strlen($nr) != 11) {
            return 0;
        }
        // Elimina CPFs formados por dígitos repetidos
        if (str_repeat($nr[0], 11) === $nr) {
            return 0;
        }
        // Cálculo primeiro dígito verificador
        $SOMA = 0;
        for ($i = 0; $i < 9; $i++) {
            $SOMA += ((int)$nr[$i]) * (10 - $i);
        }
        $resto = $SOMA % 11;
        $DIGITO_1 = ($resto < 2) ? 0 : 11 - $resto;
        if ($DIGITO_1 !== (int)$nr[9]) {
            return 0;
        }
        // Cálculo segundo dígito verificador
        $SOMA = 0;
        for ($i = 0; $i < 10; $i++) {
            $SOMA += ((int)$nr[$i]) * (11 - $i);
        }
        $resto = $SOMA % 11;
        $DIGITO_2 = ($resto < 2) ? 0 : 11 - $resto;
        if ($DIGITO_2 !== (int)$nr[10]) {
            return 0;
        }
        return 1;
    }

    public static function getBaseCNPJOrCPF(string $numero) : int {
        // VERIFICA SE É UM CNPJ
        $validacaoNumero = TCnpjCpfUtils::validarCNPJ(str_pad(trim(str_replace(['.', '/', '-'], '', $numero)), 14, '0', STR_PAD_LEFT));
        if ($validacaoNumero === 1) {
            // se o CNPJ está formatado
            if (strpos(trim($numero), '/') > 0) {
                $posBarra = strpos(trim($numero), '/');
                $prefixoCNPJ = substr(trim($numero), 0, $posBarra);
                $cnpjSemPontos = str_replace('.', '', $prefixoCNPJ);
                $cnpjAjustado = str_pad($cnpjSemPontos, 8, '0', STR_PAD_LEFT);
                return (int) $cnpjAjustado;
            }
            // se o cnpj tiver só numeros
            elseif (strpos(trim($numero), '/') === false && strpos(trim($numero), '.') === false && strlen(trim($numero)) > 11) {
                return (int) substr(trim($numero), 0, -6);
            }
            else {
                return (int) substr(str_pad(trim(str_replace(['.', '/', '-'], '', $numero)), 14, '0', STR_PAD_LEFT), 0, 8);
            }
        } else { // else é um cpf
            return (int) str_pad(str_replace(['.', '/', '-'], '', $numero), 11, '0', STR_PAD_LEFT);
        }
        return (int)$numero;
    }

}

?>
