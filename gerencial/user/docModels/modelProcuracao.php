<?php

require_once '../base/fpdf/fpdf.php';

class ModelProcuracao
{
    public static function gerarProcuracaoPDF(array $cliente, array $outorgado): void
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);

        // ==================== LOGO ====================
        self::adicionarLogo($pdf, $outorgado);

        // TÍTULO
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, self::txt('PROCURAÇÃO'), 0, 1, 'C');
        $pdf->Ln(8);

        // ==================== OUTORGANTE ====================
        $pdf->SetFont('Arial', 'B', 11);   // Nome em negrito
        $nomeCliente = mb_strtoupper($cliente['Nome'] ?? '', 'UTF-8');

        $pdf->Write(7, self::txt($nomeCliente)); 

        $pdf->SetFont('Arial', '', 11);
        $textoCliente = sprintf(
            ', brasileiro(a), %s, %s, inscrito(a) no CPF nº %s, residente e domiciliado(a) na %s, %s, %s na cidade de %s/%s.',
            $cliente['EstadoCivil'] ?? 'EstadoCivil não informado',
            $cliente['Profissao'] ?? 'Profissão não informada',
            $cliente['CpfCnpj'] ?? '',
            $cliente['Endereco'] ?? '',
            $cliente['NumeroEndereco'] ?? 'NúmeroEndereco não informado',
            $cliente['Bairro'] ?? 'Bairro não informado',
            $cliente['Cidade'] ?? 'Cidade não informada',
            $cliente['Estado'] ?? 'UF não informada'
        );

        $pdf->Write(7, self::txt($textoCliente)); 
        $pdf->Ln(8);

        // ==================== OUTORGADO ====================
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, self::txt('OUTORGA A'), 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 11); 
        $nomeOutorgado = $outorgado['Nome'] ?? '';

        $pdf->Write(7, self::txt($nomeOutorgado));

        $pdf->SetFont('Arial', '', 11);
        $textoOutorgado = sprintf(
            ', brasileiro, %s, advogado, inscrito na OAB/RS sob o nº %s, CPF nº %s, com escritório profissional na %s, n° %s, %s  na cidade de %s/%s, cep: %s.',
            $outorgado['EstadoCivil'] ?? 'EstadoCivil não informado',
            $outorgado['OAB'] ?? '',
            $outorgado['CPF'] ?? '',
            $outorgado['Endereco'] ?? '',
            $outorgado['NumeroEndereco'] ?? 'N[umeroEndereço não informado',
            $outorgado['Bairro'] ?? 'Bairro não informado',
            $outorgado['Cidade'] ?? 'Cidade não informada',
            $outorgado['Estado'] ?? 'UF não informada',
            $outorgado['CEP'] ?? 'CEP não informado'
        );

        $pdf->Write(7, self::txt($textoOutorgado));
        $pdf->Ln(8);

        // ==================== OS PODERES ====================
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, self::txt('OS PODERES'), 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 11);
        $poderes = 'O outorgante nomeia e constitui o outorgado seu procurador onde com esta se apresentar, outorgando-lhe os poderes necessários para representá-lo em juízo e fora dele, para praticar qualquer ação ou ato, como oponente, assistente, tudo podendo praticar, assinar ou requerer, bem como transigir, desistir, reconvir, concordar, discordar, ratificar, retificar, receber quantias, dar quitação, oferecer queixa-crime, acompanhar quaisquer processos em todos os termos e instâncias, representar perante qualquer repartição, autarquia ou órgão federal, estadual ou municipal, firmar qualquer compromisso, inclusive de inventariante, e ainda, praticar todos os demais atos que se fizerem necessários para o bem desempenhar os poderes constantes no presente mandato.';

        $pdf->MultiCell(0, 7, self::txt($poderes), 0, 'J');
        $pdf->Ln(5);

        // ==================== FINALIDADE ====================
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, self::txt('FINALIDADE'), 0, 1);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 11);
        $finalidade = 'Representar judicialmente/extrajudicialmente perante ao DETRAN/RS, DAER/RS e demais órgãos de trânsito.';

        $pdf->MultiCell(0, 7, self::txt($finalidade), 0, 'J');
        $pdf->Ln(10);

        // ==================== DATA ====================
        $cidade = $cliente['Cidade'] ?? 'Venâncio Aires';
        $estado = $cliente['Estado'] ?? 'RS';

        $dataExtenso = sprintf(
            '%s/%s, %s de %s de %s.',
            $cidade,
            $estado,
            date('d'),
            self::nomeMes(date('m')),
            date('Y')
        );

        $pdf->Cell(0, 8, self::txt($dataExtenso), 0, 1, 'L');
        $pdf->Ln(20);

        // ==================== ASSINATURA ====================
        $pdf->Cell(0, 8, '___________________________________________________________________', 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, self::txt(mb_strtoupper($cliente['Nome'] ?? '', 'UTF-8') . ' - OUTORGANTE'), 0, 1, 'C');

        $pdf->Output('I', 'procuracao.pdf');
        exit;
    }

    // ====================== LOGO ======================
    private static function adicionarLogo(FPDF $pdf, array $outorgado): void
    {
        if (!empty($outorgado['LogoBase64'])) {
            $logoBase64 = $outorgado['LogoBase64'];
            if (preg_match('/^data:image\/(\w+);base64,/', $logoBase64, $tipo)) {
                $logoBase64 = substr($logoBase64, strpos($logoBase64, ',') + 1);
                $tipoImagem = strtolower($tipo[1]);
                $logoData = base64_decode($logoBase64);

                if ($logoData !== false) {
                    $arquivoTemp = tempnam(sys_get_temp_dir(), 'logo_') . '.' . $tipoImagem;
                    file_put_contents($arquivoTemp, $logoData);
                    $pdf->Image($arquivoTemp, 77, 12, 55);
                    $pdf->Ln(25);

                    register_shutdown_function(function() use ($arquivoTemp) {
                        if (file_exists($arquivoTemp)) unlink($arquivoTemp);
                    });
                }
            }
        } elseif (file_exists(__DIR__ . '/../img/logo_procuracao.png')) {
            $pdf->Image(__DIR__ . '/../img/logo_procuracao.png', 77, 12, 55);
            $pdf->Ln(25);
        }
    }

    private static function txt(string $texto): string
    {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }

    private static function nomeMes(string $mes): string
    {
        $meses = [
            '01' => 'janeiro', '02' => 'fevereiro', '03' => 'março', '04' => 'abril',
            '05' => 'maio', '06' => 'junho', '07' => 'julho', '08' => 'agosto',
            '09' => 'setembro', '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro'
        ];
        return $meses[$mes] ?? '';
    }
}