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
        c.Documento As ForcliDocumento,
        p.Nome As PagadorNome,
        p.Documento As PagadorDocumento,
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
    "SELECT Nome, Documento, Email
     FROM empresa
     WHERE id = ? ",
    null,
    [$_SESSION['idEmpresa']]
);

$dadosEmpresa = $dadosEmpresa[0] ?? null;

/* ================= PDF ================= */

class PDF extends FPDF {

    public $empresa;

    function Header() {

        // Fundo topo
        $this->SetFillColor(245,124,0);
        $this->Rect(0,0,210,34,'F');

        // Nome empresa
        $this->SetTextColor(255,255,255);
        $this->SetFont('Arial','B',18);
        $this->SetY(8);

        $this->Cell(
            0,
            8,
            mb_convert_encoding($this->empresa['Nome'], 'ISO-8859-1', 'UTF-8'),
            0,
            1,
            'L'
        );

        // Documento e email
        $this->SetFont('Arial','',9);

        $empresaInfo =
            'Documento: '.$this->empresa['Documento'].
            '   |   Email: '.$this->empresa['Email'];

        $this->Cell(
            0,
            5,
            mb_convert_encoding($empresaInfo, 'ISO-8859-1', 'UTF-8'),
            0,
            1,
            'L'
        );

        // Linha
        $this->SetDrawColor(220,220,220);
        $this->Line(10,38,200,38);

        $this->Ln(14);

        $this->SetTextColor(0,0,0);
    }

    function Footer() {

        $this->SetY(-18);

        $this->SetDrawColor(220,220,220);
        $this->Line(10,$this->GetY(),200,$this->GetY());

        $this->Ln(2);

        $this->SetFont('Arial','I',8);

        $this->Cell(
            0,
            10,
            utf8_decode(
                'Gerado em '.date('d/m/Y H:i').
                '  |  Página '.$this->PageNo()
            ),
            0,
            0,
            'C'
        );
    }

    function BoxTitulo($titulo){

        $this->SetFillColor(255,243,224);

        $this->SetTextColor(230,81,0);

        $this->SetFont('Arial','B',11);

        $this->Cell(
            0,
            8,
            utf8_decode($titulo),
            0,
            1,
            'L',
            true
        );

        $this->SetTextColor(0,0,0);
    }
}

/* ================= INICIAR PDF ================= */

$pdf = new PDF();
$pdf->empresa = $dadosEmpresa;

$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

/* ================= TÍTULO ================= */

$titulo = ($dados['Status'] == 2)
    ? 'ORÇAMENTO'
    : 'PEDIDO';

$pdf->SetFont('Arial','B',20);

$pdf->Cell(140,10,cv($titulo),0,0,'L');

/* ================= STATUS ================= */

$statusTexto = 'NÃO PAGO';
$statusCor = [245,124,0];

if($dados['Status'] == 1){
    $statusTexto = 'PAGO';
    $statusCor = [46,125,50];
}

if($dados['Status'] == 2){
    $statusTexto = 'ORÇAMENTO';
    $statusCor = [255,193,7];
}

if($dados['Status'] == 3){
    $statusTexto = 'DÉBITO';
    $statusCor = [220,53,69];
}

if($dados['Status'] == 4){
    $statusTexto = 'DÉBITO PAGO';
    $statusCor = [13,110,253];
}

$pdf->SetFillColor($statusCor[0], $statusCor[1], $statusCor[2]);
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('Arial','B',10);

$pdf->Cell(
    40,
    10,
    cv($statusTexto),
    0,
    1,
    'C',
    true
);

$pdf->SetTextColor(0,0,0);

$pdf->Ln(8);

/* ================= DADOS GERAIS ================= */

$pdf->BoxTitulo('DADOS GERAIS');

$pdf->SetFont('Arial','',11);

$pdf->Cell(
    95,
    7,
    cv('Número do Pedido: '.$dados['idMovimento']),
    0,
    0
);

$pdf->Cell(
    95,
    7,
    cv('Data: '.date('d/m/Y', strtotime($dados['Data']))),
    0,
    1
);

/* ================= DATA PAGAMENTO ================= */

if (!empty($dados['DataPgto'])) {

    $pdf->Cell(
        95,
        7,
        cv('Data Pagamento: '.date('d/m/Y', strtotime($dados['DataPgto']))),
        0,
        1
    );
}

/* ================= CONDIÇÃO ================= */

$condicoes = [
    1 => 'À Vista / Dinheiro',
    2 => 'Pix',
    3 => 'Cartão de Crédito',
    4 => 'Cartão de Débito',
    5 => 'Cheque',
    6 => '30 Dias',
    7 => '60 Dias'
];

$condicaoTexto = $condicoes[$dados['CondPgto']] ?? '-';

$pdf->Cell(
    95,
    7,
    cv('Condição de Pagamento: '.$condicaoTexto),
    0,
    1
);

/* ================= STATUS PROCESSO ================= */

$statusProcesso = '-';

if ($dados['StatusProcesso'] == 1) {
    $statusProcesso = 'Em Andamento';
}

if ($dados['StatusProcesso'] == 2) {
    $statusProcesso = 'Concluído';
}

