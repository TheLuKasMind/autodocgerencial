<?php
ob_start();

include '../base/baseFuncoes.php';
require_once '../base/connection.php';
require('../base/fpdf/fpdf.php');
require_once '../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$id = $_GET['id'] ?? 0;

if (!$id) {
    die("ID inválido");
}

/* ================= BUSCAR MOVIMENTO ================= */

$dados = ExSqlNET(
    "SELECT *,
        movimento.Id As idMovimento,
        c.Nome As ForcliNome,
        p.Nome As PagadorNome,
        movimento.Obs
     FROM movimento
     LEFT JOIN forcli c on c.id = movimento.Forcli
     LEFT JOIN forcli p on p.id = movimento.ForcliRepasse
     WHERE movimento.id = ?
       AND movimento.idEmpresa = ?",
    null,
    [$id, $_SESSION['idEmpresa']]
);

$dados = $dados[0] ?? null;

if (!$dados) {
    die("Registro não encontrado");
}

/* ================= BUSCAR ITENS ================= */

$itens = ExSqlNET(
    "SELECT *
     FROM movimentoitem
     WHERE ControleMovimento = ?
       AND idEmpresa = ?",
    null,
    [$dados['idMovimento'], $_SESSION['idEmpresa']]
);

/* ================= FUNÇÃO UTF ================= */

function cv($txt) {
    return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
}

/* ================= DADOS EMPRESA ================= */

$dadosEmpresa = ExSqlNET(
    "SELECT Nome, Documento, Email FROM empresa WHERE id = ? ",
    null,
    [$_SESSION['idEmpresa']]
);

$dadosEmpresa = $dadosEmpresa[0] ?? null;

/* ================= PDF ================= */

class PDF extends FPDF {

    function Header() {

        // Faixa superior
        // $this->SetFillColor(245,124,0);
        // $this->Rect(0,0,210,25,'F');

        // $this->SetTextColor(255,255,255);
        // $this->SetFont('Arial','B',16);
        // $this->SetY(8);
        // // $this->Cell(0,8,'AUTODOC GERENCIAL',0,1,'C');

        // $this->SetFont('Arial','',10);
        // $this->Cell(0,5,'Documento gerado pelo sistema',0,1,'C');

        // $this->Ln(15);
        // $this->SetTextColor(0,0,0);
        
        
         // Faixa superior
        $this->SetFillColor(245,124,0);
        $this->Rect(0,0,210,25,'F');

        $this->SetTextColor(255,255,255);
        $this->SetFont('Arial','B',16);
        $this->SetY(6);
        
        // Nome da empresa
        $this->Cell(0,7, mb_convert_encoding($this->empresa['Nome'], 'ISO-8859-1', 'UTF-8'),0,1,'C');

        $this->SetFont('Arial','',9);

        // Documento
        $this->Cell(0,5, 'Documento: '.$this->empresa['Documento'],0,1,'C');

        // Email
        $this->Cell(0,5, 'Email: '.$this->empresa['Email'],0,1,'C');

        $this->Ln(8);
        $this->SetTextColor(0,0,0);
    }

    function Footer() {

        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,
            'Gerado em '.date('d/m/Y H:i').'  |  Pagina '.$this->PageNo(),
            0,0,'C'
        );
    }
}

$pdf = new PDF();
$pdf = new PDF();
$pdf->empresa = $dadosEmpresa; //Lucas 17.03.2026 14:41

$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);


/* ================= TÍTULO ================= */

$pdf->SetFont('Arial','B',18);

$titulo = ($dados['Status'] == 2)
    ? 'ORÇAMENTO'
    : 'PEDIDO';

$pdf->Cell(0,10, cv($titulo),0,1,'C');
$pdf->Ln(5);


/* ================= DADOS ================= */

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,7,'Dados Gerais',0,1);

$pdf->SetFont('Arial','',11);
$pdf->Cell(100,6,'Data: '.date('d/m/Y', strtotime($dados['Data'])),0,1);

$pdf->Ln(4);


/* ================= CLIENTE ================= */

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,7,'Cliente',0,1);

$pdf->SetFont('Arial','',11);
$pdf->Cell(0,6, cv('Nome: '.$dados['ForcliNome']),0,1);

if (!empty($dados['PagadorNome'])) {
    $pdf->Cell(0,6, cv('Pagador: '.$dados['PagadorNome']),0,1);
}

$pdf->Ln(5);


/* ================= VEÍCULO ================= */

if (!empty($dados['ModeloVeiculo']) || !empty($dados['PlacaVeiculo'])) {

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,7,'Veiculo',0,1);

    $pdf->SetFont('Arial','',11);
    $pdf->Cell(0,6, cv('Modelo: '.$dados['ModeloVeiculo']),0,1);
    $pdf->Cell(0,6, cv('Cor: '.$dados['CorVeiculo']),0,1);
    $pdf->Cell(0,6, cv('Placa: '.$dados['PlacaVeiculo']),0,1);

    $pdf->Ln(5);
}


/* ================= TABELA ================= */

$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,7,'Itens',0,1);

$pdf->SetFillColor(245,124,0);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',10);

$pdf->Cell(90,8,'Descricao',1,0,'C',true);
$pdf->Cell(25,8,'Qtd',1,0,'C',true);
$pdf->Cell(35,8,'Valor',1,0,'C',true);
$pdf->Cell(40,8,'Total',1,1,'C',true);

$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);

$totalGeral = 0;
$fill = false;

foreach ($itens as $item) {

    $qtd   = floatval($item['Qtd']);
    $valor = floatval($item['Valor']);
    $total = floatval($item['TotalItem']);

    $totalGeral += $total;

    $pdf->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);

    $pdf->Cell(90,7, cv($item['Descricao']),1,0,'L',true);
    $pdf->Cell(25,7, $qtd,1,0,'C',true);
    $pdf->Cell(35,7, number_format($valor,2,',','.'),1,0,'R',true);
    $pdf->Cell(40,7, number_format($total,2,',','.'),1,1,'R',true);

    $fill = !$fill;
}


/* ================= TOTAL DESTACADO ================= */

$pdf->SetFont('Arial','B',13);
$pdf->SetFillColor(224,242,254);
$pdf->SetDrawColor(14,165,233);

$pdf->Cell(150,10,'Total Geral',1,0,'R',true);
$pdf->Cell(
    40,
    10,
    'R$ '.number_format($totalGeral,2,',','.'),
    1,
    1,
    'R',
    true
);

$pdf->Ln(10);


/* ================= OBS ================= */

if (!empty($dados['Obs'])) {

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,7,cv('Observações'),0,1);

    $pdf->SetFont('Arial','',11);

    $pdf->MultiCell(
        0,
        6,
        cv($dados['Obs']),
        1,   // borda
        'L'
    );

    $pdf->Ln(5);
}

/* Rodapé padrão */
$pdf->SetFont('Arial','I',10);
$pdf->MultiCell(
    0,
    6,
    cv('Obrigado pela preferencia!')
);


/* ================= SAÍDA ================= */

ob_end_clean();
$pdf->Output('I', 'pedido.pdf');
exit;