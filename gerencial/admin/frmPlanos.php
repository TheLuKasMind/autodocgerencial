<?php
include '../base/baseFuncoes.php';
require_once '../base/connection.php';
require_once '../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])){
    header("Location: ../frmLogin.php");
    exit;
}

if (!isset($_SESSION['AdminGeral']) || $_SESSION['AdminGeral'] != 1) {
    header("Location: ../frmLogin.php");
    exit;
}

global $dbGeralNET;

$erro = '';
$sucesso = '';

/* ================= SALVAR ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = $_POST['Id'] ?? '';
    $nome      = $_POST['Nome'] ?? '';
    $valor     = $_POST['Valor'] ?? 0;
    $periodo   = $_POST['Periodo'] ?? '';
    $descricao = $_POST['Descricao'] ?? '';
    $status    = $_POST['Status'] ?? 1;

    if ($nome == '') {
        $erro = "Informe o nome.";
    } else {

        if ($id) {

            // UPDATE
            $sql = "UPDATE planos 
                    SET Nome=?, Valor=?, Periodo=?, Descricao=?, Status=? 
                    WHERE Id=?";
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([$nome,$valor,$periodo,$descricao,$status,$id]);

            $sucesso = "Plano atualizado.";

        } else {

            // INSERT
            $sql = "INSERT INTO planos (Nome,Valor,Periodo,Descricao,Status)
                    VALUES (?,?,?,?,?)";
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([$nome,$valor,$periodo,$descricao,$status]);

            $sucesso = "Plano cadastrado.";
        }
    }
}

/* ================= AÇÕES ================= */

if (isset($_GET['del'])) {

    $id = $_GET['del'];

    $sql = "DELETE FROM planos WHERE Id=?";
    $stmt = $dbGeralNET->prepare($sql);
    $stmt->execute([$id]);

    header("Location: frmPlanos.php");
    exit;
}

if (isset($_GET['toggle'])) {

    $id = $_GET['toggle'];

    $sql = "UPDATE planos 
            SET Status = CASE WHEN Status=1 THEN 0 ELSE 1 END
            WHERE Id=?";
    $stmt = $dbGeralNET->prepare($sql);
    $stmt->execute([$id]);

    header("Location: frmPlanos.php");
    exit;
}

/* ================= EDITAR ================= */

$edit = null;

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $sql = "SELECT * FROM planos WHERE Id=?";
    $dados = ExSqlNET($sql,null,[$id]);

    if ($dados) {
        $edit = $dados[0];
    }
}

/* ================= LISTA ================= */

