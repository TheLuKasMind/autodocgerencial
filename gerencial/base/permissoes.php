<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paginasSistema = [

    'frmHome.php' => 'Inicial',

    'frmLancamentoLista.php' => 'Pedidos e Vendas',
    'frmLancamento.php' => 'Pedidos e Vendas',

    'frmMultaLista.php' => 'Multas',
    'frmMulta.php' => 'Multas',

    'frmBoletimCaixa.php' => 'Boletim de Caixa',

    'frmFinanceiro.php' => 'Financeiro Geral',

    'frmPatrimonio.php' => 'Compras e Patrimônio',

    'frmServProdLista.php' => 'Serviços / Produtos',
    'frmServProd.php' => 'Serviços / Produtos',

    'frmForcliLista.php' => 'Clientes / Fornecedores',
    'frmForcli.php' => 'Clientes / Fornecedores',

    'frmGrupo.php' => 'Grupos',

    'frmDespesasLista.php' => 'Despesas e Contas',
    'frmDespesas.php' => 'Despesas e Contas',

    'frmForcliExtrato.php' => 'Extrato Clientes',

    'frmDocumentos.php' => 'Geração de Documentos',

    'frmUser.php' => 'Usuários',

    'frmRel.php' => 'Relatórios',

    // 'frmGerencial.php' => 'Gerencial',

    // 'frmPlanos.php' => 'Planos',

    'frmMeuCadastro.php' => 'Meu Cadastro',

    'frmConfig.php' => 'Configurações'
];

?>