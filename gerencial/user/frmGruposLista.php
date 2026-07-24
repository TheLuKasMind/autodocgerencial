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

$listaGrupos = ExSqlNET("
    SELECT Id, Inativo, Nome as Descricao, CASE Tipo WHEN 'P' THEN 'Produtos' WHEN 'C' THEN 'Clientes'
    END AS TipoLiteral
    FROM grupos
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

    <div class="page-title">Cadastro de Grupos</div>
    <div class="subtitle">
        Cadastro de grupos de produtos e clientes
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
        <a href="Grupo" class="btn">Novo Cadastro</a>
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
                        <th>Tipo</th>
                        <th>Inativo</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!empty($listaGrupos)) : ?>
                    <?php foreach ($listaGrupos as $grupo) : ?>

                        <?php
                            // Status
                            $statusClass = $grupo['Inativo'] == 0 ? 'status-ativo' : 'status-inativo';
                            $statusTexto = $grupo['Inativo'] == 0 ? 'Ativo' : 'Inativo';
                        ?>

                        <tr onclick="selecionarGrupo(
                            '<?= $grupo['Id'] ?>',
                            '<?= htmlspecialchars($grupo['Descricao'], ENT_QUOTES) ?>'
                        )">
                            <!-- <td><?= htmlspecialchars($grupo['Id']) ?></td> -->
                            <td><?= htmlspecialchars($grupo['Descricao']) ?></td>
                            <td><?= htmlspecialchars($grupo['TipoLiteral']) ?></td>
                            <td class="<?= $statusClass ?>"><?= $statusTexto ?></td>
                        </tr>

                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">Nenhum grupo cadastrado</td>
                    </tr>
                <?php endif; ?>
                </tbody>

        </div>
    </div>

</div>

</body>
</html>
<script>
function selecionarGrupo(Codigo) {
    window.location.href = 'Grupo?Codigo=' + Codigo;
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