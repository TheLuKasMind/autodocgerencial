<?php

require_once '../base/connection.php';
include '../base/baseFuncoes.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'] ?? 0;
$idEditar = $_GET['id'] ?? null;
$grupoEditar = null;

if (isset($_GET['excluir'])) {

    $idExcluir = (int) $_GET['excluir'];

    ExSqlNET("
        DELETE FROM grupos
        WHERE Id = ? AND idEmpresa = ?
    ", null, [$idExcluir, $idEmpresa]);

    header("Location: " . $_SERVER['PHP_SELF'] . "?excluido=1");
    exit;
}


if ($idEditar) {
    $dados = ExSqlNET("
        SELECT * FROM grupos WHERE Id = ? AND idEmpresa = ?
    ", null, [$idEditar, $idEmpresa]);

    if ($dados) {
        $grupoEditar = $dados[0];
    }
}

$grupos = ExSqlNET("
    SELECT Nome, Id, Tipo, Inativo
    FROM grupos
    WHERE idEmpresa = ?
    ORDER BY Nome
", null, [$idEmpresa]);


if ($idEditar) {
    $dados = ExSqlNET("
        SELECT * FROM grupos WHERE Id = ? AND idEmpresa = ?
    ", null, [$idEditar, $idEmpresa]);

    if ($dados) {
        $grupoEditar = $dados[0];
    }
}

$msg = "";

/* ================= SALVAR ================= */

$msg = "";
$erro = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id     = $_POST['id'] ?? null;
    $nome   = trim($_POST['nomeGrupo'] ?? '');
    $tipo   = $_POST['tipoGrupo'] ?? '';
    $inativo = isset($_POST['inativo']) ? 1 : 0;

    if ($nome && $tipo) {

        if ($id) {

            ExSqlNET("
                UPDATE grupos
                SET Nome = ?, Tipo = ?, Inativo = ?
                WHERE Id = ? AND idEmpresa = ?
            ", null, [$nome, $tipo, $inativo, $id, $idEmpresa]);

            header("Location: " . $_SERVER['PHP_SELF'] . "?editado=1");
        } else {

            ExSqlNET("
                INSERT INTO grupos (Nome, Tipo, Inativo, idEmpresa)
                VALUES (?, ?, ?, ?)
            ", null, [$nome, $tipo, $inativo, $idEmpresa]);

            header("Location: " . $_SERVER['PHP_SELF'] . "?sucesso=1");
        }

        exit;
    }
}

$msg = "";
$erro = false;

if (isset($_GET['sucesso'])) $msg = "Grupo cadastrado com sucesso!";
if (isset($_GET['editado'])) $msg = "Grupo atualizado com sucesso!";
if (isset($_GET['excluido'])) $msg = "Grupo excluído com sucesso!";

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro de Grupos</title>

<link rel="stylesheet" href="../css/base.css?v=15">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../img/favicon.png">
   
<style>
/* ===== BASE ===== */

body {
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    color: #1e293b;
    background: #f8fafc;
}

/* ===== CONTAINERS ===== */

.box {
    background: #ffffff;
    padding: 26px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    margin-bottom: 22px;
    border: 1px solid #f1f5f9;
}

.titulo {
    font-size: 19px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #1e293b;
}

/* ===== FORM ===== */

label {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 6px;
    font-weight: 500;
}

input[type="text"],
select {
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    font-size: 14px;
    transition: all 0.2s ease;
    background: #f8fafc;
}

input:hover,
select:hover {
    border-color: #cbd5f5;
}

input:focus,
select:focus {
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249,115,22,0.12);
    outline: none;
    background: #fff;
}

/* ===== GRID ===== */

.linha {
    display: flex;
    gap: 18px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.campo {
    display: flex;
    flex-direction: column;
}

.campo.nome {
    flex: 2;
    min-width: 260px;
}

.campo.tipo {
    flex: 1;
    min-width: 200px;
}

.campo.check {
    flex-direction: row;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    font-size: 14px;
}

/* ===== BOTÕES ===== */

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 13px 22px;
    border-radius: 10px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
    transition: all 0.2s ease;
    letter-spacing: 0.2px;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(249,115,22,0.25);
}

.btn:active {
    transform: scale(0.98);
}

.btn-sm {
    padding: 8px 14px;
    font-size: 13px;
    border-radius: 8px;
}

/* excluir */
.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.btn-danger:hover {
    box-shadow: 0 8px 18px rgba(239,68,68,0.25);
}

/* ===== ALERTA ===== */

.msg {
    background: #f97316;
    color: white;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 22px;
    font-size: 14px;
    box-shadow: 0 4px 10px rgba(249,115,22,0.2);
}

.msg.erro {
    background: #ef4444;
}

/* ===== TABELA ===== */

.tabela {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.tabela thead {
    background: #fff7ed;
}

.tabela th {
    padding: 14px 12px;
    text-align: left;
    color: #9a3412;
    font-weight: 600;
    font-size: 13px;
    letter-spacing: 0.3px;
}

.tabela td {
    padding: 14px 12px;
    border-bottom: 1px solid #f1f5f9;
}

.tabela tbody tr {
    transition: all 0.15s ease;
}

.tabela tbody tr:hover {
    background: #fff7ed;
}

/* ===== BADGES ===== */

.badge {
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.badge.ativo {
    background: #dcfce7;
    color: #166534;
}

.badge.inativo {
    background: #fee2e2;
    color: #991b1b;
}

/* ===== AÇÕES ===== */

.td-acoes {
    text-align: center;
}

.acoes {
    display: flex;
    justify-content: center;
    gap: 10px;
}

/* ===== RESPONSIVO ===== */

@media (max-width: 768px) {

    .linha {
        flex-direction: column;
        align-items: stretch;
    }

    .campo.nome,
    .campo.tipo {
        width: 100%;
    }

    .btn {
        width: 100%;
    }

    .acoes {
        flex-direction: column;
    }
}
</style>
</head>

<body>

<?php include '../base/navbarUser.php'; ?>

<div class="content">

    <h1 class="page-title">Cadastro de Grupos</h1>

    <?php if ($msg): ?>
        <div class="msg <?= $erro ? 'erro' : '' ?>">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="box">
            <div class="titulo">Dados do Grupo</div>
        
            <div class="linha">
        
                <div class="campo nome">
                    <label>Nome do Grupo</label>
                    <input type="text" name="nomeGrupo" required
                        value="<?= $grupoEditar['Nome'] ?? '' ?>">
                </div>
        
                <div class="campo tipo">
                    <label>Tipo de Grupo</label>
                    <select name="tipoGrupo">
                        <option value="">Selecione</option>
                        <option value="P" <?= ($grupoEditar['Tipo'] ?? '') == 'P' ? 'selected' : '' ?>>Produtos / Serviços</option>
                        <option value="C" <?= ($grupoEditar['Tipo'] ?? '') == 'C' ? 'selected' : '' ?>>Clientes</option>
                    </select>
                </div>
        
                <div class="campo check">
                    <input type="checkbox" name="inativo" id="inativo"
<?= ($grupoEditar['Inativo'] ?? 0) ? 'checked' : '' ?>>
                    <label for="inativo">Inativo</label>
                </div>
        
            </div>
        </div>
        
        <input type="hidden" name="id" value="<?= $grupoEditar['Id'] ?? '' ?>">

        <button type="submit" class="btn">
            <?= $grupoEditar ? 'Atualizar Grupo' : 'Cadastrar Grupo' ?>
        </button>

    </form>

    <div class="box">
        <div class="titulo">Grupos Cadastrados</div>
    
        <table class="tabela">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th style="width:160px; text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
        
                <?php if ($grupos): ?>
                    <?php foreach ($grupos as $g): ?>
                        <tr>
                            <td><?= htmlspecialchars($g['Nome']) ?></td>
        
                            <td>
                                <?= $g['Tipo'] == 'P' ? 'Produtos / Serviços' : 'Clientes' ?>
                            </td>
        
                            <td>
                                <?php if ($g['Inativo']): ?>
                                    <span class="badge inativo">Inativo</span>
                                <?php else: ?>
                                    <span class="badge ativo">Ativo</span>
                                <?php endif; ?>
                            </td>
        
                             <td class="td-acoes">
                                <div class="acoes">
                                    <a href="?id=<?= $g['Id'] ?>" class="btn btn-sm">Editar</a>
                            
                                    <a href="?excluir=<?= $g['Id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Tem certeza que deseja excluir este grupo?')">
                                       Excluir
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;">
                            Nenhum grupo cadastrado
                        </td>
                    </tr>
                <?php endif; ?>
        
            </tbody>
        </table>
    </div>

</div>

</body>
</html>