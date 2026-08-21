<?php
ob_start();

require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';
require_once  __DIR__ .'/../base/fpdf/fpdf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$id = $_GET['id'] ?? 0;

if (!$id) die("ID inválido");

/* ================= BUSCAR MULTA ================= */

$dados = ExSqlNET(
    "SELECT 
        m.*,
        c.Nome AS ClienteNome,
        c.Documento AS ClienteDocumento
     FROM multa m
     LEFT JOIN forcli c ON c.Id = m.Forcli
     WHERE m.Id = ?
       AND m.idEmpresa = ?",
    null,
    [$id, $_SESSION['idEmpresa']]
);

$dados = $dados[0] ?? null;
if (!$dados) die("Multa não encontrada");

/* ================= EMPRESA ================= */

$empresa = ExSqlNET(
    "SELECT Nome, Documento, Email FROM empresa WHERE id = ?",
    null,
    [$_SESSION['idEmpresa']]
);

$empresa = $empresa[0] ?? null;

/* ================= UTF ================= */

function cv($txt) {
    return mb_convert_encoding($txt ?? '', 'ISO-8859-1', 'UTF-8');
}

/* ================= PDF ================= */

class PDF extends FPDF {

    public $empresa;

    function Header() {

        // Barra lateral
        $this->SetFillColor(249,115,22);
        $this->Rect(0,0,8,297,'F');

        // Nome empresa
        $this->SetFont('Arial','B',14);
        $this->SetXY(12,8);
        $this->Cell(0,6, cv($this->empresa['Nome']),0,1);

        $this->SetFont('Arial','',9);
        $this->SetX(12);
        $this->Cell(0,5, cv('Doc: '.$this->empresa['Documento']),0,1);

        $this->SetX(12);
        $this->Cell(0,5, cv($this->empresa['Email']),0,1);

        // Linha separadora
        $this->SetDrawColor(220,220,220);
        $this->Line(10,28,200,28);

        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);

        $this->Cell(
            0,
            10,
            cv('Gerado em '.date('d/m/Y H:i').' | Página '.$this->PageNo()),
            0,
            0,
            'C'
        );
    }

    function box($titulo) {
        $this->SetFont('Arial','B',11);
        $this->SetFillColor(245,245,245);
        $this->Cell(0,8, cv($titulo),0,1,'L',true);
    }

    function linha($label, $valor) {
        $this->SetFont('Arial','B',10);
        $this->Cell(50,6, cv($label),0,0);

        $this->SetFont('Arial','',10);
        $this->Cell(0,6, cv($valor),0,1);
    }
}

/* ================= INICIAR ================= */

$pdf = new PDF();
$pdf->empresa = $empresa;

$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);

/* ================= TÍTULO ================= */

$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,10, cv('MULTA DE TRÂNSITO'),0,1,'C');

$pdf->Ln(4);

/* ================= STATUS (DESTAQUE) ================= */

$statusTexto = [
    0 => 'Em Aberto',
    1 => 'Defesa Enviada',
    2 => 'Em Recurso',
    3 => 'Deferida',
    4 => 'Indeferida',
    5 => 'Finalizada',
    6 => 'Elaboração de defesa',
    7 => 'Defesa Enviada',
    8 => 'Elaboração de recurso',
    9 => 'Recurso 1º instância enviado',
    10 => 'Recurso 2º instância enviado',
    11 => 'Suspenso'
];

$status = $statusTexto[$dados['StatusMulta']] ?? 'Não definido';

$pdf->SetFillColor(255,243,205);
$pdf->SetDrawColor(255,230,156);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8, cv('Status: '.$status),1,1,'C',true);

$pdf->Ln(4);

/* ================= DADOS MULTA ================= */

$pdf->box('Dados da Multa');

if (!empty($dados['DataCadastro'])) {
    $pdf->linha('Data:', date('d/m/Y', strtotime($dados['DataCadastro'])));
}
$pdf->linha('Série:', $dados['SerieMulta']);
$pdf->linha('Processo:', $dados['CodigoProcesso']);
$pdf->linha('Órgão:', $dados['OrgaoFiscalizador']);

if (!empty($dados['PrazoDefesa'])) {
    $pdf->linha('Prazo Defesa:', date('d/m/Y', strtotime($dados['PrazoDefesa'])));
}

$pdf->Ln(4);

/* ================= CLIENTE ================= */

$pdf->box('Cliente');

$pdf->linha('Nome:', $dados['ClienteNome']);
$pdf->linha('Documento:', $dados['ClienteDocumento']);

$pdf->Ln(4);

/* ================= VEÍCULO ================= */

$pdf->box('Veículo');

$pdf->linha('Placa:', $dados['PlacaVeiculo']);
$pdf->linha('Outras:', $dados['PlacasAdicionais']);

$pdf->Ln(4);

/* ================= FLAGS ================= */

$pdf->box('Informações');

$pdf->linha('Auto Suspensiva:', $dados['AutoSuspensiva'] ? 'SIM' : 'NÃO');
$pdf->linha('Recurso:', $dados['RecursoMulta'] ? 'SIM' : 'NÃO');

$pdf->Ln(4);

/* ================= OBS ================= */

if (!empty($dados['Observacao'])) {

    $pdf->box('Observações');

    $pdf->SetFont('Arial','',10);
    $pdf->MultiCell(0,6, cv($dados['Observacao']),1);

    $pdf->Ln(4);
}

/* ================= RODAPÉ FINAL ================= */

$pdf->SetFont('Arial','I',9);
$pdf->Cell(0,6, cv('Documento gerado automaticamente pelo sistema.'),0,1,'C');

ob_end_clean();
$pdf->Output('I', 'multa.pdf');
exit;