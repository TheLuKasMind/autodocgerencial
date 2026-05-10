<?php
ob_start();

include '../base/baseFuncoes.php';
require_once '../base/connection.php';
require('../base/fpdf/fpdf.php');
require_once '../base/verificaPlano.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'];

function pdf($texto){
    return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
}

/* ================= FILTROS ================= */

$statusFiltro   = $_GET['status'] ?? '';
$clienteFiltro  = $_GET['cliente'] ?? '';
$serieFiltro    = $_GET['serie'] ?? '';
$placaFiltro    = $_GET['placa'] ?? '';
$orgaoFiltro    = $_GET['orgao'] ?? '';
$processoFiltro = $_GET['processo'] ?? '';
$autoFiltro     = $_GET['auto'] ?? '';
$dataInicial    = $_GET['dataInicial'] ?? '';
$dataFinal      = $_GET['dataFinal'] ?? '';

$where = " WHERE m.idEmpresa = $idEmpresa ";

/* STATUS */
if ($statusFiltro !== '') {
    $where .= " AND m.StatusMulta = '$statusFiltro' ";
}

/* CLIENTE */
if ($clienteFiltro != '') {
    $where .= " AND m.Forcli = '$clienteFiltro' ";
}

/* SÉRIE */
if ($serieFiltro != '') {
    $where .= " AND m.SerieMulta LIKE '%$serieFiltro%' ";
}

/* PLACA */
if ($placaFiltro != '') {
    $where .= " AND m.PlacaVeiculo LIKE '%$placaFiltro%' ";
}

/* ÓRGÃO */
if ($orgaoFiltro != '') {
    $where .= " AND m.OrgaoFiscalizador LIKE '%$orgaoFiltro%' ";
}

/* PROCESSO */
if ($processoFiltro != '') {
    $where .= " AND m.CodigoProcesso LIKE '%$processoFiltro%' ";
}

/* AUTO SUSPENSIVA */
if ($autoFiltro !== '') {
    $where .= " AND m.AutoSuspensiva = '$autoFiltro' ";
}

/* DATA */
if ($dataInicial != '') {
    $where .= " AND CAST(m.DataCadastro AS DATE) >= '$dataInicial' ";
}

if ($dataFinal != '') {
    $where .= " AND CAST(m.DataCadastro AS DATE) <= '$dataFinal' ";
}

/* ================= MULTAS ================= */

