<?php
/**
 * 
 * @Author: Fabio Fonseca <ff@fabiofonseca.com.br>
 * Based on Adianti Framework (LGPL) - Pablo Dall’Oglio
 */
use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Container\TVBox;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

class TriagemComparecimentoDashboard extends TPage
{
    // id_destino => cor. Ex: 3 (Perícia) é verde.
    private array $palette = [
        1         => '#FF851B', // Balcão
        2         => '#0073B7', // Fono
        3         => '#00A65A', // Perícia
        4         => '#DD4B39', // Seso
        5         => '#00C0EF', // Outros
        'triagem' => '#605CA8', // Triagem
    ];

    private string $baseDate; // YYYY-mm-dd
    const SESSION_BASE_DATE = 'triagem_base_date';

    // Helper para pegar cor por id ou chave
    private function color($key): string
    {
        return $this->palette[$key] ?? '#999999';
    }

    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();

        // --------- DATA-BASE (sessão -> hoje) ----------
        $this->baseDate = TSession::getValue(self::SESSION_BASE_DATE) ?: date('Y-m-d');
        // ------------------------------------------------

        try
        {
            // ===== Filtro (Data base) =====
            $form = new BootstrapFormBuilder('form_filter_base_date');
            $form->setProperty('style','margin-bottom:10px');

            $lbl = new TLabel('Escolha um dia como data base do dashboard');
            $dt  = new TDate('basedate');
            $dt->setMask('dd/mm/yyyy');
            $dt->setValue($this->formatBR($this->baseDate));

            $form->addFields([$lbl], [$dt]);
            $form->addAction('Aplicar', new TAction([$this, 'onApplyBaseDate']), 'fa:filter blue');

            // ===== Layout principal =====
            $html = new THtmlRenderer('app/resources/SECRETARIA/triagem/dashboard_triagem.html');

            TTransaction::open('banco_atendimento');
            //TTransaction::dump('/tmp/log_SETOR.txt');

            // ==================== INDICADORES (dia da base) ====================
            $indicator1 = new THtmlRenderer('app/resources/SECRETARIA/triagem/info-box.html');
            $indicator2 = new THtmlRenderer('app/resources/SECRETARIA/triagem/info-box.html');
            $indicator3 = new THtmlRenderer('app/resources/SECRETARIA/triagem/info-box.html');
            $indicator4 = new THtmlRenderer('app/resources/SECRETARIA/triagem/info-box.html');
            $indicator5 = new THtmlRenderer('app/resources/SECRETARIA/triagem/info-box.html');
            $indicator6 = new THtmlRenderer('app/resources/SECRETARIA/triagem/info-box.html');
            $indicator7 = new THtmlRenderer('app/resources/SECRETARIA/triagem/info-box.html');
            $indicator8 = new THtmlRenderer('app/resources/SECRETARIA/triagem/info-box.html');
            $indicator9 = new THtmlRenderer('app/resources/SECRETARIA/triagem/info-box.html');

            $indicator1->enableSection('main', [
                'title' => 'Triagem',
                'icon' => 'users',
                'background' => 'purple',
                'value' => Comparecimento::where('data_chegada', '=', $this->baseDate)
                                            ->where('origem_registro', '=', 'TRIAGEM')
                                            ->count()
            ]);

            $indicator2->enableSection('main', [
                'title' => 'PERÍCIA',
                'icon' => 'user-doctor',
                'background' => 'green',
                'value' => Comparecimento::where('data_chegada', '=', $this->baseDate)
                                            ->where('id_destino', '=', 3)
                                            ->where('origem_registro', '=', 'TRIAGEM')->count()
            ]);

            $indicator3->enableSection('main', [
                'title' => 'BALCÃO',
                'icon' => 'user',
                'background' => 'orange',
                'value' => Comparecimento::where('data_chegada', '=', $this->baseDate)
                                            ->where('id_destino', '=', 1)
                                            ->where('origem_registro', '=', 'TRIAGEM')->count()
            ]);

            $indicator4->enableSection('main', [
                'title' => 'SESO',
                'icon' => 'hand-holding-heart',
                'background' => 'red',
                'value' => Comparecimento::where('data_chegada', '=', $this->baseDate)
                                            ->where('id_destino', '=', 4)
                                            ->where('origem_registro', '=', 'TRIAGEM')->count()
            ]);

            $indicator5->enableSection('main', [
                'title' => 'FONO',
                'icon' => 'microphone',
                'background' => 'blue',
                'value' => Comparecimento::where('data_chegada', '=', $this->baseDate)
                                            ->where('id_destino', '=', 2)
                                            ->where('origem_registro', '=', 'TRIAGEM')
                                            ->count()
            ]);

            $indicator6->enableSection('main', [
                'title' => 'OUTROS',
                'icon' => 'clipboard-question',
                'background' => 'aqua',
                'value' => Comparecimento::where('data_chegada', '=', $this->baseDate)
                                            ->where('id_destino', '=', 5)
                                            ->where('origem_registro', '=', 'TRIAGEM')
                                            ->count()
            ]);

            $indicator7->enableSection('main', [
                'title' => 'PESSOAS AGUARDANDO',
                'icon' => 'hourglass-half',
                'background' => 'grey',
                'value' => Comparecimento::where('data_chegada', '=', $this->baseDate)
                                            ->where('chamado', 'is', null)
                                            ->where('origem_registro', '=', 'TRIAGEM')
                                            ->count()
            ]);

            $indicator8->enableSection('main', [
                'title' => 'PERÍCIA SEM AGENDAMENTO NO bancorh',
                'icon' => 'user-plus',
                'background' => 'grey',
                'value' => Comparecimento::where('data_chegada', '=', $this->baseDate)
                                            ->where('id_destino', '=', 3)
                                            ->where('E_siglaexame', 'is', null)
                                            ->where('origem_registro', '=', 'TRIAGEM')
                                            ->count()
            ]);

            $indicator9->enableSection('main', [
                'title' => 'OUTROS',
                'icon' => 'clipboard-question',
                'background' => 'grey',
                'value' => Comparecimento::where('data_chegada', '=', $this->baseDate)
                                            ->where('id_destino', '=', 5)
                                            ->where('origem_registro', '=', 'TRIAGEM')
                                            ->count()
            ]);

            // ==================== JANELAS (7 e 30 dias até a base) ====================
            $r7  = $this->range($this->baseDate, 7);
            $r30 = $this->range($this->baseDate, 30);

            // ==================== CHART 1 (Bar - últimos 7 dias até a base) ====================
            $chart1 = new THtmlRenderer('app/resources/SECRETARIA/triagem/google_bar_chart.html');

            $data1 = [];
            $data1[] = [ 'Group', 'Triagem' ];

            $stats1 = Comparecimento::where('data_chegada', '>=', $r7['ini'])
                                    ->where('data_chegada', '<=', $r7['fim'])
                                    ->where('origem_registro', '=', 'TRIAGEM')
                                    ->groupBy('data_chegada')
                                    ->countBy('id', 'count');
            if ($stats1)
            {
                foreach ($stats1 as $row)
                {
                    $data1[] = [ $row->data_chegada, (int) $row->count];
                }
            }

            $chart1->enableSection('main', [
                'data'   => json_encode($data1),
                'width'  => '100%',
                'height' => '500px',
                'title'  => 'Quantidade de pessoas recebidas na triagem por dia (7 dias até '.$this->formatBR($r7['fim']).')',
                'ytitle' => 'Data',
                'xtitle' => 'Quantidade',
                'uniqid' => uniqid(),
                // opcional: uma cor única para as barras
                //'bar_color' => $this->color('triagem'),
            ]);

            // ==================== CHART 2 (Pie - últimos 30 dias até a base) ====================
            $chart2 = new THtmlRenderer('app/resources/SECRETARIA/triagem/google_pie_chart.html');

            $data2 = [];
            $data2[] = [ 'Destino', 'Total' ];

            $stats2 = Comparecimento::where('data_chegada', '>=', $r30['ini'])
                                    ->where('data_chegada', '<=', $r30['fim'])
                                    ->where('origem_registro', '=', 'TRIAGEM')
                                    ->groupBy('id_destino')
                                    ->countBy('id', 'count');

            if ($stats2)
            {
                foreach ($stats2 as $row)
                {
                    $rotulo = Destino::find($row->id_destino)->destino; // ex: "PERÍCIA", "BALCÃO", "SESO", "FONO", "OUTROS"
                    $data2[] = [ $rotulo, (int) $row->count ];
                }
            }

            // cores por rótulo (devem bater com os rótulos da primeira coluna de $data2)
            $labelColorsPie = [
                'BALCÃO'  => $this->color(1),
                'FONO'    => $this->color(2),
                'SESO'    => $this->color(4),
                'PERÍCIA' => $this->color(3),
                'OUTRO'  => $this->color(5),
            ];

            $chart2->enableSection('main', [
                'data'         => json_encode($data2, JSON_NUMERIC_CHECK),
                'label_colors' => json_encode($labelColorsPie, JSON_UNESCAPED_UNICODE),
                'title'        => 'Distribuição de atendimentos por destino (30 dias até '.$this->formatBR($r30['fim']).')',
                'width'        => '100%',
                'height'       => '500px',
                'xtitle'       => '',
                'ytitle'       => '',
                'uniqid'       => uniqid(),
                'pie_hole'     => 0.25  // 0 = pizza; 0.25 = donut leve
            ]);

            // ==================== CHART 3 (Column - últimos 30 dias até a base) ====================
            $chart3 = new THtmlRenderer('app/resources/SECRETARIA/triagem/google_column_chart.html');

            $sql = "
                SELECT
                    CONCAT(LPAD(LEFT(hora, 2), 2, '0'), 'H') AS hora,
                    SUM(CASE WHEN id_destino = 3 THEN 1 ELSE 0 END) AS pericia,
                    SUM(CASE WHEN id_destino = 1 THEN 1 ELSE 0 END) AS balcao,
                    SUM(CASE WHEN id_destino NOT IN (1, 3) THEN 1 ELSE 0 END) AS outros
                FROM comparecimento
                WHERE data_chegada BETWEEN :ini30 AND :fim
                    AND origem_registro = 'TRIAGEM'
                GROUP BY CONCAT(LPAD(LEFT(hora, 2), 2, '0'), 'H')
                ORDER BY hora
            ";

            $params = [
                ':ini30' => $r30['ini'],
                ':fim'   => $r30['fim'],
            ];

            $conn = TTransaction::get();               // PDO
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data3 = [];
            $data3[] = ['Hora', 'Perícia', 'Balcão', 'Outros'];
            foreach ($rows as $r) {
                $data3[] = [
                    $r['hora'],
                    (int) $r['pericia'],
                    (int) $r['balcao'],
                    (int) $r['outros'],
                ];
            }

            $labelColorsCol = [
                'Perícia' => $this->color(3),
                'Balcão'  => $this->color(1),
                'Outros'  => $this->color(5),
            ];

            $chart3->enableSection('main', [
                'data'         => json_encode($data3, JSON_NUMERIC_CHECK),
                'label_colors' => json_encode($labelColorsCol, JSON_UNESCAPED_UNICODE),
                'width'        => '100%',
                'height'       => '300px',
                'title'        => 'Volume por hora e destino (30 dias até '.$this->formatBR($r30['fim']).')',
                'ytitle'       => 'Quantidade',
                'xtitle'       => 'Hora de chegada',
                'uniqid'       => uniqid(),
            ]);

            // ==================== Subtítulos ====================
            $subIndicadores = new TElement('h3');
            $subIndicadores->style = 'margin:10px 0 15px';
            $subIndicadores->add('Números do dia '.$this->formatBR($this->baseDate).' ('. date('H:i') . ')');

            $subCharts = new TElement('h3');
            $subCharts->style = 'margin:25px 0 10px';
            $subCharts->add('Dados Históricos agrupados');

            $html->enableSection('main', [
                'subtitle_indicadores' => $subIndicadores,
                'subtitle_charts'      => $subCharts,
                'indicator1' => $indicator1,
                'indicator2' => $indicator2,
                'indicator3' => $indicator3,
                'indicator4' => $indicator4,
                'indicator5' => $indicator5,
                'indicator6' => $indicator6,
                'indicator7' => $indicator7,
                'indicator8' => $indicator8,
                'indicator9' => $indicator9,
                'chart1'     => $chart1,
                'chart2'     => $chart2,
                'chart3'     => $chart3
            ]);

            $container = new TVBox;
            $container->style = 'width: 100%';
            $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));

            // adiciona o filtro de data no topo
            $container->add($form);

            $container->add($html);

            parent::add($container);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            parent::add($e->getMessage());
        }
    }

    // ==================== AÇÕES / HELPERS ====================

    public function onApplyBaseDate($param)
    {
        // aceita d/m/Y ou Y-m-d
        $us = $this->normalizeUS($param['basedate'] ?? '') ?? date('Y-m-d');
        TSession::setValue(self::SESSION_BASE_DATE, $us);

        // recarrega a própria página com a nova base
        AdiantiCoreApplication::loadPage(__CLASS__);
    }

    private function normalizeUS(?string $date): ?string
    {
        if (!$date) return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return $date; // já no padrão
        $d = DateTime::createFromFormat('d/m/Y', $date);
        return $d ? $d->format('Y-m-d') : null;
    }

    private function formatBR(string $us): string
    {
        $d = DateTime::createFromFormat('Y-m-d', $us);
        return $d ? $d->format('d/m/Y') : '';
    }

    /**
     * Retorna ['ini'=> base-{$days}, 'fim'=> base]
     * Sempre inclusive no fim (<= base)
     */
    private function range(string $base, int $days): array
    {
        return [
            'ini' => date('Y-m-d', strtotime($base . " -{$days} days")),
            'fim' => $base
        ];
    }
}
