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

$idEmpresa = $_SESSION['idEmpresa'];
$clienteFiltro = $_GET['cliente'] ?? '';
$dataHoje = date('Y-m-d');
$dataInicial = $_GET['dataInicial'] ?? $dataHoje;
$dataFinal   = $_GET['dataFinal'] ?? $dataHoje;

$statusFiltro = $_GET['status'] ?? '';
$tipoPesquisa = $_GET['tipo'] ?? 0;

/* ===========================
   MOVIMENTO (PEDIDOS/VENDAS)
=========================== */
$where = " WHERE m.idEmpresa = $idEmpresa ";
$params = [];

if ($clienteFiltro != '') {
    $where .= " AND m.Forcli = ? ";
    $params[] = $clienteFiltro;
}

if ($dataInicial != '') {
    $where .= " AND DATE(m.Data) >= ? ";
    $params[] = $dataInicial;
}

if ($dataFinal != '') {
    $where .= " AND DATE(m.Data) <= ? ";
    $params[] = $dataFinal;
}

if ($statusFiltro !== '' && $statusFiltro !== null) {
    if ($statusFiltro == 0) {
        $where .= " AND m.Status IN (0,3) ";
    } else {
        $where .= " AND m.Status = ? ";
        $params[] = $statusFiltro;
    }
}

/* ===========================
   MOVIMENTOCC (CAIXA)
=========================== */
$whereMovCC = " WHERE movimentocc.idEmpresa = $idEmpresa ";
$paramsMovCC = [];

if ($clienteFiltro != '') {
    $whereMovCC .= " AND movimentocc.idForcli = ? ";
    $paramsMovCC[] = $clienteFiltro;
}

if ($dataInicial != '') {
    $whereMovCC .= " AND DATE(movimentocc.Data) >= ? ";
    $paramsMovCC[] = $dataInicial;
}

if ($dataFinal != '') {
    $whereMovCC .= " AND DATE(movimentocc.Data) <= ? ";
    $paramsMovCC[] = $dataFinal;
}

/* ===========================
   CONSULTA FINAL POR TIPO
=========================== */

