<?php
ob_start();

require_once '../base/connection.php';
require_once '../base/baseFuncoes.php';
require_once '../base/fpdf/fpdf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

date_default_timezone_set('America/Sao_Paulo');

$idEmpresa   = $_SESSION['idEmpresa'];
$dataInicial = $_GET['dataInicial'] ?? date('Y-m-d');
$dataFinal   = $_GET['dataFinal'] ?? date('Y-m-d');


// ================= BUSCAR RESUMO =================
$resumo = ExSqlNET("
    SELECT 
        SUM(CASE WHEN TipoMov = 'ENTRADA' THEN Valor ELSE 0 END) entradas,
        SUM(CASE WHEN TipoMov = 'SAIDA' THEN Valor ELSE 0 END) saidas
    FROM movimentocc
    WHERE idEmpresa = ?
    AND CaixaGeral = 0
    AND DATE(Data) BETWEEN ? AND ?
", null, [$idEmpresa, $dataInicial, $dataFinal]);

$entradas = $resumo[0]['entradas'] ?? 0;
$saidas   = $resumo[0]['saidas'] ?? 0;
$saldo    = $entradas - $saidas;


// ================= BUSCAR MOVIMENTOS =================
$lista = ExSqlNET("
    SELECT Data AS Data, Descricao, Valor, TipoMov
    FROM movimentocc
    WHERE idEmpresa = ?
    AND CaixaGeral = 0
    AND DATE(Data) BETWEEN ? AND ?
    ORDER BY Data DESC
", null, [$idEmpresa, $dataInicial, $dataFinal]);


// ================= PDF =================
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);


// ================= CABEÇALHO =================
$pdf->SetFillColor(245,124,0);
$pdf->Rect(0,0,210,25,'F');

$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',16);
$pdf->SetY(8);
$pdf->Cell(0,8,'RELATORIO DE CAIXA',0,1,'C');

$pdf->SetFont('Arial','',11);
$pdf->Cell(
    0,
    5,
    'Periodo: '.date('d/m/Y', strtotime($dataInicial)).' a '.date('d/m/Y', strtotime($dataFinal)),
    0,
    1,
    'C'
);

$pdf->Ln(15);


// Reset texto
$pdf->SetTextColor(0,0,0);


// ================= RESUMO =================
$pdf->SetFont('Arial','B',12);

// Títulos
$pdf->SetFillColor(220,252,231);
$pdf->SetDrawColor(34,197,94);
$pdf->Cell(63,15,'Entradas',1,0,'C',true);

$pdf->SetFillColor(254,226,226);
$pdf->SetDrawColor(220,38,38);
$pdf->Cell(63,15,'Saidas',1,0,'C',true);

$pdf->SetFillColor(224,242,254);
$pdf->SetDrawColor(14,165,233);
$pdf->Cell(63,15,'Saldo',1,1,'C',true);


// Valores
$pdf->SetFont('Arial','B',13);

$pdf->SetTextColor(22,163,74);
$pdf->Cell(63,12,'R$ '.number_format($entradas,2,',','.'),1,0,'C');

$pdf->SetTextColor(220,38,38);
$pdf->Cell(63,12,'R$ '.number_format($saidas,2,',','.'),1,0,'C');

$pdf->SetTextColor(14,165,233);
$pdf->Cell(63,12,'R$ '.number_format($saldo,2,',','.'),1,1,'C');

$pdf->Ln(15);


// ================= TABELA =================
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',11);
$pdf->SetFillColor(245,124,0);

$pdf->Cell(35,10,'Data',1,0,'C',true);
//$pdf->Cell(25,10,'Hora',1,0,'C',true);
$pdf->Cell(35,10,'Tipo',1,0,'C',true);
$pdf->Cell(85,10,'Descricao',1,0,'C',true);
$pdf->Cell(35,10,'Valor',1,1,'C',true);
//$pdf->Cell(45,10,'Valor',1,1,'C',true);


$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);

$fill = false;

foreach($lista as $l){

    $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);

    $hora = date('d/m/Y H:i', strtotime($l['Data']));
    //$hora  = date('H:i', strtotime($l['Data']));
    $valor = number_format($l['Valor'],2,',','.');

    $pdf->Cell(35,8,$hora,1,0,'C',true);
    //$pdf->Cell(25,8,$hora,1,0,'C',true);
    $pdf->Cell(35,8,$l['TipoMov'],1,0,'C',true);

    $pdf->Cell(
        85,
        8,
        mb_convert_encoding($l['Descricao'], 'ISO-8859-1', 'UTF-8'),
        1,
        0,
        'L',
        true
    );
    
    if($l['TipoMov'] == 'ENTRADA'){
        $pdf->SetTextColor(22,163,74);
        $pdf->Cell(35,8,'+ R$ '.$valor,1,1,'R',true);
    } else {
        $pdf->SetTextColor(220,38,38);
        $pdf->Cell(35,8,'- R$ '.$valor,1,1,'R',true);
    }

    $pdf->SetTextColor(0,0,0);
    $fill = !$fill;
}


// ================= RODAPÉ =================
$pdf->Ln(8);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(
    0,
    10,
    'Saldo Final do Periodo: R$ '.number_format($saldo,2,',','.'),
    0,
    1,
    'R'
);


// ================= SAÍDA =================
ob_end_clean();
$pdf->Output('I','Relatorio_Caixa.pdf');
exit;