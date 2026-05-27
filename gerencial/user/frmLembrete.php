<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['usuario_id'])){
    header("Location: Login");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'];

$msg = '';
$tipoMsg = '';

$usuariosEmpresa = ExSqlNET("SELECT id, Nome FROM user WHERE idEmpresa = ? AND Inativo = 0 ORDER BY Nome", null,[$idEmpresa]);

$usuariosEmpresa = ExSqlNET(
    "SELECT id, Nome 
    FROM user 
    WHERE idEmpresa = ? 
    AND Inativo = 0 
    ORDER BY Nome",
    null,
    [$idEmpresa]
);

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $idUsers = $_POST['idUser'] ?? [];

    $dados = [
        'id'            => $_POST['id'] ?? 0,
        'idEmpresa'     => $idEmpresa,
        'Titulo'        => $_POST['Titulo'] ?? '',
        'Descricao'     => $_POST['Descricao'] ?? '',
        'DataLembrete'  => $_POST['DataLembrete'] ?? date('Y-m-d'),
        'Concluido'     => isset($_POST['Concluido']) ? 1 : 0,
        'idUser'        => $_SESSION['usuario_id'] ?? 0
    ];
    $erro = '';
    if(empty($dados['id'])){
        if(empty($idUsers)){
            $erro = "Selecione ao menos um usuário.";
        }else{
            foreach($idUsers as $idUser){
                $dados['idUser'] = $idUser;
                $retorno = Lembrete($dados, "CADASTRAR");
                if(!empty($retorno)){
                    $erro = $retorno;
                    break;
                }
            }
        }
    }else{
        $erro = Lembrete($dados, "ATUALIZAR");
    }
    if(empty($erro)){
        $_SESSION['mensagem_sucesso'] = 'Lembrete salvo com sucesso.';
    }else{
        $_SESSION['mensagem_erro'] = $erro;
    }
    header("Location: Lembretes");
    exit;
}

if(isset($_GET['del'])){
    $erro = Lembrete([
        'id'         => $_GET['del'],
        'idEmpresa'  => $idEmpresa,
        'idUser'     => $_SESSION['usuario_id']
    ], "EXCLUIR");
    if(empty($erro)){
        $_SESSION['mensagem_sucesso'] = 'Lembrete excluído.';
    }else{
        $_SESSION['mensagem_erro'] = $erro;
    }
    header("Location: Lembretes");
    exit;
}

$editar = null;

if(isset($_GET['edit'])){

    $editar = ExSqlNET("
        SELECT *
        FROM movtolembrete
        WHERE idEmpresa = ?
        AND id = ?
    ", null, [
        $idEmpresa,
        $_GET['edit']
    ])[0] ?? null;
}

$lembretes = ExSqlNET("
SELECT 
    movtolembrete.*,
    user.Nome AS Nome
FROM movtolembrete
LEFT JOIN user 
    ON user.id = movtolembrete.idUser
WHERE movtolembrete.idEmpresa = ?
ORDER BY 
    Concluido ASC,
    DataLembrete ASC,
    movtolembrete.id DESC
LIMIT 50
", null, [$idEmpresa]);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lembretes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <link rel="icon" href="../img/favicon.png">

    <style>
    .check-inline{
        display:flex;
        align-items:center;
        gap:8px;
        width:fit-content;
        cursor:pointer;
    }

    .check-inline input{
        width:auto !important;
        margin:0;
    }
    .content {
        padding: 30px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #334155;
    }

    .subtitle {
        color: #64748b;
        margin-top: 5px;
        margin-bottom: 25px;
    }

    .card {
        background: white;
        border-radius: 14px;
        padding: 25px;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 14px;
        margin-bottom: 6px;
        font-weight: 600;
        color: #475569;
    }

    .form-group input,
    .form-group textarea {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        font-size: 14px;
    }

    .form-group textarea {
        min-height: 120px;
        resize: vertical;
    }

    .table-modern {
        width: 100%;
        border-collapse: collapse;
    }

    .table-modern th {
        background: #fff7ed;
        color: #c2410c;
        padding: 12px;
        text-align: left;
    }

    .table-modern td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .badge {
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .badge-ok {
        background: #dcfce7;
        color: #166534;
    }

    .badge-pendente {
        background: #fef3c7;
        color: #92400e;
    }

    .actions {
        display: flex;
        gap: 8px;
    }

    @media(max-width:700px) {

        .form-grid {
            grid-template-columns: 1fr;
        }

        .table-modern {
            display: block;
            overflow: auto;
        }
    }

    .usuarios-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 10px;
        margin-bottom: 15px;
    }

    .usuario-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        cursor: pointer;
        transition: .2s;
    }

    .usuario-item:hover {
        border-color: #fb923c;
        background: #fff7ed;
    }

    .usuario-item input {
        width: 18px;
        height: 18px;
        accent-color: #ea580c;
    }
    </style>