$sql = "SELECT * FROM planos ORDER BY Id DESC";
$planos = ExSqlNET($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Planos</title>
    <link rel="stylesheet" href="../css/base.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/favicon.png">
    <style>
        /* ================= PLANOS ================= */

        .plano-valor {
            font-weight: 700;
            color: #ea580c;
            font-size: 15px;
        }

        .plano-periodo {
            font-size: 13px;
            color: #64748b;
        }

        /* STATUS BADGE BONITO */

        .badge-status {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-ativo {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inativo {
            background: #fee2e2;
            color: #991b1b;
        }

        /* AÇÕES */

        .acoes {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        /* BOTÃO ATIVAR / INATIVAR */

        .btn-toggle {
            background: #0ea5e9;
            color: white;
        }

        .btn-toggle:hover {
            background: #0284c7;
        }

        /* BOTÃO EDITAR */

        .btn-editar {
            background: #f59e0b;
            color: white;
        }

        .btn-editar:hover {
            background: #d97706;
        }

        /* TABELA PLANOS AJUSTE */

        .table-planos td {
            vertical-align: middle;
        }

        /* RESPONSIVO MELHOR */

        @media (max-width: 768px) {

            .acoes {
                flex-direction: column;
            }

            .btn-sm {
                width: 100%;
                text-align: center;
            }
        }

        /* ============================= */
        /* FORMULÁRIO - MELHOR ESPAÇAMENTO */
        /* ============================= */

        .card form {
            display: flex;
            flex-direction: column;
            gap: 14px; /* espaço entre os blocos */
        }

        /* grupo label + campo */
        .card form label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 4px;
        }

        .card form input,
        .card form select,
        .card form textarea {
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            background: #f8fafc;
            transition: 0.2s;
        }

        /* efeito foco */
        .card form input:focus,
        .card form select:focus,
        .card form textarea:focus {
            outline: none;
            border-color: #ea580c;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(234,88,12,0.15);
        }

        /* textarea mais confortável */
        .card form textarea {
            min-height: 90px;
            resize: vertical;
        }

        /* botão mais separado */
        .card form button {
            margin-top: 10px;
            padding: 12px;
        }
    </style>
</head>

<?php include '../base/navbarUser.php'; ?>

<body>

    <div class="content">
        <div class="page-title">Cadastro de Planos</div>

        <div class="card">

            <form method="post">

                <input type="hidden" name="Id" value="<?= $edit['id'] ?? '' ?>">

                <label>Nome</label>
                <input name="Nome" value="<?= $edit['Nome'] ?? '' ?>" required>

                <label>Valor</label>
                <input name="Valor" id="Valor" type="text" step="0.01" value="<?= $edit['Valor'] ?? '' ?>">

                <label>Periodo</label>
                <select name="Periodo">

                    <option value="MENSAL" <?= (($edit['Periodo'] ?? '')=='MENSAL')?'selected':'' ?>>Mensal</option>
                    <option value="TRIMESTRAL" <?= (($edit['Periodo'] ?? '')=='TRIMESTRAL')?'selected':'' ?>>Trimestral
                    </option>
                    <option value="ANUAL" <?= (($edit['Periodo'] ?? '')=='ANUAL')?'selected':'' ?>>Anual</option>

                </select>

                <label>Descrição</label>
                <textarea name="Descricao"><?= $edit['Descricao'] ?? '' ?></textarea>

                <label>Status</label>
                <select name="Status">
                    <option value="1" <?= (($edit['Status'] ?? 1)==1)?'selected':'' ?>>Ativo</option>
                    <option value="0" <?= (($edit['Status'] ?? 1)==0)?'selected':'' ?>>Inativo</option>
                </select>

                <button class="btn btn">Salvar</button>

            </form>

            <?php if ($erro): ?>
            <div style="color:red"><?= $erro ?></div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
            <div style="color:green"><?= $sucesso ?></div>
            <?php endif; ?>

        </div>

        <h3>Planos Cadastrados</h3>

        <table class="table-planos">

            <tr>
                <!-- <th>ID</th> -->
                <th>Plano</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>

            <?php foreach ($planos as $p): ?>

            <tr>

                <!-- <td><?= $p['id'] ?></td> -->

                <td>
                    <strong><?= $p['Nome'] ?></strong><br>
                    <span class="plano-periodo">
                        <?= $p['Periodo'] ?>
                    </span>
                </td>

                <td style="font-weight:600;">
                    R$ <?= number_format($p['Valor'],2,',','.') ?>
                </td>

                <td>
                    <span class="badge-status <?= $p['Status'] ? 'badge-ativo' : 'badge-inativo' ?>">
                        <?= $p['Status'] ? 'Ativo' : 'Inativo' ?>
                    </span>
                </td>

                <td>

                    <div class="acoes">

                        <a class="btn btn-secondary" href="?edit=<?= $p['id'] ?>">
                            Editar
                        </a>

                        <a class="btn btn-secondary" href="?toggle=<?= $p['id'] ?>">
                            <?= $p['Status'] ? 'Inativar' : 'Ativar' ?>
                        </a>

                        <a class="btn-excluir" href="?del=<?= $p['id'] ?>" onclick="return confirm('Excluir plano?')">
                            Excluir
                        </a>

                    </div>

                </td>

            </tr>

            <?php endforeach; ?>

        </table>
    </div>
</body>

<script>
//======================================================================
function formatarMoeda(campo) {
    let v = campo.value.replace(/\D/g, '');

    v = (v / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    campo.value = v;
}

//======================================================================
document.getElementById('Valor')?.addEventListener('input', function () {
    formatarMoeda(this);
});
</script>

</html>