$listaMultas = ExSqlNET("
    SELECT 
        m.*,
        c.Nome AS ClienteNome,
        CASE m.StatusMulta
            WHEN 0 THEN 'Em Aberto'
            WHEN 1 THEN 'Defesa Enviada'
            WHEN 2 THEN 'Em Recurso'
            WHEN 3 THEN 'Deferida'
            WHEN 4 THEN 'Indeferida'
            WHEN 5 THEN 'Finalizada'
        ELSE 'Não Definido'
        END AS StatusLiteral
    FROM multa m
    LEFT JOIN forcli c ON c.Id = m.Forcli
    $where
    ORDER BY m.Id DESC
");

if (!$listaMultas || count($listaMultas) == 0) {
    die("Nenhuma multa encontrada.");
}

/* ================= EMPRESA ================= */

$dadosEmpresa = ExSqlNET(
    "SELECT Nome, Documento, Email FROM empresa WHERE id = ? ",
    null,
    [$idEmpresa]
);

$dadosEmpresa = $dadosEmpresa[0] ?? null;

/* ================= PDF ================= */

class PDF extends FPDF {

    public $empresa;

    function Header() {

        /* TOPO LARANJA */
        $this->SetFillColor(249,115,22);
        $this->Rect(0,0,210,28,'F');

        $this->SetTextColor(255,255,255);

        $this->SetFont('Arial','B',15);
        $this->SetY(6);
        $this->Cell(0,6,pdf($this->empresa['Nome']),0,1,'C');

        $this->SetFont('Arial','',9);
        $this->Cell(0,5,pdf('Documento: '.$this->empresa['Documento']),0,1,'C');
        $this->Cell(0,5,pdf('Email: '.$this->empresa['Email']),0,1,'C');

        $this->Ln(6);

        $this->SetTextColor(0,0,0);

        $this->SetFont('Arial','B',14);
        $this->Cell(0,8,pdf('RELATÓRIO DE MULTAS'),0,1,'C');

        $this->Ln(3);
    }

    function Footer() {

        $this->SetY(-12);

        $this->SetFont('Arial','I',8);
        $this->SetTextColor(120,120,120);

        $this->Cell(
            0,
            5,
            pdf('Página '.$this->PageNo().' | Gerado em '.date('d/m/Y H:i')),
            0,
            0,
            'C'
        );
    }
}

$pdf = new PDF();
$pdf->empresa = $dadosEmpresa;

$pdf->SetAutoPageBreak(true,15);
$pdf->AddPage();

$pdf->SetFont('Arial','',9);

/* ================= FILTROS ================= */

$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(249,115,22);
$pdf->Cell(0,6,pdf('Filtros Aplicados'),0,1);

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',9);

$dataInicialBR = $dataInicial ? date('d/m/Y', strtotime($dataInicial)) : '---';
$dataFinalBR   = $dataFinal ? date('d/m/Y', strtotime($dataFinal)) : '---';

$pdf->Cell(0,5,pdf("Período: {$dataInicialBR} até {$dataFinalBR}"),0,1);

if ($orgaoFiltro != '') {
    $pdf->Cell(0,5,pdf("Órgão: {$orgaoFiltro}"),0,1);
}

if ($placaFiltro != '') {
    $pdf->Cell(0,5,pdf("Placa: {$placaFiltro}"),0,1);
}

$pdf->Ln(4);

/* ================= TOTAIS ================= */

$totalMultas = count($listaMultas);
$totalAutoSuspensiva = 0;

foreach ($listaMultas as $multa) {
    if (($multa['AutoSuspensiva'] ?? 0) == 1) {
        $totalAutoSuspensiva++;
    }
}

/* ================= LOOP ================= */

foreach ($listaMultas as $multa) {

    /* LINHA SEPARADORA */
    $pdf->SetDrawColor(220,220,220);
    $pdf->Rect(10, $pdf->GetY(), 190, 0);

    $pdf->Ln(3);

    /* HEADER CARD */
    $pdf->SetFillColor(249,115,22);
    $pdf->SetTextColor(255,255,255);
    $pdf->SetFont('Arial','B',10);

    $titulo = 'Multa #'.$multa['id'].' - '.$multa['StatusLiteral'];

    $pdf->Cell(190,7,pdf($titulo),1,1,'L',true);

    /* DADOS */
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',9);

    $pdf->Cell(
        95,
        5,
        pdf('Cliente: '.$multa['ClienteNome']),
        0,
        0
    );

    $pdf->Cell(
        95,
        5,
        pdf('Data Cadastro: '.date('d/m/Y', strtotime($multa['DataCadastro']))),
        0,
        1
    );

    $pdf->Cell(
        95,
        5,
        pdf('Série: '.$multa['SerieMulta']),
        0,
        0
    );

    $pdf->Cell(
        95,
        5,
        pdf('Processo: '.$multa['CodigoProcesso']),
        0,
        1
    );

    $pdf->Cell(
        95,
        5,
        pdf('Placa: '.$multa['PlacaVeiculo']),
        0,
        0
    );

    $pdf->Cell(
        95,
        5,
        pdf('Órgão: '.$multa['OrgaoFiscalizador']),
        0,
        1
    );

    $prazo = !empty($multa['PrazoDefesa'])
        ? date('d/m/Y', strtotime($multa['PrazoDefesa']))
        : 'Não informado';

    $pdf->Cell(
        95,
        5,
        pdf('Prazo Defesa: '.$prazo),
        0,
        0
    );

    $pdf->Cell(
        95,
        5,
        pdf(
            'Auto Suspensiva: '.
            (($multa['AutoSuspensiva'] == 1) ? 'SIM' : 'NÃO')
        ),
        0,
        1
    );

    /* OBS */
    if (!empty($multa['Obs'])) {

        $pdf->Ln(1);

        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(0,5,pdf('Observações:'),0,1);

        $pdf->SetFont('Arial','',9);

        $pdf->MultiCell(
            190,
            5,
            pdf($multa['Obs']),
            1
        );
    }

    $pdf->Ln(6);
}

/* ================= RESUMO ================= */

$pdf->Ln(2);

$pdf->SetFont('Arial','B',12);

$pdf->SetFillColor(249,115,22);
$pdf->SetTextColor(255,255,255);

$pdf->Cell(130,10,pdf('TOTAL DE MULTAS'),1,0,'R',true);
$pdf->Cell(60,10,$totalMultas,1,1,'C',true);

$pdf->Cell(130,10,pdf('AUTO SUSPENSIVAS'),1,0,'R',true);
$pdf->Cell(60,10,$totalAutoSuspensiva,1,1,'C',true);

/* ================= OUTPUT ================= */

ob_end_clean();

$pdf->Output('I','RelatorioMultas.pdf');

exit;
?>