<?php

require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';


$msgRetorno = "";
$tipoMsg = "";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'];

$listaDespesa = ExSqlNET("
    SELECT *, CASE Categoria WHEN 1 THEN 'Fixa' WHEN 2 THEN 'Variável' WHEN 3 THEN 'Impostos'
    WHEN 4 THEN 'Serviços' WHEN 5 THEN 'Outros'
    END AS CategoriaLiteral
    FROM tipodespesa
    WHERE idEmpresa = ".$idEmpresa ."
    ORDER BY Descricao
");

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Despesas | Autodoc Gerencial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <!-- CSS base do sistema -->
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
</head>

<body>

<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">

    <div class="page-title">Despesas e Contas</div>
    <div class="subtitle">
        Cadastro de todas as despesas e contas do sistema
    </div>

    <?php

        if (isset($_SESSION['mensagem_sucesso'])) {
            $msgRetorno = $_SESSION['mensagem_sucesso'];
            $tipoMsg = "success";
            unset($_SESSION['mensagem_sucesso']);
        }
        if (isset($_SESSION['mensagem_erro'])) {
            $msgRetorno = $_SESSION['mensagem_erro'];
            unset($_SESSION['mensagem_erro']);
            $tipoMsg = "error";
        }

        if ($msgRetorno): ?>
        <div class="alert <?= $tipoMsg ?>">
            <?= htmlspecialchars($msgRetorno) ?>
        </div>
        <?php endif;

        if ($tipoMsg === 'success') {
            $_POST = [];
        }
    ?>

    <div class="actions">
        <a href="Despesa" class="btn">Nova Despesa</a>
    </div>

    <div class="card">
        
        <div style="margin-bottom:15px; position:relative;">
        
            <span style="
                position:absolute;
                left:10px;
                top:50%;
                transform:translateY(-50%);
                font-size:14px;
                color:#64748b;
            ">🔎</span>
        
            <input 
                type="text"
                id="filtroTabela"
                placeholder="Buscar despesa..."
                style="
                    width:100%;
                    padding:8px 8px 8px 32px;
                    border:1px solid #ddd;
                    border-radius:6px;
                "
                onkeyup="filtrarTabela()"
                autofocus
            >
        
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <!-- <th>Código</th> -->
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Ação</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!empty($listaDespesa)) : ?>
                    <?php foreach ($listaDespesa as $despesa) : ?>

                        <?php
                            // Impacto no caixa
                            if ($despesa['Acao'] === 1) {
                                $impactoHtml = '<span class="badge badge-entrada">Entrada</span>';
                            } elseif ($despesa['Acao'] === -1) {
                                $impactoHtml = '<span class="badge badge-saida">Saída</span>';
                            } else {
                                $impactoHtml = '<span class="badge badge-neutro">Sem impacto</span>';
                            }

                            // Status
                            $statusClass = $despesa['Inativo'] == 0 ? 'status-ativo' : 'status-inativo';
                            $statusTexto = $despesa['Inativo'] == 0 ? 'Ativo' : 'Inativo';
                        ?>

                        <tr onclick="selecionarDespesa(
                            '<?= $despesa['id'] ?>',
                            '<?= htmlspecialchars($despesa['Descricao'], ENT_QUOTES) ?>'
                        )">
                            <!-- <td><?= htmlspecialchars($despesa['Codigo']) ?></td> -->
                            <td><?= htmlspecialchars($despesa['Descricao']) ?></td>
                            <td><?= htmlspecialchars($despesa['CategoriaLiteral']) ?></td>
                            <td><?= $impactoHtml ?></td>
                            <td class="<?= $statusClass ?>"><?= $statusTexto ?></td>
                        </tr>

                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">Nenhuma despesa cadastrada</td>
                    </tr>
                <?php endif; ?>
                </tbody>

        </div>
    </div>

</div>

</body>
</html>
<script>
function selecionarDespesa(Codigo) {
    window.location.href = 'Despesa?Codigo=' + Codigo;
}


function filtrarTabela() {

    let input = document.getElementById("filtroTabela");
    let filtro = input.value.toLowerCase();

    let tabela = document.querySelector("table tbody");
    let linhas = tabela.getElementsByTagName("tr");

    for (let i = 0; i < linhas.length; i++) {

        let textoLinha = linhas[i].innerText.toLowerCase();

        if (textoLinha.indexOf(filtro) > -1) {
            linhas[i].style.display = "";
        } else {
            linhas[i].style.display = "none";
        }

    }
}

</script>