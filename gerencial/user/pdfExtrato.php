<?php
ob_start();
error_reporting(0);

require_once '../base/connection.php';
require_once '../base/baseFuncoes.php';
require_once '../base/fpdf/fpdf.php';

$idEmpresa = $_SESSION['idEmpresa'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    exit;
}

/* ===========================
   FILTROS
=========================== */

$cliente     = $_GET['cliente'] ?? null;
$dataInicial = $_GET['dataInicial'] ?? date('Y-m-d');
$dataFinal   = $_GET['dataFinal'] ?? date('Y-m-d');

$where  = " WHERE 1=1 ";
$params = [];

$statusFiltro  = $_GET['status'] ?? '';

if (!empty($cliente)) {
    $where .= " AND m.Forcli = ? ";
    $params[] = $cliente;
}

$where .= " AND m.Data BETWEEN ? AND ? ";
$params[] = $dataInicial;
$params[] = $dataFinal;

if ($statusFiltro !== '' && $statusFiltro !== null) {
    if ($statusFiltro ==  0){
        $where .= " AND m.Status in ( 0, 3) ";
    }else{
        $where .= " AND m.Status = '$statusFiltro'";    
    }
}

/* ===========================
   CONSULTA
=========================== */

$lista = ExSqlNET("
    SELECT 
        m.id,
        m.Data,
        f.Nome,
        movimentocc.Descricao,
        (
            SELECT SUM(TotalItem)
            FROM movimentoitem
            WHERE ControleMovimento = m.id
        ) AS ValorTotal
    FROM movimento m
    LEFT JOIN movimentocc ON movimentocc.ControleOrigem = m.id
    LEFT JOIN forcli f ON f.id = m.Forcli
    $where
    ORDER BY m.Data ASC
", null, $params);

/* ===========================
   PDF CUSTOM
=========================== */

class PDF extends FPDF
{
    function Header()
    {
        // // Barra superior laranja
        // $this->SetFillColor(243,122,32);
        // $this->Rect(0,0,210,25,'F');

        // $this->SetTextColor(255,255,255);
        // $this->SetFont('Arial','B',16);
        // $this->SetY(8);
        // $this->Cell(0,10,'EXTRATO FINANCEIRO',0,1,'C');

        // $this->Ln(12);
        // $this->SetTextColor(0,0,0);
        
        // Barra superior laranja
        $this->SetFillColor(243,122,32);
        $this->Rect(0,0,210,25,'F');

        $this->SetTextColor(255,255,255);

        // Nome da empresa
        $this->SetFont('Arial','B',14);
        $this->SetY(6);
        $this->Cell(0,6,mb_convert_encoding($this->empresa['Nome'],'ISO-8859-1','UTF-8'),0,1,'C');

        // Documento + Email
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,'Documento: '.$this->empresa['Documento'],0,1,'C');
        $this->Cell(0,5,'Email: '.$this->empresa['Email'],0,1,'C');

        $this->Ln(5);

        // Título
        $this->SetFont('Arial','B',14);
        $this->Cell(0,8,'EXTRATO FINANCEIRO',0,1,'C');

        $this->Ln(6);
        $this->SetTextColor(0,0,0);
        
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(120);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$dadosEmpresa = ExSqlNET(
    "SELECT Nome, Documento, Email FROM empresa WHERE id = ? ",
    null,
    [$idEmpresa]
);

$dadosEmpresa = $dadosEmpresa[0] ?? null;

$pdf = new PDF();

$pdf->empresa = $dadosEmpresa; 

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

/* ===========================
   BLOCO INFORMAÇÕES
=========================== */

$pdf->SetFillColor(240,240,240);
$pdf->SetFont('Arial','',10);

// $pdf->Cell(0,8,'Empresa: '.($_SESSION['nomeEmpresa'] ?? 'Minha Empresa'),0,1,'L',true);

if (!empty($cliente) && !empty($lista)) {
    $pdf->Cell(
        0,
        8,
        'Cliente: '.mb_convert_encoding($lista[0]['Nome'],'ISO-8859-1','UTF-8'),
        0,
        1,
        'L',
        true
    );
}

$pdf->Cell(
    0,
    8,
    'Periodo: '.date('d/m/Y',strtotime($dataInicial)).' ate '.date('d/m/Y',strtotime($dataFinal)),
    0,
    1,
    'L',
    true
);

$pdf->Cell(0,8,'Emitido em: '.date('d/m/Y H:i'),0,1,'L',true);

$pdf->Ln(8);

/* ===========================
   CABEÇALHO TABELA
=========================== */

$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(243,122,32);
$pdf->SetTextColor(255,255,255);

$pdf->Cell(30,8,'Data',1,0,'C',true);
//$pdf->Cell(40,8,'Movimento',1,0,'C',true);
$pdf->Cell(80,8,'Descricao',1,0,'C',true);
$pdf->Cell(40,8,'Valor (R$)',1,1,'C',true);

$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);

/* ===========================
   DADOS
=========================== */

$totalGeral = 0;
$fill = false;

foreach ($lista as $item) {

    $valor = $item['ValorTotal'] ?? 0;
    $totalGeral += $valor;

    $pdf->SetFillColor(248,248,248);

    $pdf->Cell(
        30,
        7,
        date('d/m/Y',strtotime($item['Data'])),
        1,
        0,
        'C',
        $fill
    );

    // $pdf->Cell(
    //     40,
    //     7,
    //     $item['id'],
    //     1,
    //     0,
    //     'C',
    //     $fill
    // );

    $descricao = mb_convert_encoding($item['Descricao'] ?? '', 'ISO-8859-1','UTF-8');

    $pdf->Cell(
        80,
        7,
        substr($descricao,0,45),
        1,
        0,
        'L',
        $fill
    );

    $pdf->Cell(
        40,
        7,
        number_format($valor,2,',','.'),
        1,
        1,
        'R',
        $fill
    );

    $fill = !$fill;
}

/* ===========================
   TOTAL
=========================== */

$pdf->Ln(6);

$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(243,122,32);

$pdf->Cell(150,10,'Total Geral:',0,0,'R');
$pdf->Cell(40,10,'R$ '.number_format($totalGeral,2,',','.'),0,1,'R');

$pdf->SetTextColor(0,0,0);

/* ===========================
   SAÍDA
=========================== */

ob_end_clean();
$pdf->Output('I','ExtratoFinanceiro.pdf');
exit;