</head>

<body>

    <?php include __DIR__ . '/../base/navbarUser.php'; ?>

    <div class="content">

        <div class="page-title">
            Lembretes
        </div>

        <div class="subtitle">
            Gerencie os lembretes do sistema
        </div>

        <?php if(isset($_SESSION['mensagem_sucesso'])): ?>

        <div class="alert success">
            <?= $_SESSION['mensagem_sucesso'] ?>
        </div>

        <?php unset($_SESSION['mensagem_sucesso']); endif; ?>

        <?php if(isset($_SESSION['mensagem_erro'])): ?>

        <div class="alert error">
            <?= $_SESSION['mensagem_erro'] ?>
        </div>

        <?php unset($_SESSION['mensagem_erro']); endif; ?>

        <div class="card">

            <form method="post">

                <input type="hidden" name="id" value="<?= $editar['id'] ?? 0 ?>">

                <div class="form-grid">

                    <div class="form-group">
                        <label>Título</label>

                        <input type="text" name="Titulo" required
                            value="<?= htmlspecialchars($editar['Titulo'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Data do Lembrete</label>

                        <input type="date" name="DataLembrete" required
                            value="<?= $editar['DataLembrete'] ?? date('Y-m-d') ?>">
                    </div>

                    <div class="form-group full">
                        <label>Descrição</label>

                        <textarea name="Descricao"><?= htmlspecialchars($editar['Descricao'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group full">
                        <label class="check-inline">
                            <input type="checkbox" name="Concluido" <?= ($editar['Concluido'] ?? 0) == 1 ? 'checked' : '' ?>>
                            <span>Lembrete concluído</span>
                        </label>
                    </div>
                </div>

                <label>Usuários do Lembrete</label>

                <div class="usuarios-grid">
                    <label class="usuario-item">
                        <input type="checkbox" name="idUser[]" value="<?= $_SESSION['usuario_id'] ?>" checked>
                        <span>Eu mesmo</span>
                    </label>

                    <?php foreach($usuariosEmpresa as $user): ?>
                    <?php if($user['id'] == $_SESSION['usuario_id']) continue; ?>
                    <label class="usuario-item">

                        <input type="checkbox" name="idUser[]" value="<?= $user['id'] ?>">
                        <span><?= htmlspecialchars($user['Nome']) ?></span>
                    </label>
                    <?php endforeach; ?>

                </div>

                <div style="margin-top:20px; display:flex; gap:10px;">

                    <button type="submit" class="btn">
                        Salvar
                    </button>

                    <?php if($editar): ?>

                    <a href="Lembrete" class="btn">
                        Cancelar
                    </a>

                    <?php endif; ?>

                </div>

            </form>

        </div>

        <div class="card">
            <table class="table-modern">
                <thead>

                    <tr>
                        <th>Título</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Usuário</th>
                        <th width="160">Ações</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach($lembretes as $lem): ?>

                    <tr>

                        <td>
                            <strong>
                                <?= htmlspecialchars($lem['Titulo']) ?>
                            </strong>

                            <?php if(!empty($lem['Descricao'])): ?>

                            <div style="margin-top:6px; color:#64748b; font-size:13px;">
                                <?= nl2br(htmlspecialchars($lem['Descricao'])) ?>
                            </div>

                            <?php endif; ?>
                        </td>

                        <td>
                            <?= date('d/m/Y', strtotime($lem['DataLembrete'])) ?>
                        </td>

                        <td>
                            <?php if($lem['Concluido']): ?>
                            <span class="badge badge-ok">
                                Concluído
                            </span>
                            <?php else: ?>
                            <span class="badge badge-pendente">
                                Pendente
                            </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong>
                                <?= htmlspecialchars($lem['Nome']) ?>
                            </strong>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="?edit=<?= $lem['id'] ?>" class="btn btn-editar">
                                    Editar
                                </a>
                                <a href="?del=<?= $lem['id'] ?>" class="btn-excluir"
                                    onclick="return confirm('Excluir lembrete?')">
                                    Excluir
                                </a>
                            </div>
                        </td>
                    </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</body>

</html>