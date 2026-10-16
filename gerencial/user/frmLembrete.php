<?php
require_once __DIR__ . '/../base/connection.php';
require_once __DIR__ . '/../base/baseFuncoes.php';
require_once __DIR__ . '/../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: Login');
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'];
$idUsuarioLogado = (int)$_SESSION['usuario_id'];

$usuariosEmpresa = ExSqlNET(
    'SELECT id, Nome FROM user WHERE idEmpresa = ? AND Inativo = 0 ORDER BY Nome',
    null,
    [$idEmpresa]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $idUsers = $_POST['idUser'] ?? [];

    $dados = [
        'id'           => $id,
        'idEmpresa'    => $idEmpresa,
        'Titulo'       => trim($_POST['Titulo'] ?? ''),
        'Descricao'    => trim($_POST['Descricao'] ?? ''),
        'DataLembrete' => $_POST['DataLembrete'] ?? date('Y-m-d'),
        'Concluido'    => isset($_POST['Concluido']) ? 1 : 0,
        'idUser'       => $idUsuarioLogado,
    ];

    $erro = '';

    if ($dados['Titulo'] === '') {
        $erro = 'Informe o título do lembrete.';
    } elseif ($id === 0) {
        $idUsers = array_values(array_unique(array_filter(array_map('intval', (array)$idUsers), static fn($v) => $v > 0)));
        if (empty($idUsers)) {
            $erro = 'Selecione ao menos um usuário.';
        } else {
            foreach ($idUsers as $idUser) {
                // Confirma que o usuário selecionado pertence à empresa atual e está ativo.
                $usuarioValido = ExSqlNET(
                    'SELECT id FROM user WHERE id = ? AND idEmpresa = ? AND Inativo = 0',
                    null,
                    [$idUser, $idEmpresa]
                );
                if (empty($usuarioValido)) {
                    continue;
                }

                $dados['idUser'] = $idUser;
                $retorno = Lembrete($dados, 'CADASTRAR');
                if (!empty($retorno)) {
                    $erro = $retorno;
                    break;
                }
            }
        }
    } else {
        // Mantém o usuário associado ao lembrete ao editar, sem transferir a propriedade para quem está logado.
        $lembreteExistente = ExSqlNET(
            'SELECT idUser FROM movtolembrete WHERE id = ? AND idEmpresa = ?',
            null,
            [$id, $idEmpresa]
        );
        if (empty($lembreteExistente)) {
            $erro = 'Lembrete não encontrado.';
        } else {
            $dados['idUser'] = (int)$lembreteExistente[0]['idUser'];
            $erro = Lembrete($dados, 'ATUALIZAR');
        }
    }

    if (empty($erro)) {
        $_SESSION['mensagem_sucesso'] = 'Lembrete salvo com sucesso.';
    } else {
        $_SESSION['mensagem_erro'] = $erro;
    }

    header('Location: Lembretes');
    exit;
}

if (isset($_GET['del'])) {
    $idExcluir = (int)$_GET['del'];
    $erro = Lembrete([
        'id'        => $idExcluir,
        'idEmpresa' => $idEmpresa,
        'idUser'    => $idUsuarioLogado,
    ], 'EXCLUIR');

    if (empty($erro)) {
        $_SESSION['mensagem_sucesso'] = 'Lembrete excluído.';
    } else {
        $_SESSION['mensagem_erro'] = $erro;
    }

    header('Location: Lembretes');
    exit;
}

$editar = null;
if (isset($_GET['edit'])) {
    $resultadoEdicao = ExSqlNET(
        'SELECT movtolembrete.*, user.Nome AS NomeUsuario
         FROM movtolembrete
         LEFT JOIN user ON user.id = movtolembrete.idUser
         WHERE movtolembrete.idEmpresa = ? AND movtolembrete.id = ?',
        null,
        [$idEmpresa, (int)$_GET['edit']]
    );
    $editar = $resultadoEdicao[0] ?? null;
}

$lembretes = ExSqlNET(
    'SELECT movtolembrete.*, user.Nome AS NomeUsuario
     FROM movtolembrete
     LEFT JOIN user ON user.id = movtolembrete.idUser
     WHERE movtolembrete.idEmpresa = ?
     ORDER BY movtolembrete.Concluido ASC, movtolembrete.DataLembrete ASC, movtolembrete.id DESC
     LIMIT 50',
    null,
    [$idEmpresa]
);

