<?php
require_once '../base/connection.php';
require_once '../base/baseFuncoes.php';
require_once '../base/fpdf/fpdf.php';

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    exit;
}

/* ================= FUNÇÕES ================= */

function dataBR($data) {
    return date('d/m/Y', strtotime($data));
}

function texto($str) {
    return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
}

/* ================= VARIÁVEIS ================= */

$mostrarValores = $_POST['mostrarValores'] ?? 0;
$idEmpresa = $_SESSION['idEmpresa'];

$dataInicial   = $_POST['dataInicial'] ?? date('Y-m-01');
$dataFinal     = $_POST['dataFinal'] ?? date('Y-m-d');
$tipoRelatorio = $_POST['tipo'] ?? 'faturamento';

$graficoBase64 = $_POST['graficoBase64'] ?? '';

$mesSelecionado = $_POST['mes'] ?? date('m');
$anoSelecionado = date('Y');

$produtoId = $_POST['produtoId'] ?? 0;

$grupoId = $_POST['grupoId'] ?? 0; //Grupo de Produto

$grupoIdForcli = $_POST['grupoIdForcli'] ?? 0; //Grupo de Clientes


$dadosEmpresa = ExSqlNET(
    "SELECT Nome, Documento, Email FROM empresa WHERE id = ? ",
    null,
    [$idEmpresa]
);

$dadosEmpresa = $dadosEmpresa[0] ?? null;

/* ================= PDF PADRÃO ================= */

class PDF extends FPDF {
    public $empresa = [];

    function Header() {
        
        // Barra laranja topo
        $this->SetFillColor(249,115,22);
        $this->Rect(0,0,210,25,'F');

        $this->SetTextColor(255,255,255);

        // Nome da empresa
        $this->SetFont('Arial','B',14);
        $this->SetY(6);
        $this->Cell(0,6,texto($this->empresa['Nome'] ?? ''),0,1,'C');

        // Documento + Email
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,texto('Documento: '.($this->empresa['Documento'] ?? '')),0,1,'C');
        $this->Cell(0,5,texto('Email: '.($this->empresa['Email'] ?? '')),0,1,'C');

        $this->Ln(3);

        // Título do relatório
        $this->SetFont('Arial','B',13);
        $this->Cell(0,7,texto('RELATÓRIO - '.strtoupper($_POST['tipo'] ?? 'FATURAMENTO')),0,1,'C');

        $this->Ln(6);
        $this->SetTextColor(0,0,0);
        
    }

    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(120,120,120);
        $this->Cell(0,5,texto('Página '.$this->PageNo().' | Gerado em '.date('d/m/Y H:i')),0,0,'C');
        $this->SetTextColor(0,0,0);
    }
}

$pdf = new PDF();
$pdf->empresa = $dadosEmpresa;

$pdf->SetAutoPageBreak(true,15);
$pdf->AddPage();

/* ================= PERÍODO ================= */

$pdf->SetFont('Arial','',10);

if ($tipoRelatorio == 'metas') {
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
    $periodo = strftime('%B', mktime(0,0,0,$mesSelecionado,1)).' de '.$anoSelecionado;
} else {
    $periodo = dataBR($dataInicial).' até '.dataBR($dataFinal);
}

$pdf->Cell(0,8,texto('Período: '.$periodo),0,1,'C');
$pdf->Ln(5);