$pdf->Cell(
    95,
    7,
    cv('Andamento Processo: '.$statusProcesso),
    0,
    1
);

$pdf->Ln(5);

/* ================= CLIENTE ================= */

$pdf->BoxTitulo('CLIENTE');

$pdf->SetFont('Arial','',11);

$pdf->Cell(
    0,
    7,
    cv('Cliente: '.$dados['ForcliNome']),
    0,
    1
);

if (!empty($dados['ForcliDocumento'])) {

    $pdf->Cell(
        0,
        7,
        cv('Documento: '.$dados['ForcliDocumento']),
        0,
        1
    );
}

/* ================= PAGADOR ================= */

if (!empty($dados['PagadorNome'])) {

    $pdf->Cell(
        0,
        7,
        cv('Responsável Financeiro: '.$dados['PagadorNome']),
        0,
        1
    );

    if (!empty($dados['PagadorDocumento'])) {

        $pdf->Cell(
            0,
            7,
            cv('Documento Responsável: '.$dados['PagadorDocumento']),
            0,
            1
        );
    }
}

$pdf->Ln(5);

/* ================= VEÍCULO ================= */

if (
    !empty($dados['ModeloVeiculo']) ||
    !empty($dados['PlacaVeiculo']) ||
    !empty($dados['CorVeiculo'])
) {

    $pdf->BoxTitulo('DADOS DO VEÍCULO');

    $pdf->SetFont('Arial','',11);

    $pdf->Cell(
        95,
        7,
        cv('Modelo: '.$dados['ModeloVeiculo']),
        0,
        0
    );

    $pdf->Cell(
        95,
        7,
        cv('Cor: '.$dados['CorVeiculo']),
        0,
        1
    );

    $pdf->Cell(
        95,
        7,
        cv('Placa: '.$dados['PlacaVeiculo']),
        0,
        1
    );

    $pdf->Ln(5);
}

/* ================= TABELA ================= */

$pdf->BoxTitulo('ITENS DO PEDIDO');

$pdf->SetFillColor(245,124,0);
$pdf->SetTextColor(255,255,255);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(85,10,'Descricao',0,0,'L',true);
$pdf->Cell(20,10,'Qtd',0,0,'C',true);
$pdf->Cell(40,10,'Valor Unit.',0,0,'R',true);
$pdf->Cell(45,10,'Total',0,1,'R',true);

$pdf->SetTextColor(0,0,0);

$totalGeral = 0;
$fill = false;

foreach ($itens as $item) {

    $qtd   = floatval($item['Qtd']);
    $valor = floatval($item['Valor']);
    $total = floatval($item['TotalItem']);

    $totalGeral += $total;

    if($fill){
        $pdf->SetFillColor(255,248,240);
    } else {
        $pdf->SetFillColor(255,255,255);
    }

    $pdf->SetFont('Arial','',10);

    $pdf->Cell(
        85,
        9,
        cv($item['Descricao']),
        0,
        0,
        'L',
        true
    );

    $pdf->Cell(
        20,
        9,
        number_format($qtd,0,',','.'),
        0,
        0,
        'C',
        true
    );

    $pdf->Cell(
        40,
        9,
        'R$ '.number_format($valor,2,',','.'),
        0,
        0,
        'R',
        true
    );

    $pdf->Cell(
        45,
        9,
        'R$ '.number_format($total,2,',','.'),
        0,
        1,
        'R',
        true
    );

    $fill = !$fill;
}

/* ================= TOTAL ================= */

$pdf->Ln(5);

$pdf->SetFont('Arial','B',16);

$pdf->SetFillColor(245,124,0);
$pdf->SetTextColor(255,255,255);

$pdf->Cell(
    145,
    12,
    'TOTAL GERAL',
    0,
    0,
    'R',
    true
);

$pdf->Cell(
    45,
    12,
    'R$ '.number_format($totalGeral,2,',','.'),
    0,
    1,
    'R',
    true
);

$pdf->SetTextColor(0,0,0);

$pdf->Ln(8);

/* ================= OBS ================= */

if (!empty($dados['Obs'])) {

    $pdf->BoxTitulo('OBSERVAÇÕES');

    $pdf->SetFont('Arial','',11);

    $pdf->MultiCell(
        0,
        7,
        cv($dados['Obs']),
        0,
        'L'
    );

    $pdf->Ln(6);
}

/* ================= ASSINATURA ================= */

$pdf->Ln(18);

$pdf->SetDrawColor(180,180,180);

$pdf->Line(60, $pdf->GetY(), 150, $pdf->GetY());

$pdf->Ln(3);

$pdf->SetFont('Arial','',10);

$pdf->Cell(
    0,
    6,
    cv('Assinatura do Cliente'),
    0,
    1,
    'C'
);

/* ================= RODAPÉ FINAL ================= */

$pdf->Ln(10);

$pdf->SetFont('Arial','I',9);

$pdf->MultiCell(
    0,
    5,
    cv('Obrigado pela preferência!'),
    0,
    'C'
);

/* ================= SAÍDA ================= */

ob_end_clean();

$pdf->Output('I', 'pedido.pdf');

exit;
?>