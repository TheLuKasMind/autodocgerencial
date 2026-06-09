<?php
ob_start();

require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';
require  __DIR__ .'/../base/fpdf/fpdf.php';

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

$statusFiltro  = $_GET['status'] ?? '';
$clienteFiltro = $_GET['cliente'] ?? '';
$repasseFiltro = $_GET['repasse'] ?? '';
$dataInicial   = $_GET['dataInicial'] ?? '';
$dataFinal     = $_GET['dataFinal'] ?? '';
$condicaoFiltro = $_GET['CondPgto'] ?? '';
$placaFiltro = $_GET['placa'] ?? '';
$statusProcessoFiltro  = $_GET['statusProcesso'] ?? '';

$where = "WHERE p.idEmpresa = ". $idEmpresa;

if ($statusFiltro !== '' && $statusFiltro !== null) {
    if ($statusFiltro ==  0){
        $where .= " AND p.Status in (0,3)";
    }else{
        $where .= " AND p.Status = '$statusFiltro'";
    }
}

if ($clienteFiltro != '') {
    // $where .= " AND p.Forcli = '$clienteFiltro'";
    if ($clienteFiltro > 0 ){
        $where .= " AND ( p.Forcli = '$clienteFiltro' OR p.ForcliRepasse = '$clienteFiltro' )";
    }
}

if ($dataInicial != '') {
    $where .= " AND CAST(p.Data AS DATE) >= '$dataInicial'";
}

if ($dataFinal != '') {
    $where .= " AND CAST(p.Data AS DATE) <= '$dataFinal'";
}

if ($condicaoFiltro != '') {
    $where .= " AND p.CondPgto = '$condicaoFiltro'";
}

if ($repasseFiltro !== '') {
    if ($repasseFiltro > 0){
        if ($repasseFiltro == 0) {
            $where .= " AND (p.ForcliRepasse = 0 OR p.ForcliRepasse IS NULL)";
        } else {
            $where .= " AND p.ForcliRepasse = '$repasseFiltro'";
        }
    }
}

$placaFiltro = trim($_GET['placa'] ?? '');

if ($placaFiltro !== '') {
    $placaFiltro = addslashes($placaFiltro);
    $where .= " AND p.PlacaVeiculo = '$placaFiltro'";
}

if ($statusProcessoFiltro  !== '' && $statusProcessoFiltro  !== null && $statusProcessoFiltro  !== '0') {
    $where .= " AND p.StatusProcesso = '$statusProcessoFiltro '";    
}

$pedidoFiltro = $_GET['pedido'] ?? '';
if (!empty($pedidoFiltro)) {
    $pedidos = explode(',', $pedidoFiltro);
    $pedidos = array_filter(array_map('intval', $pedidos));
    if (!empty($pedidos)) {
        $lista = implode(',', $pedidos);
        $where .= " AND p.id IN ($lista)";
    }
}

/* ================= PEDIDOS ================= */