function h($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $editar ? 'Editar Lembrete' : 'Cadastro de Lembretes' ?> | Autodoc Gerencial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <style>
        .content{width:min(1180px,calc(100% - 40px));max-width:1180px;margin:30px auto 48px;padding:0}
        .page-title{font-size:28px;line-height:1.25;font-weight:750;letter-spacing:-.7px;color:#0f172a;margin:0 0 10px}
        .page-subtitle{font-size:14px;line-height:1.6;color:#64748b;margin:0 0 22px;padding-top:2px}
        .content .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 4px 18px rgba(15,23,42,.045);overflow:hidden;margin-bottom:20px}
        .section-heading{display:flex;align-items:center;gap:10px;margin:0 0 22px;color:#1e293b;font-size:16px;font-weight:750;line-height:1.4}
        .section-heading:before{content:"";display:block;width:4px;height:22px;border-radius:5px;background:#f97316;flex:0 0 4px}
        .section-description{margin:-12px 0 22px;color:#64748b;font-size:13px;line-height:1.6}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 18px}
        .form-grid>div{min-width:0}
        .content label.field-label{display:block;margin:0 0 7px;color:#334155;font-size:13px;font-weight:650}
        .content input:not([type="checkbox"]),.content textarea{box-sizing:border-box;width:100%;min-height:43px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-family:inherit;font-size:14px;line-height:1.5;outline:none;transition:border-color .18s,box-shadow .18s}
        .content input:not([type="checkbox"]):focus,.content textarea:focus{border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,.13)}
        .content textarea{min-height:120px;resize:vertical}
        .content input[type="checkbox"]{width:17px;height:17px;accent-color:#ea580c;cursor:pointer;margin:0;flex:0 0 auto}
        .content .checkbox{display:flex;align-items:center;gap:9px;min-height:43px;padding:0 2px}
        .content .checkbox label{margin:0;cursor:pointer;font-weight:550;color:#475569}
        .usuarios-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;margin-top:10px}
        .usuario-item{display:flex;align-items:center;gap:10px;min-width:0;background:#f8fafc;border:1px solid #e2e8f0;border-radius:11px;padding:12px;cursor:pointer;transition:border-color .18s,background .18s}
        .usuario-item:hover{border-color:#fb923c;background:#fff7ed}
        .usuario-item span{font-size:13px;color:#334155;overflow-wrap:anywhere}
        .content .actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:20px}
        .content .btn,.content .btn-secondary,.content .btn-excluir{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:10px 16px;border:1px solid transparent;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;cursor:pointer;transition:transform .15s,box-shadow .15s,background .15s;box-sizing:border-box}
        .content .btn{background:#f97316;color:#fff;box-shadow:0 3px 8px rgba(249,115,22,.18)}
        .content .btn:hover{background:#ea580c;box-shadow:0 5px 12px rgba(249,115,22,.23);transform:translateY(-1px)}
        .content .btn-secondary{background:#fff;color:#334155;border-color:#cbd5e1}
        .content .btn-secondary:hover{background:#f8fafc;border-color:#94a3b8}
        .content .btn-excluir{background:#fff;color:#dc2626;border-color:#fecaca}
        .content .btn-excluir:hover{background:#fef2f2;border-color:#fca5a5}
        .content .alert{padding:13px 16px;border-radius:11px;border:1px solid #e2e8f0;font-size:14px;margin-bottom:18px}
        .content .alert.success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
        .content .alert.error{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
        .status-note{margin-top:16px;padding:12px 14px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;font-size:13px;line-height:1.55}
        .table-wrap{width:100%;overflow-x:auto;border:1px solid #e2e8f0;border-radius:12px}
        .table-modern{width:100%;min-width:760px;border-collapse:separate;border-spacing:0}
        .table-modern th{background:#fff7ed;color:#c2410c;padding:13px 14px;text-align:left;font-size:12px;font-weight:750;letter-spacing:.25px;white-space:nowrap}
        .table-modern td{padding:14px;border-bottom:1px solid #edf2f7;color:#334155;font-size:13px;vertical-align:top}
        .table-modern tbody tr:last-child td{border-bottom:0}
        .table-modern tbody tr:hover{background:#fffdfa}
        .lembrete-titulo{font-weight:750;color:#0f172a;font-size:14px}
        .lembrete-descricao{margin-top:6px;color:#64748b;font-size:12px;line-height:1.5;white-space:normal;overflow-wrap:anywhere;max-width:380px}
        .badge{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:750;white-space:nowrap}
        .badge-ok{background:#dcfce7;color:#166534}
        .badge-pendente{background:#fef3c7;color:#92400e}
        .table-actions{display:flex;gap:7px;align-items:center;flex-wrap:wrap}
        .table-actions .btn,.table-actions .btn-excluir{min-height:34px;padding:7px 10px;font-size:12px;border-radius:8px}
        .empty-state{text-align:center;color:#64748b;padding:30px 16px!important}
        @media(max-width:850px){.content{width:calc(100% - 28px);margin-top:22px}.content .card{padding:20px}}
        @media(max-width:560px){.content{width:calc(100% - 22px);margin:16px auto 30px}.page-title{font-size:23px;margin-bottom:8px}.page-subtitle{padding-top:3px;margin-bottom:17px}.content .card{padding:16px;border-radius:13px}.form-grid{grid-template-columns:1fr;gap:16px}.usuarios-grid{grid-template-columns:1fr}.content .actions{align-items:stretch;flex-direction:column}.content .actions>*{width:100%;box-sizing:border-box}.table-modern th,.table-modern td{padding:11px}}
    </style>
</head>
<body>
<?php include __DIR__ . '/../base/navbarUser.php'; ?>
<div class="content">
    <div class="page-title"><?= $editar ? 'Editar Lembrete' : 'Cadastro de Lembretes' ?></div>
    <div class="page-subtitle"><?= $editar ? 'Atualize as informações deste lembrete.' : 'Organize tarefas e acompanhe os lembretes da sua equipe.' ?></div>

    <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
        <div class="alert success"><?= h($_SESSION['mensagem_sucesso']) ?></div>
        <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensagem_erro'])): ?>
        <div class="alert error"><?= h($_SESSION['mensagem_erro']) ?></div>
        <?php unset($_SESSION['mensagem_erro']); ?>
    <?php endif; ?>

    <form method="post" class="card">
        <h2 class="section-heading">Informações do lembrete</h2>
        <p class="section-description">Informe o título, a data e os detalhes do lembrete.</p>
        <input type="hidden" name="id" value="<?= (int)($editar['id'] ?? 0) ?>">

        <div class="form-grid">
            <div>
                <label class="field-label" for="Titulo">Título do lembrete</label>
                <input type="text" id="Titulo" name="Titulo" maxlength="200" placeholder="Ex.: Ligar para o cliente" value="<?= h($editar['Titulo'] ?? '') ?>" required>
            </div>
            <div>
                <label class="field-label" for="DataLembrete">Data do lembrete</label>
                <input type="date" id="DataLembrete" name="DataLembrete" value="<?= h($editar['DataLembrete'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div style="grid-column:1/-1">
                <label class="field-label" for="Descricao">Descrição</label>
                <textarea id="Descricao" name="Descricao" placeholder="Adicione detalhes ou informações importantes..."><?= h($editar['Descricao'] ?? '') ?></textarea>
            </div>
            <div class="checkbox">
                <input type="checkbox" id="Concluido" name="Concluido" value="1" <?= ((int)($editar['Concluido'] ?? 0) === 1) ? 'checked' : '' ?>>
                <label for="Concluido">Marcar lembrete como concluído</label>
            </div>
        </div>

        <div style="margin-top:24px">
            <h2 class="section-heading" style="margin-bottom:8px">Usuários do lembrete</h2>
            <?php if ($editar): ?>
                <div class="status-note" style="margin-top:0">Este lembrete está associado a <strong><?= h($editar['NomeUsuario'] ?? 'usuário não identificado') ?></strong>. Para manter o vínculo atual, a edição não altera o usuário responsável.</div>
            <?php else: ?>
                <p class="section-description" style="margin:0 0 12px">Selecione uma ou mais pessoas que devem receber este lembrete.</p>
                <div class="usuarios-grid">
                    <?php foreach ($usuariosEmpresa as $user): ?>
                        <label class="usuario-item">
                            <input type="checkbox" name="idUser[]" value="<?= (int)$user['id'] ?>" <?= ((int)$user['id'] === $idUsuarioLogado) ? 'checked' : '' ?>>
                            <span><?= h(((int)$user['id'] === $idUsuarioLogado) ? 'Eu mesmo' : $user['Nome']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="actions">
            <a href="Lembretes" class="btn-secondary">← Limpar / Listar</a>
            <?php if ($editar): ?>
                <a href="?del=<?= (int)$editar['id'] ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir este lembrete? Essa ação não pode ser desfeita.')">🗑 Excluir</a>
            <?php endif; ?>
            <button type="submit" class="btn">💾 Salvar lembrete</button>
        </div>
    </form>

    <div class="card">
        <h2 class="section-heading">Lembretes cadastrados</h2>
        <p class="section-description">Acompanhe os próximos lembretes e edite ou exclua quando necessário.</p>
        <div class="table-wrap">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Lembrete</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Usuário</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($lembretes)): ?>
                    <tr><td colspan="5" class="empty-state">Nenhum lembrete cadastrado ainda.</td></tr>
                <?php else: ?>
                    <?php foreach ($lembretes as $lem): ?>
                        <tr>
                            <td>
                                <div class="lembrete-titulo"><?= h($lem['Titulo'] ?? '') ?></div>
                                <?php if (!empty($lem['Descricao'])): ?>
                                    <div class="lembrete-descricao"><?= nl2br(h($lem['Descricao'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= !empty($lem['DataLembrete']) ? date('d/m/Y', strtotime($lem['DataLembrete'])) : '—' ?></td>
                            <td>
                                <?php if ((int)($lem['Concluido'] ?? 0) === 1): ?>
                                    <span class="badge badge-ok">Concluído</span>
                                <?php else: ?>
                                    <span class="badge badge-pendente">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td><?= h($lem['NomeUsuario'] ?? '—') ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="?edit=<?= (int)$lem['id'] ?>" class="btn-secondary">Editar</a>
                                    <a href="?del=<?= (int)$lem['id'] ?>" class="btn-excluir" onclick="return confirm('Excluir este lembrete?')">Excluir</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="status-note">A listagem exibe até 50 lembretes por vez.</div>
    </div>
</div>
</body>
</html>
