<?php
class EgPessoaBasicoService
{
    protected $selectFieldsBasic = <<<SQL
    SELECT DISTINCT
            FUNC.CPF,
            VINC.MATRIC AS MATRICULA,
            FUNC.NOME,
            VINC.NUMERO AS NUMVINC,
            VINC.DTVAC AS DT_VACANCIA,
            VINC.DTAPOSENT AS DT_APOSENTADORIA,
            PROV.DTINI AS DT_INICIO_PROVENTO,
            PROV.DTFIM AS DT_FIM_PROVENTO,
            PROV.SETOR,
            PROV.CARGO,
            CRGO.NOME AS NOME_CARGO,
            CRGO.CATEGORIA AS CATEGORIA_CARGO,
            CRGO.SUBCATEGORIA AS SUBCATEGORIA_CARGO,
            CRGO.TIPO_CARGO,
            STOR.NOMESETOR,
            STOR.FLEX_CAMPO_01 AS COMPLEMENTO,
            SECR.FLEX_CAMPO_01 AS SECRETARIA,
            DESG.DTINI AS DESG_DTINI,
            DESG.FUNCAO AS DESG_FUNCAO,
            FG.NOME AS NOME_FG,
            HTFC.REFERENCIA AS REFERENCIA_FG,
            PROV.JORNADA AS CARGA_HORARIA,
            FUNC.FLEX_CAMPO_90 AS EMAIL,
            FUNC.TELEFONE,
            FUNC.NUMTEL_CELULAR
    SQL;

    protected $baseSQL = <<<SQL
        FROM BANCORH.FUNCIONARIOS FUNC
        LEFT JOIN BANCORH.VINCULOS VINC ON FUNC.NUMERO = VINC.NUMFUNC
        LEFT JOIN BANCORH.PROVIMENTOS_EV PROV ON VINC.NUMFUNC = PROV.NUMFUNC
            AND VINC.NUMERO = PROV.NUMVINC AND PROV.EMP_CODIGO = VINC.EMP_CODIGO
        LEFT JOIN BANCORH.CARGOS_ CRGO ON CRGO.CARGO = PROV.CARGO
        LEFT JOIN BANCORH.HSETOR_ STOR ON STOR.SETOR = PROV.SETOR
            AND STOR.EMP_CODIGO = VINC.EMP_CODIGO AND STOR.DATAFIM IS NULL
        LEFT JOIN BANCORH.HSETOR_ SECR ON STOR.FLEX_CAMPO_05 = SECR.SETOR
            AND SECR.DATAFIM IS NULL AND SECR.EMP_CODIGO = STOR.EMP_CODIGO
        LEFT JOIN BANCORH.DESIGNACOES_EV DESG ON VINC.NUMFUNC = DESG.NUMFUNC
            AND VINC.NUMERO = DESG.NUMVINC AND VINC.EMP_CODIGO = DESG.EMP_CODIGO
            AND DESG.DTFIM IS NULL
        LEFT JOIN BANCORH.HIST_FUNCAO_EV HTFC ON DESG.FUNCAO = HTFC.FUNCAO
            AND DESG.DTFIM IS NULL
            AND DESG.DTINI BETWEEN HTFC.DTINI AND NVL(HTFC.DTFIM, TO_DATE('9999-12-31','YYYY-MM-DD'))
        LEFT JOIN BANCORH.FUNCOES_EV_ FG ON DESG.FUNCAO = FG.FUNCAO
        WHERE PROV.DTINI = (
            SELECT MAX(P2.DTINI)
            FROM BANCORH.PROVIMENTOS_EV P2
            WHERE PROV.NUMFUNC = P2.NUMFUNC
            AND PROV.NUMVINC = P2.NUMVINC
        )
SQL;

        public function getLista(array $filters, string $orderColumn, string $orderDirection, int $limit, int $offset): array
    {
        // validação mínima: CPF ou Nome (>=5 caracteres)
        $cpf  = trim($filters['CPF'] ?? '');
        $nome = trim($filters['NOME'] ?? '');
        if (empty($cpf) && mb_strlen($nome) < 5) {
            // sem filtros válidos, retorna vazio
            return [];
        }

        TTransaction::open('banco_oracle');
        $conn = TTransaction::get();

        $sql = $this->baseSQL;
        // aplicar filtros
        $clauses = [];
        foreach ($filters as $field => $value) {
            $val = trim($value);
            if ($val !== '') {
                $clauses[] = "{$field} LIKE '%" . addslashes($val) . "%'";
            }
        }
        if ($clauses) {
            $sql .= " AND " . implode(' AND ', $clauses);
        }

        // ordenação e paginação
        $sql .= " ORDER BY {$orderColumn} {$orderDirection}";
        $sql .= " OFFSET {$offset} ROWS FETCH NEXT {$limit} ROWS ONLY";

        $result = $conn->query($sql);
        $items  = $result->fetchAll(PDO::FETCH_ASSOC);
        TTransaction::close();

        // mapear para objetos EgPessoaBasico
        return array_map(function($row) {
            $obj = new EgPessoaBasico;
            foreach ($row as $key => $value) {
                $prop = strtoupper($key);
                $obj->$prop = $value;
            }
            return $obj;
        }, $items);
    }

    public function getTotal(array $filters): int
    {
        // mantém mesma lógica de validação
        $cpf  = trim($filters['CPF'] ?? '');
        $nome = trim($filters['NOME'] ?? '');
        if (empty($cpf) && mb_strlen($nome) < 5) {
            return 0;
        }

        TTransaction::open('banco_oracle');
        $conn = TTransaction::get();
        $sqlCount = "SELECT COUNT(*) AS TOTAL FROM ({$this->baseSQL}) SUB";
        $clauses  = [];
        foreach ($filters as $field => $value) {
            $val = trim($value);
            if ($val !== '') {
                $clauses[] = "{$field} LIKE '%" . addslashes($val) . "%'";
            }
        }
        if ($clauses) {
            $sqlCount .= " AND " . implode(' AND ', $clauses);
        }
        $total = (int) $conn->query($sqlCount)->fetchColumn();
        TTransaction::close();
        return $total;
    }
}