$listaPedidos = ExSqlNET("
SELECT 
    p.*,
    c.Nome AS ClienteNome,
    COALESCE(r.Nome,'') AS ClienteRepasseNome,
    CASE p.Status
        WHEN 1 THEN 'Pago'
        WHEN 2 THEN 'Orçamento'
        WHEN 0 THEN 'Em Aberto'
        WHEN 3 THEN 'Débito'
        WHEN 4 THEN 'Débito Pago'
    END AS StatusLiteral,
    CONCAT(p.ModeloVeiculo,' : ',p.CorVeiculo,' - ', p.PlacaVeiculo) As Veiculo,
    COALESCE(
        (SELECT SUM(TotalItem) 
        FROM movimentoitem 
        WHERE ControleMovimento = p.id),
        0
    ) AS Valor,
    CASE p.CondPgto 
        WHEN 1 THEN 'À Vista / Dinheiro'
        WHEN 2 THEN 'Pix'
        WHEN 3 THEN 'Cartão de Crédito'
        WHEN 4 THEN 'Cartão de Débito'
        WHEN 5 THEN 'Cheque'
        WHEN 6 THEN '30 Dias'
        WHEN 7 THEN '60 Dias'
    END AS CondPgtoLiteral
FROM movimento p
LEFT JOIN forcli c ON c.Id = p.Forcli
LEFT JOIN forcli r ON r.Id = p.ForcliRepasse
$where
ORDER BY p.id DESC
");

if (!$listaPedidos || count($listaPedidos) == 0) {
    die("Nenhum pedido encontrado.");
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
        $this->SetFillColor(249,115,22);
        $this->Rect(0,0,210,25,'F');

        $this->SetTextColor(255,255,255);
        $this->SetFont('Arial','B',14);
        $this->SetY(6);
        $this->Cell(0,6,pdf($this->empresa['Nome']),0,1,'C');

        $this->SetFont('Arial','',9);
        $this->Cell(0,5,pdf('Documento: '.$this->empresa['Documento']),0,1,'C');
        $this->Cell(0,5,pdf('Email: '.$this->empresa['Email']),0,1,'C');

        $this->Ln(5);

        $this->SetFont('Arial','B',14);
        $this->Cell(0,6,pdf('RELATÓRIO DE PEDIDOS'),0,1,'C');

        $this->Ln(6);
        $this->SetTextColor(0,0,0);
    }

    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(120,120,120);
        $this->Cell(0,5,pdf('Página '.$this->PageNo().' | Gerado em '.date('d/m/Y H:i')),0,0,'C');
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

$pdf->Ln(5);

$totalGeral = 0;

/* ================= LOOP ================= */

foreach ($listaPedidos as $pedido) {

    $totalGeral += $pedido['Valor'];

    /* ===== CARD DO PEDIDO ===== */
    $pdf->SetDrawColor(200,200,200);
    $pdf->Rect(10, $pdf->GetY(), 190, 0); // linha separadora leve

    $pdf->Ln(3);

    /* HEADER */
    $pdf->SetFillColor(249,115,22);
    $pdf->SetTextColor(255,255,255);
    $pdf->SetFont('Arial','B',10);

    $pdf->Cell(120,7,pdf("Pedido #".$pedido['id']." - ".$pedido['StatusLiteral']),1,0,'L',true);
    $pdf->Cell(70,7,pdf("R$ ".number_format($pedido['Valor'],2,',','.')),1,1,'R',true);

    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('Arial','',9);

    /* INFO EM 2 COLUNAS */
    $pdf->Cell(95,5,pdf("Cliente: ".$pedido['ClienteNome']),0,0);
    $pdf->Cell(95,5,pdf("Data: ".date('d/m/Y', strtotime($pedido['Data']))),0,1);

    $pdf->Cell(95,5,pdf("Pagamento: ".$pedido['CondPgtoLiteral']),0,0);
    $pdf->Cell(95,5,pdf("Pagador: ".$pedido['ClienteRepasseNome']),0,1);

    $pdf->Cell(0,5,pdf("Veículo: ".$pedido['Veiculo']),0,1);

    $pdf->Ln(2);

    /* ===== ITENS ===== */

    $itens = ExSqlNET("
        SELECT mi.*, sp.Nome
        FROM movimentoitem mi
        LEFT JOIN servprod sp ON sp.Id = mi.ServProd
        WHERE mi.ControleMovimento = ".$pedido['id']
    );

    $pdf->SetFillColor(240,240,240);
    $pdf->SetFont('Arial','B',9);

    $pdf->Cell(90,6,pdf('Produto'),1,0,'L',true);
    $pdf->Cell(20,6,pdf('Qtd'),1,0,'C',true);
    $pdf->Cell(30,6,pdf('Valor'),1,0,'R',true);
    $pdf->Cell(30,6,pdf('Total'),1,1,'R',true);

    $pdf->SetFont('Arial','',9);

    if ($itens) {
        foreach ($itens as $item) {

            $pdf->Cell(90,6,pdf($item['Nome']),1);
            $pdf->Cell(20,6,$item['Qtd'],1,0,'C');
            $pdf->Cell(30,6,'R$ '.number_format($item['Valor'],2,',','.'),1,0,'R');
            $pdf->Cell(30,6,'R$ '.number_format($item['TotalItem'],2,',','.'),1,1,'R');
        }
    }

    /* TOTAL */
    $pdf->SetFont('Arial','B',9);
    $pdf->SetFillColor(230,230,230);

    $pdf->Cell(140,7,pdf('Total Pedido'),1,0,'R',true);
    $pdf->Cell(30,7,'R$ '.number_format($pedido['Valor'],2,',','.'),1,1,'R',true);

    $pdf->Ln(6);
}

/* ================= TOTAL GERAL ================= */

$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(249,115,22);
$pdf->SetTextColor(255,255,255);

$pdf->Cell(140,10,pdf('TOTAL GERAL'),1,0,'R',true);
$pdf->Cell(30,10,'R$ '.number_format($totalGeral,2,',','.'),1,1,'R',true);

/* ================= OUTPUT ================= */

ob_end_clean();
$pdf->Output('I','RelatorioPedidos.pdf');

exit;
?>