if ($tipoPesquisa == 1) {

    /* ===========================
       SOMENTE PEDIDOS / VENDAS
    ============================ */
    $sqlFinal = "
        SELECT 
            m.id,
            m.Data,
            f.Nome,
            m.Obs,
            CASE
                WHEN movimentocc.Descricao IS NOT NULL
                    AND movimentocc.Descricao <> ''
                THEN movimentocc.Descricao

                WHEN m.Status = 3
                THEN CONCAT(
                    'Débito em Aberto - ',
                    COALESCE(
                        (
                            SELECT MovimentoItem.Descricao
                            FROM movimentoitem MovimentoItem
                            WHERE MovimentoItem.ControleMovimento = m.id
                            ORDER BY MovimentoItem.id ASC
                            LIMIT 1
                        ),
                        'Sem itens'
                    )
                )

                WHEN m.Status = 4
                THEN CONCAT(
                    'Débito Pago - ',
                    COALESCE(
                        (
                            SELECT MovimentoItem.Descricao
                            FROM movimentoitem MovimentoItem
                            WHERE MovimentoItem.ControleMovimento = m.id
                            ORDER BY MovimentoItem.id ASC
                            LIMIT 1
                        ),
                        'Sem itens'
                    )
                )

                ELSE CONCAT(
                    CASE m.Status
                        WHEN 0 THEN 'Pedido em Aberto - '
                        WHEN 1 THEN 'Pedido Pago - '
                        WHEN 2 THEN 'Orçamento - '
                        ELSE 'Pedido - '
                    END,
                    COALESCE(
                        (
                            SELECT MovimentoItem.Descricao
                            FROM movimentoitem MovimentoItem
                            WHERE MovimentoItem.ControleMovimento = m.id
                            ORDER BY MovimentoItem.id ASC
                            LIMIT 1
                        ),
                        'Sem itens'
                    )
                )
            END AS Descricao,
            (
                SELECT SUM(mi.TotalItem)
                FROM movimentoitem mi
                WHERE mi.ControleMovimento = m.id
            ) AS Valor,
            (
                SELECT MovimentoItem.Descricao 
                FROM movimentoitem MovimentoItem
                WHERE ControleMovimento = m.id
                LIMIT 1
            ) AS Itens,
            movimentocc.Valor AS Lucro,
            m.Status,
            m.DataPgto
        FROM movimento m
        LEFT JOIN movimentocc 
            ON movimentocc.ControleOrigem = m.id
        LEFT JOIN forcli f 
            ON f.id = m.Forcli
        $where
        ORDER BY m.Data DESC
    ";

    $lista = ExSqlNET($sqlFinal, null, $params);

} elseif ($tipoPesquisa == 2) {

    /* ===========================
       SOMENTE LANCAMENTOS DE CAIXA
    ============================ */
    $sqlFinal = "
        SELECT 
            movimentocc.Controle,
            movimentocc.Data,
            f.Nome,
            '' AS Obs,
            movimentocc.Descricao,
            movimentocc.Valor AS Valor,
            movimentocc.Descricao AS Itens,
            movimentocc.Valor AS Lucro,
            1 AS Status,
            movimentocc.DataPgto
        FROM movimentocc
        LEFT JOIN forcli f 
            ON f.id = movimentocc.idForcli
        $whereMovCC
        AND (
            movimentocc.ControleOrigem IS NULL
            OR movimentocc.ControleOrigem = 0
        )
        ORDER BY movimentocc.Data DESC
    ";

    $lista = ExSqlNET($sqlFinal, null, $paramsMovCC);

} else {

    /* ===========================
       TODOS
       PEDIDOS + CAIXA AVULSO
    ============================ */
    $sqlFinal = "
    
        SELECT 
            m.id,
            m.Data,
            f.Nome,
            m.Obs,
            CASE
                WHEN movimentocc.Descricao IS NOT NULL
                    AND movimentocc.Descricao <> ''
                THEN movimentocc.Descricao

                WHEN m.Status = 3
                THEN CONCAT(
                    'Débito em Aberto - ',
                    COALESCE(
                        (
                            SELECT MovimentoItem.Descricao
                            FROM movimentoitem MovimentoItem
                            WHERE MovimentoItem.ControleMovimento = m.id
                            ORDER BY MovimentoItem.id ASC
                            LIMIT 1
                        ),
                        'Sem itens'
                    )
                )

                WHEN m.Status = 4
                THEN CONCAT(
                    'Débito Pago - ',
                    COALESCE(
                        (
                            SELECT MovimentoItem.Descricao
                            FROM movimentoitem MovimentoItem
                            WHERE MovimentoItem.ControleMovimento = m.id
                            ORDER BY MovimentoItem.id ASC
                            LIMIT 1
                        ),
                        'Sem itens'
                    )
                )

                ELSE CONCAT(
                    CASE m.Status
                        WHEN 0 THEN 'Pedido em Aberto - '
                        WHEN 1 THEN 'Pedido Pago - '
                        WHEN 2 THEN 'Orçamento - '
                        ELSE 'Pedido - '
                    END,
                    COALESCE(
                        (
                            SELECT MovimentoItem.Descricao
                            FROM movimentoitem MovimentoItem
                            WHERE MovimentoItem.ControleMovimento = m.id
                            ORDER BY MovimentoItem.id ASC
                            LIMIT 1
                        ),
                        'Sem itens'
                    )
                )
            END AS Descricao,
            (
                SELECT SUM(mi.TotalItem)
                FROM movimentoitem mi
                WHERE mi.ControleMovimento = m.id
            ) AS Valor,
            (
                SELECT MovimentoItem.Descricao 
                FROM movimentoitem MovimentoItem
                WHERE ControleMovimento = m.id
                LIMIT 1
            ) AS Itens,
            movimentocc.Valor AS Lucro,
            m.Status,
            m.DataPgto,
            m.id as ControleMovimento
        FROM movimento m
        LEFT JOIN movimentocc 
            ON movimentocc.ControleOrigem = m.id
        LEFT JOIN forcli f 
            ON f.id = m.Forcli
        $where

        UNION ALL

        SELECT 
            movimentocc.Controle,
            movimentocc.Data,
            f.Nome,
            '' AS Obs,
            movimentocc.Descricao,
            movimentocc.Valor AS Valor,
            movimentocc.Descricao AS Itens,
            movimentocc.Valor AS Lucro,
            1 AS Status,
            movimentocc.DataPgto,
            0 As ControleMovimento
        FROM movimentocc
        LEFT JOIN forcli f 
            ON f.id = movimentocc.idForcli
        $whereMovCC
        AND (
            movimentocc.ControleOrigem IS NULL
            OR movimentocc.ControleOrigem = 0
        )

        ORDER BY Data DESC
    ";

    $lista = ExSqlNET($sqlFinal, null, array_merge($params, $paramsMovCC));
}

/* ===========================
   PDF CUSTOM
=========================== */

class PDF extends FPDF
{
    function Header()
    {
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
$pdf->Cell(50,8,'Cliente',1,0,'C',true);
$pdf->Cell(60,8,'Descricao',1,0,'C',true);
$pdf->Cell(40,8,'Valor (R$)',1,1,'C',true);

$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);

/* ===========================
   DADOS
=========================== */

$totalGeral = 0;
$fill = false;

foreach ($lista as $item) {

    /* CORREÇÃO:
       sua query retorna campo como Valor,
       não ValorTotal
    */
    $valor = (float)($item['Valor'] ?? 0);

    $totalGeral += $valor;

    $pdf->SetFillColor(248,248,248);

    $pdf->Cell(
        30,
        7,
        date('d/m/Y', strtotime($item['Data'])),
        1,
        0,
        'C',
        $fill
    );

    $nomeCliente = mb_convert_encoding(
        $item['Nome'] ?? '',
        'ISO-8859-1',
        'UTF-8'
    );

    $pdf->Cell(
        50,
        7,
        substr($nomeCliente, 0, 45),
        1,
        0,
        'L',
        $fill
    );

    $descricao = mb_strtoupper(
        mb_convert_encoding(
            $item['Descricao'] ?? '',
            'ISO-8859-1',
            'UTF-8'
        ),
        'ISO-8859-1'
    );

    $pdf->Cell(
        60,
        7,
        substr($descricao, 0, 45),
        1,
        0,
        'L',
        $fill
    );

    $pdf->Cell(
        40,
        7,
        'R$ ' . number_format($valor, 2, ',', '.'),
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

$totalGeral = 0;

foreach ($lista as $item) {
    $valor = (float)($item['Valor'] ?? 0);
    $totalGeral += (float) str_replace(',', '.', $valor ?? 0);
}

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