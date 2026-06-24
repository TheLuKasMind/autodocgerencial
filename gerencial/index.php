<?php

$url = $_GET['url'] ?? '';

switch ($url) {

    case '':
    //BASE==============
    case 'gerencial/Inicial':
        require 'frmInicial.php';
        break;
    case 'gerencial/Login':
        require __DIR__ . '/frmLogin.php';
        break;
    // case 'gerencial/Login':
    //     require 'frmLogin.php';
    //     break;
    
    case 'gerencial/Home':
        require 'user/frmHome.php';
        break;

    //PEDIDOS==============
    case 'gerencial/Pedidos':
        require 'user/frmLancamentoLista.php';
        break;
    case 'gerencial/Pedido':
        require 'user/frmLancamento.php';
        break;
    case 'gerencial/Pedido/Imprimir':
        require 'user/frmLancamentoImprimir.php';
        break;
    case 'gerencial/Pedidos/ImprimirLista':
        require 'user/frmLancamentoListaImprimir.php';
        break;

    //MULTA==============
    case 'gerencial/Multas':
        require 'user/frmMultaLista.php';
        break;
    case 'gerencial/Multa':
        require 'user/frmMulta.php';
        break;
    case 'gerencial/Multas/ImprimirLista':
        require 'user/frmMultaListaImprimir.php';
        break;
    case 'gerencial/Multa/Imprimir':
        require 'user/frmMultaImprimir.php';
        break;

    //BOLETIM DE CAIXA==============
    case 'gerencial/BoletimCaixa':
        require 'user/frmBoletimCaixa.php';
        break;
    case 'gerencial/BoletimCaixa/Lancamento':
        require 'user/frmBoletimCaixaLcto.php';
        break;
    case 'gerencial/BoletimCaixa/Imprimir':
        require 'user/pdfBoletimCaixa.php';
        break;

    //FINANCEIRO==============
    case 'gerencial/Financeiro':
        require 'user/frmFinanceiro.php';
        break;

    //PATRIMÔNIO==============
    case 'gerencial/Patrimonio':
        require 'user/frmPatrimonio.php';
        break;

    //PRODUTO==============
    case 'gerencial/Produtos':
        require 'user/frmServProdLista.php';
        break;
    case 'gerencial/Produto':
        require 'user/frmServProd.php';
        break;

    //CLIENTES==============
    case 'gerencial/Clientes':
        require 'user/frmForcliLista.php';
        break;
    case 'gerencial/Cliente':
        require 'user/frmForcli.php';
        break;
    case 'gerencial/Cliente/Extrato':
        require 'user/frmForcliExtrato.php';
        break;
    case 'gerencial/Cliente/Extrato/Imprimir':
        require 'user/pdfExtrato.php';
        break;
    case 'gerencial/ajaxCnpj.php':
        require 'user/ajaxCnpj.php';
        break;
    case 'gerencial/base.js':
        require 'user/base.js';
        break;
    case 'gerencial/abrirArquivo':
        require 'user/abrirArquivo';
        break;

    //GRUPOS==============
    case 'gerencial/Grupos':
        require 'user/frmGrupo.php';
        break;

    //DESPESAS==============
    case 'gerencial/Despesas':
        require 'user/frmDespesasLista.php';
        break;
    case 'gerencial/Despesa':
        require 'user/frmDespesas.php';
        break;
      
    //LEMBRETES==============
    case 'gerencial/Lembretes':
        require 'user/frmLembrete.php';
        break;

    //DOCUMENTOS==============
    case 'gerencial/Documentos':
        require 'user/frmDocumentos.php';
        break;

    //USUÁRIOS==============
    case 'gerencial/Usuarios':
        require 'user/frmUser.php';
        break;

    //RELATÓRIOS==============
    case 'gerencial/Relatorios':
        require 'user/frmRel.php';
        break;
    case 'gerencial/Relatorio/PDF':
        require 'user/pdfRelatorio.php';
        break;

    //MEU CADASTRO==============
    case 'gerencial/MeuCadastro':
        require 'user/frmMeuCadastro.php';
        break;

    //CONFIGURAÇÃO==============
    case 'gerencial/Configuracao':
        require 'user/frmConfig.php';
        break;


    // ->ADMIN
    //GERENCIAL==============
    case 'gerencial/Gerencial':
        require 'admin/frmGerencial.php';
        break;

    //PLANOS==============
    case 'gerencial/Planos':
        require 'admin/frmPlanos.php';
        break;

    //BACKUP==============
    case 'gerencial/Backup':
        require 'admin/frmBackup.php';
        break;

    // === ROTA PARA CALLBACK ===
    case 'gerencial/Callback':
        require 'admin/callback.php';
        break;

    case 'gerencial/GerarToken':
        require 'admin/gdrive-auth.php';
        break;  

    default:
        // echo 'Página não encontrada. Página: '. $url;
        require __DIR__ . '/../gerencial/frmInicial.php';
        
        break;
}

?>