/* ================= FATURAMENTO ================= */
if ($tipoRelatorio == 'faturamento') {

    $whereFiltro = "";
    $params = [$idEmpresa, $dataInicial, $dataFinal];
    
    if ($grupoId > 0) {
        $whereFiltro = " AND sp.Grupo = ? ";
        $params[] = $grupoId;
    }
    
    $dados = ExSqlNET("
        SELECT SUM(mi.TotalItem) total
        FROM movimento m
        LEFT JOIN movimentoitem mi 
            ON mi.ControleMovimento = m.id
    
        LEFT JOIN servprod sp 
            ON sp.id = mi.ServProd
    
        WHERE m.idEmpresa = ?
        AND DATE(m.Data) BETWEEN ? AND ?
        $whereFiltro
        ORDER BY sp.Nome ASC
    ", null, $params);

    $total = $dados[0]['total'] ?? 0;

    $pdf->SetFont('Arial','B',12);
    $pdf->SetFillColor(240,240,240);
    $pdf->Cell(0,12,texto('Total Faturamento: R$ '.number_format($total,2,',','.')),1,1,'C',true);
}


/* ================= METAS ================= */
if ($tipoRelatorio == 'metas') {

    $whereFiltro = "";
    $paramsMetas = [$idEmpresa, $idEmpresa, $mesSelecionado, $anoSelecionado, $idEmpresa];
    
    if ($produtoId > 0) {
    
        $whereFiltro = " AND sp.id = ? ";
        $paramsMetas[] = $produtoId;
    
    } elseif ($grupoId > 0) {
    
        $whereFiltro = " AND sp.Grupo = ? ";
        $paramsMetas[] = $grupoId;
    }
    
    $metas = ExSqlNET("
        SELECT 
            sp.id, 
            sp.Nome, 
            sp.MetaMensal,
            IFNULL(SUM(
                CASE 
                    WHEN m.id IS NOT NULL THEN mi.Qtd 
                    ELSE 0 
                END
            ),0) vendido,
            IFNULL(SUM(
                CASE 
                    WHEN m.id IS NOT NULL THEN mi.TotalItem 
                    ELSE 0 
                END
            ),0) Faturamento
        FROM servprod sp
        LEFT JOIN movimentoitem mi 
            ON mi.ServProd = sp.id
            AND mi.idEmpresa = ?
        LEFT JOIN movimento m 
            ON m.id = mi.ControleMovimento
            AND m.idEmpresa = ?
            AND MONTH(m.Data) = ?
            AND YEAR(m.Data) = ?
        WHERE
            sp.idEmpresa = ?
            $whereFiltro
        GROUP BY sp.id, sp.Nome, sp.MetaMensal
        ORDER BY sp.Nome ASC
    ", null, $paramsMetas);

    $pdf->SetFont('Arial','B',9);
    $pdf->SetFillColor(249,115,22);
    $pdf->SetTextColor(255,255,255);
    
    $pdf->Cell(80,8,texto('Produto'),1,0,'L',true);
    $pdf->Cell(15,8,texto('Vendido'),1,0,'C',true);
    $pdf->Cell(15,8,texto('Meta'),1,0,'C',true);
    $pdf->Cell(15,8,texto('Falta'),1,0,'C',true);
    $pdf->Cell(15,8,texto('% Meta'),1,0,'C',true);
    
    if ($mostrarValores) {
        $pdf->Cell(30,8,texto('Faturamento'),1,0,'R',true);
    }


    $pdf->Ln();
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',8);

    foreach ($metas as $m) {

        $meta = $m['MetaMensal'] ?? 0;
        $vendido = $m['vendido'] ?? 0;
        $fat = $m['Faturamento'] ?? 0;
        $perc = $meta > 0 ? ($vendido / $meta) * 100 : 0;
        
        $pdf->Cell(80,8,texto($m['Nome']),1);
        $pdf->Cell(15,8,$vendido,1,0,'C');
        $pdf->Cell(15,8,$meta,1,0,'C');
        $pdf->Cell(15,8,max(0,$meta-$vendido),1,0,'C');
        $pdf->Cell(15,8,number_format($perc,1).'%',1,0,'C');
        
        if ($mostrarValores) {
            $pdf->Cell(30,8,'R$ '.number_format($fat,2,',','.'),1,0,'R');
        }


        $pdf->Ln();
    }
}


    /* ================= LUCRO ================= */
    if ($tipoRelatorio == 'lucro') {
    
    $whereFiltro = "";
    $paramsLucro = [$idEmpresa, $idEmpresa, $dataInicial, $dataFinal];
    
    if ($produtoId > 0) {
    
        $whereFiltro = " AND sp.id = ? ";
        $paramsLucro[] = $produtoId;
    
    } elseif ($grupoId > 0) {
    
        $whereFiltro = " AND sp.Grupo = ? ";
        $paramsLucro[] = $grupoId;
    }
    
    $lucroProdutos = ExSqlNET("
        SELECT 
            sp.Nome,
            SUM(mi.Qtd) AS quantidade,
            SUM(mi.TotalItem) AS faturamento,
            SUM(IFNULL(mi.ValorCusto,0) * mi.Qtd) AS custoTotal,
            SUM(mi.TotalItem - (IFNULL(mi.ValorCusto,0) * mi.Qtd)) AS lucro
        FROM movimentoitem mi
        LEFT JOIN movimento m 
            ON m.id = mi.ControleMovimento
        LEFT JOIN servprod sp 
            ON sp.id = mi.ServProd
        WHERE m.idEmpresa = ?
        AND sp.idEmpresa = ?
        AND DATE(m.Data) BETWEEN ? AND ?
        $whereFiltro
        GROUP BY sp.id
        ORDER BY sp.Nome ASC
    ", null, $paramsLucro);
    
    foreach ($lucroProdutos as $l) {
        $totalFaturamento += $l['faturamento'] ?? 0;
        $totalCusto += $l['custoTotal'] ?? 0;
        $totalLucro += $l['lucro'] ?? 0;
    }
    
    $pdf->SetFont('Arial','B',8);
    $pdf->SetFillColor(249,115,22);
    $pdf->SetTextColor(255,255,255);
    
    // AUMENTEI PRODUTO
    $pdf->Cell(70,8,texto('Produto'),1,0,'L',true);
    $pdf->Cell(15,8,texto('Qtd'),1,0,'C',true);
    
    if ($mostrarValores) {
        $pdf->Cell(20,8,texto('Faturamento'),1,0,'R',true);
        $pdf->Cell(20,8,texto('Custo'),1,0,'R',true);
        $pdf->Cell(20,8,texto('Lucro'),1,0,'R',true);
        $pdf->Cell(15,8,texto('% Margem'),1,0,'C',true);
        $pdf->Cell(20,8,texto('Ticket'),1,0,'R',true);
    }
    
    $pdf->Ln();
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',7);
    
    foreach ($lucroProdutos as $l) {
    
        $fat = $l['faturamento'] ?? 0;
        $custo = $l['custoTotal'] ?? 0;
        $lucro = $l['lucro'] ?? 0;
        $qtd = $l['quantidade'] ?? 0;
    
        $margem = $fat > 0 ? ($lucro / $fat) * 100 : 0;
        $ticket = $qtd > 0 ? $fat / $qtd : 0;
    
        $yAntes = $pdf->GetY();
        $xAntes = $pdf->GetX();
    
        $pdf->MultiCell(70,6,texto($l['Nome']),1);
    
        $altura = $pdf->GetY() - $yAntes;
    
        $pdf->SetXY($xAntes + 70, $yAntes);
    
        $pdf->Cell(15,$altura,$qtd,1,0,'C');
    
        if ($mostrarValores) {
            $pdf->Cell(20,$altura,'R$ '.number_format($fat,2,',','.'),1,0,'R');
            $pdf->Cell(20,$altura,'R$ '.number_format($custo,2,',','.'),1,0,'R');
            $pdf->Cell(20,$altura,'R$ '.number_format($lucro,2,',','.'),1,0,'R');
            $pdf->Cell(15,$altura,number_format($margem,1).'%',1,0,'C');
            $pdf->Cell(20,$altura,'R$ '.number_format($ticket,2,',','.'),1,0,'R');
        }
    
        $pdf->Ln();
    }
    
    $pdf->Ln(5);
    
    $pdf->SetFont('Arial','B',11);
    $pdf->SetFillColor(240,240,240);
    
    $pdf->Cell(0,10,texto('RESUMO GERAL'),0,1,'L');
    
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,10,'Faturamento Total: R$ '.number_format($totalFaturamento,2,',','.'),1,1,'L',true);
    $pdf->Cell(0,10,'Custo Total: R$ '.number_format($totalCusto,2,',','.'),1,1,'L',true);
    $pdf->Cell(0,10,'Lucro Total: R$ '.number_format($totalLucro,2,',','.'),1,1,'L',true);
    
    $pdf->Ln(5);

}

/* ================= DESPESAS ================= */
if ($tipoRelatorio == 'despesas') {

    $paramsDespesas = [
        $idEmpresa,
        $dataInicial,
        $dataFinal,
        $idEmpresa
    ];

    $despesas = ExSqlNET("
        SELECT 
            td.id,
            td.Nome,
            td.Descricao,

            IFNULL(SUM(
                CASE 
                    WHEN mc.TipoMov = 'SAIDA' THEN mc.Valor 
                    ELSE 0 
                END
            ),0) AS totalGasto,

            COUNT(
                CASE 
                    WHEN mc.TipoMov = 'SAIDA' THEN mc.Controle
                    ELSE NULL 
                END
            ) AS qtdLancamentos

        FROM tipodespesa td

        LEFT JOIN movimentocc mc 
            ON mc.tipoDespesa = td.id
            AND mc.idEmpresa = ?
            AND DATE(mc.Data) BETWEEN ? AND ?

        WHERE 
            td.idEmpresa = ?
            AND td.Inativo = 0

        GROUP BY td.id
        ORDER BY td.Nome ASC
    ", null, $paramsDespesas);

    // ===== TOTAL GERAL =====
    $totalDespesas = 0;

    foreach ($despesas as $d) {
        $totalDespesas += $d['totalGasto'] ?? 0;
    }

    // ===== CABEÇALHO =====
    $pdf->SetFont('Arial','B',9);
    $pdf->SetFillColor(249,115,22);
    $pdf->SetTextColor(255,255,255);

    $pdf->Cell(50,8,texto('Categoria'),1,0,'L',true);
    $pdf->Cell(25,8,texto('Lançamentos'),1,0,'C',true);
    $pdf->Cell(35,8,texto('Total Gasto'),1,0,'R',true);
    $pdf->Cell(30,8,texto('Média'),1,0,'R',true);
    $pdf->Cell(20,8,texto('% Total'),1,0,'C',true);

    $pdf->Ln();
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',8);

    // ===== DADOS =====
    foreach ($despesas as $d) {

        $total = $d['totalGasto'] ?? 0;
        $qtd = $d['qtdLancamentos'] ?? 0;

        $media = $qtd > 0 ? $total / $qtd : 0;

        $perc = $totalDespesas > 0 
            ? ($total / $totalDespesas) * 100 
            : 0;

        $pdf->Cell(50,8,texto($d['Nome']),1);
        $pdf->Cell(25,8,$qtd,1,0,'C');
        $pdf->Cell(35,8,'R$ '.number_format($total,2,',','.'),1,0,'R');
        $pdf->Cell(30,8,'R$ '.number_format($media,2,',','.'),1,0,'R');
        $pdf->Cell(20,8,number_format($perc,1,',','.').'%',1,0,'C');

        $pdf->Ln();
    }

    // ===== TOTAL FINAL =====
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',11);
    $pdf->SetFillColor(240,240,240);

    $pdf->Cell(
        0,
        12,
        texto('TOTAL DE DESPESAS: R$ '.number_format($totalDespesas,2,',','.')),
        1,
        1,
        'C',
        true
    );
}


/* ================= GRÁFICO ================= */

if ($graficoBase64) {
    $graficoBase64 = str_replace('data:image/png;base64,', '', $graficoBase64);
    $imagem = base64_decode($graficoBase64);

    file_put_contents('grafico.png', $imagem);
    $pdf->Ln(5);
      
    $pdf->Image('grafico.png', 10, $pdf->GetY(), 180);
}

/* ================= SERVIÇOS POR CLIENTE ================= */
if ($tipoRelatorio == 'servicosForcli') {

    $whereServicosForcli = "";
    $paramsServicosForcli = [
        $idEmpresa,
        $idEmpresa,
        $dataInicial,
        $dataFinal,
    ];

    // FILTRO POR GRUPO DE CLIENTES
    if ($grupoIdForcli > 0) {
        $whereServicosForcli .= " AND f.Grupo = ? ";
        $paramsServicosForcli[] = $grupoIdForcli;
    }

    $servicosForcli = ExSqlNET("
        SELECT 
            f.Nome,
            SUM(mi.Qtd) AS TotalServicos,
            SUM(mi.TotalItem) AS Faturamento,
            SUM(mc.Valor) AS Lucro
        FROM movimento m
        LEFT JOIN movimentoitem mi 
            ON mi.ControleMovimento = m.id
        LEFT JOIN movimentocc mc 
            ON mc.ControleOrigem = m.id
        LEFT JOIN forcli f 
            ON f.id = m.Forcli
        WHERE m.idEmpresa = ?
            AND mi.idEmpresa = ?
            AND DATE(m.Data) BETWEEN ? AND ?
            $whereServicosForcli
        GROUP BY f.Nome
        ORDER BY TotalServicos DESC
    ", null, $paramsServicosForcli);

    $totalFaturamento = 0;
    $totalLucro = 0;
    $totalServicos = 0;

    // ===== CABEÇALHO =====
    $pdf->SetFont('Arial','B',8);
    $pdf->SetFillColor(249,115,22);
    $pdf->SetTextColor(255,255,255);

    $pdf->Cell(70,8,texto('Cliente'),1,0,'L',true);
    $pdf->Cell(30,8,texto('Qtd Serviços'),1,0,'C',true);

    if ($mostrarValores) {
        $pdf->Cell(40,8,texto('Faturamento'),1,0,'R',true);
        $pdf->Cell(40,8,texto('Lucro'),1,0,'R',true);
    }

    $pdf->Ln();
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',8);

    // ===== DADOS =====
    foreach ($servicosForcli as $s) {

        $cliente = $s['Nome'] ?? '';
        $qtd = $s['TotalServicos'] ?? 0;
        $fat = $s['Faturamento'] ?? 0;
        $lucro = $s['Lucro'] ?? 0;

        $totalServicos += $qtd;
        $totalFaturamento += $fat;
        $totalLucro += $lucro;

        $yAntes = $pdf->GetY();
        $xAntes = $pdf->GetX();

        $pdf->MultiCell(70,6,texto($cliente),1);

        $altura = $pdf->GetY() - $yAntes;

        $pdf->SetXY($xAntes + 70, $yAntes);

        $pdf->Cell(30,$altura,number_format($qtd,0,',','.'),1,0,'C');

        if ($mostrarValores) {
            $pdf->Cell(40,$altura,'R$ '.number_format($fat,2,',','.'),1,0,'R');
            $pdf->Cell(40,$altura,'R$ '.number_format($lucro,2,',','.'),1,0,'R');
        }

        $pdf->Ln();
    }

    // ===== RESUMO =====
    $pdf->Ln(5);

    $pdf->SetFont('Arial','B',11);
    $pdf->SetFillColor(240,240,240);

    $pdf->Cell(0,10,texto('RESUMO GERAL'),0,1,'L');

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(
        0,
        10,
        texto('Total Serviços / Produtos: '.number_format($totalServicos,0,',','.')),
        1,
        1,
        'L',
        true
    );

    if ($mostrarValores) {
        $pdf->Cell(
            0,
            10,
            texto('Faturamento Total: R$ '.number_format($totalFaturamento,2,',','.')),
            1,
            1,
            'L',
            true
        );

        $pdf->Cell(
            0,
            10,
            texto('Lucro Total: R$ '.number_format($totalLucro,2,',','.')),
            1,
            1,
            'L',
            true
        );
    }
}


ob_end_clean();
$pdf->Output('I');
exit;
?>