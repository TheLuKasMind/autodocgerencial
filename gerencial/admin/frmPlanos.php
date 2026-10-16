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

if (!isset($_SESSION['AdminGeral']) || (int)$_SESSION['AdminGeral'] !== 1) {
    header('Location: Login');
    exit;
}

global $dbGeralNET;

$erro = '';
$sucesso = '';

// Mensagens que continuam visíveis após redirecionamentos.
if (isset($_SESSION['flash_sucesso'])) {
    $sucesso = (string)$_SESSION['flash_sucesso'];
    unset($_SESSION['flash_sucesso']);
}

function h($valor): string {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/* ================= SALVAR ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim((string)($_POST['Id'] ?? ''));
    $nome = trim((string)($_POST['Nome'] ?? ''));
    $valorInformado = trim((string)($_POST['Valor'] ?? '0'));
    $valor = (float)str_replace(',', '.', str_replace('.', '', $valorInformado));
    $periodo = (string)($_POST['Periodo'] ?? 'MENSAL');
    $descricao = trim((string)($_POST['Descricao'] ?? ''));
    $status = (int)($_POST['Status'] ?? 1);

    if ($nome === '') {
        $erro = 'Informe o nome do plano.';
    } elseif (!in_array($periodo, ['MENSAL', 'TRIMESTRAL', 'ANUAL'], true)) {
        $erro = 'Selecione um período válido.';
    } elseif (!in_array($status, [0, 1], true)) {
        $erro = 'Selecione um status válido.';
    } elseif ($valor < 0) {
        $erro = 'O valor não pode ser negativo.';
    } else {
        if ($id !== '') {
            $sql = 'UPDATE planos SET Nome=?, Valor=?, Periodo=?, Descricao=?, Status=? WHERE Id=?';
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([$nome, $valor, $periodo, $descricao, $status, $id]);
            $_SESSION['flash_sucesso'] = 'Plano atualizado com sucesso.';
            header('Location: Planos');
            exit;
        } else {
            $sql = 'INSERT INTO planos (Nome, Valor, Periodo, Descricao, Status) VALUES (?, ?, ?, ?, ?)';
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([$nome, $valor, $periodo, $descricao, $status]);
            $_SESSION['flash_sucesso'] = 'Plano cadastrado com sucesso.';
            header('Location: Planos');
            exit;
        }
    }
}

/* ================= AÇÕES ================= */
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $stmt = $dbGeralNET->prepare('DELETE FROM planos WHERE Id=?');
    $stmt->execute([$id]);
    $_SESSION['flash_sucesso'] = 'Plano excluído com sucesso.';
    header('Location: Planos');
    exit;
}

if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $dbGeralNET->prepare('UPDATE planos SET Status = CASE WHEN Status=1 THEN 0 ELSE 1 END WHERE Id=?');
    $stmt->execute([$id]);
    $_SESSION['flash_sucesso'] = 'Status do plano atualizado com sucesso.';
    header('Location: Planos');
    exit;
}

/* ================= EDITAR ================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $dados = ExSqlNET('SELECT * FROM planos WHERE Id=?', null, [$id]);
    if ($dados) {
        $edit = $dados[0];
    }
}

/* ================= LISTA ================= */
$planos = ExSqlNET('SELECT * FROM planos ORDER BY Id DESC') ?: [];
$valorEdit = $edit['Valor'] ?? '';
$valorEditFormatado = $valorEdit !== '' ? number_format((float)$valorEdit, 2, ',', '.') : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $edit ? 'Editar Plano' : 'Cadastro de Planos' ?> | Autodoc Gerencial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <style>
        .content{width:min(1180px,calc(100% - 40px));max-width:1180px;margin:30px auto 48px;padding:0}
        .page-title{font-size:28px;line-height:1.25;font-weight:750;letter-spacing:-.7px;color:#0f172a;margin:0 0 8px}
        .page-subtitle{font-size:14px;line-height:1.6;color:#64748b;margin:0 0 24px}
        .content .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 4px 18px rgba(15,23,42,.045);overflow:hidden;margin-bottom:24px}
        .section-heading{display:flex;align-items:center;gap:10px;margin:0 0 8px;color:#1e293b;font-size:16px;font-weight:750;line-height:1.4}
        .section-heading:before{content:"";display:block;width:4px;height:22px;border-radius:5px;background:#f97316;flex:0 0 4px}
        .section-description{margin:0 0 22px;color:#64748b;font-size:13px;line-height:1.6}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 18px}
        .form-grid>div{min-width:0}
        .form-grid .wide{grid-column:1/-1}
        .content label{display:block;margin:0 0 7px;color:#334155;font-size:13px;font-weight:650}
        .content input,.content select,.content textarea{box-sizing:border-box;width:100%;min-height:43px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-family:inherit;font-size:14px;line-height:1.4;outline:none;transition:border-color .18s,box-shadow .18s}
        .content input:focus,.content select:focus,.content textarea:focus{border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,.13)}
        .content textarea{min-height:100px;resize:vertical}
        .form-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:22px}
        .content .btn,.content .btn-secondary,.content .btn-danger,.content .btn-toggle{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:9px 14px;border:1px solid transparent;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer;transition:background .15s,border-color .15s,transform .15s;box-sizing:border-box;white-space:nowrap}
        .content .btn{background:#f97316;color:#fff;box-shadow:0 3px 8px rgba(249,115,22,.18)}
        .content .btn:hover{background:#ea580c;transform:translateY(-1px)}
        .content .btn-secondary{background:#fff;color:#334155;border-color:#cbd5e1}
        .content .btn-secondary:hover{background:#f8fafc;border-color:#94a3b8}
        .content .btn-danger{background:#fff;color:#dc2626;border-color:#fecaca}
        .content .btn-danger:hover{background:#fef2f2;border-color:#fca5a5}
        .content .btn-toggle{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
        .content .btn-toggle:hover{background:#dbeafe}
        .alert{padding:13px 16px;border-radius:11px;border:1px solid #e2e8f0;font-size:14px;margin-bottom:18px}
        .alert.success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
        .alert.error{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
        .list-header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:30px 0 14px}
        .list-header h2{margin:0;color:#0f172a;font-size:19px;font-weight:750}
        .count{display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:26px;padding:0 8px;border-radius:999px;background:#fff7ed;color:#c2410c;font-size:12px;font-weight:750}
        .table-wrap{width:100%;overflow-x:auto;border:1px solid #e2e8f0;border-radius:14px;background:#fff}
        .table-planos{width:100%;border-collapse:collapse;min-width:720px}
        .table-planos th{padding:14px 16px;background:#f8fafc;color:#64748b;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.55px;font-weight:750;border-bottom:1px solid #e2e8f0}
        .table-planos td{padding:16px;border-bottom:1px solid #f1f5f9;vertical-align:middle;color:#334155;font-size:14px}
        .table-planos tr:last-child td{border-bottom:0}
        .table-planos tbody tr:hover{background:#fffdfa}
        .plano-nome{font-weight:750;color:#0f172a;margin-bottom:4px}
        .plano-descricao{font-size:12px;color:#64748b;line-height:1.5;max-width:300px;overflow-wrap:anywhere}
        .plano-periodo{display:inline-block;font-size:12px;color:#64748b;background:#f1f5f9;border-radius:6px;padding:4px 7px;margin-top:3px}
        .plano-valor{font-weight:750;color:#ea580c;font-size:15px;white-space:nowrap}
        .badge-status{padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700;display:inline-block}
        .badge-ativo{background:#dcfce7;color:#166534}
        .badge-inativo{background:#fee2e2;color:#991b1b}
        .acoes{display:flex;gap:6px;flex-wrap:wrap}
        .empty-state{text-align:center;padding:36px 20px;color:#64748b;font-size:14px}
        @media(max-width:850px){.content{width:calc(100% - 28px);margin-top:22px}.content .card{padding:20px}}
        @media(max-width:560px){.content{width:calc(100% - 22px);margin:16px auto 30px}.page-title{font-size:23px}.page-subtitle{margin-bottom:18px}.content .card{padding:16px;border-radius:13px}.form-grid{grid-template-columns:1fr;gap:16px}.form-grid .wide{grid-column:auto}.form-actions{align-items:stretch;flex-direction:column}.form-actions>*{width:100%}.list-header{align-items:flex-start}.table-wrap{border-radius:12px}}
    </style>
</head>
<body>
<?php include __DIR__ . '/../base/navbarUser.php'; ?>
<div class="content">
    <div class="page-title"><?= $edit ? 'Editar Plano' : 'Cadastro de Planos' ?></div>
    <div class="page-subtitle">Gerencie os planos, valores e períodos disponíveis no sistema.</div>

    <?php if ($erro !== ''): ?><div class="alert error" role="alert"><?= h($erro) ?></div><?php endif; ?>
    <?php if ($sucesso !== ''): ?><div class="alert success" role="status"><?= h($sucesso) ?></div><?php endif; ?>

    <div class="card">
        <h2 class="section-heading"><?= $edit ? 'Informações do plano' : 'Novo plano' ?></h2>
        <p class="section-description"><?= $edit ? 'Atualize os dados e salve as alterações.' : 'Preencha os dados abaixo para cadastrar um plano.' ?></p>
        <form method="post" action="<?= $edit ? '?edit=' . rawurlencode((string)($edit['Id'] ?? $edit['id'] ?? '')) : 'Planos' ?>">
            <input type="hidden" name="Id" value="<?= h($edit['Id'] ?? $edit['id'] ?? '') ?>">
            <div class="form-grid">
                <div>
                    <label for="Nome">Nome do plano</label>
                    <input type="text" id="Nome" name="Nome" maxlength="150" placeholder="Ex.: Plano Profissional" value="<?= h($edit['Nome'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="Valor">Valor (R$)</label>
                    <input type="text" id="Valor" name="Valor" inputmode="decimal" placeholder="0,00" value="<?= h($valorEditFormatado) ?>" required>
                </div>
                <div>
                    <label for="Periodo">Período de cobrança</label>
                    <select id="Periodo" name="Periodo" required>
                        <option value="MENSAL" <?= (($edit['Periodo'] ?? 'MENSAL') === 'MENSAL') ? 'selected' : '' ?>>Mensal</option>
                        <option value="TRIMESTRAL" <?= (($edit['Periodo'] ?? '') === 'TRIMESTRAL') ? 'selected' : '' ?>>Trimestral</option>
                        <option value="ANUAL" <?= (($edit['Periodo'] ?? '') === 'ANUAL') ? 'selected' : '' ?>>Anual</option>
                    </select>
                </div>
                <div>
                    <label for="Status">Status do plano</label>
                    <select id="Status" name="Status">
                        <option value="1" <?= ((int)($edit['Status'] ?? 1) === 1) ? 'selected' : '' ?>>Ativo</option>
                        <option value="0" <?= ((int)($edit['Status'] ?? 1) === 0) ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
                <div class="wide">
                    <label for="Descricao">Descrição</label>
                    <textarea id="Descricao" name="Descricao" placeholder="Descreva o que está incluído neste plano..."><?= h($edit['Descricao'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <?php if ($edit): ?><a class="btn-secondary" href="Planos">Cancelar edição</a><?php endif; ?>
                <button class="btn" type="submit">💾 <?= $edit ? 'Salvar alterações' : 'Cadastrar plano' ?></button>
            </div>
        </form>
    </div>

    <div class="list-header">
        <h2>Planos cadastrados</h2>
        <span class="count"><?= count($planos) ?> <?= count($planos) === 1 ? 'plano' : 'planos' ?></span>
    </div>

    <div class="table-wrap">
        <?php if (!$planos): ?>
            <div class="empty-state">Nenhum plano cadastrado ainda.</div>
        <?php else: ?>
        <table class="table-planos">
            <thead><tr><th>Plano</th><th>Valor</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($planos as $p): ?>
                <?php $planoId = $p['Id'] ?? $p['id'] ?? ''; ?>
                <tr>
                    <td>
                        <div class="plano-nome"><?= h($p['Nome'] ?? '') ?></div>
                        <span class="plano-periodo"><?= h($p['Periodo'] ?? '') ?></span>
                        <?php if (trim((string)($p['Descricao'] ?? '')) !== ''): ?><div class="plano-descricao" style="margin-top:7px"><?= h($p['Descricao']) ?></div><?php endif; ?>
                    </td>
                    <td><span class="plano-valor">R$ <?= number_format((float)($p['Valor'] ?? 0), 2, ',', '.') ?></span></td>
                    <td><span class="badge-status <?= !empty($p['Status']) ? 'badge-ativo' : 'badge-inativo' ?>"><?= !empty($p['Status']) ? 'Ativo' : 'Inativo' ?></span></td>
                    <td><div class="acoes">
                        <a class="btn-secondary" href="?edit=<?= rawurlencode((string)$planoId) ?>">Editar</a>
                        <a class="btn-toggle" href="?toggle=<?= rawurlencode((string)$planoId) ?>" onclick="return confirm('<?= !empty($p['Status']) ? 'Deseja inativar' : 'Deseja ativar' ?> este plano?')"><?= !empty($p['Status']) ? 'Inativar' : 'Ativar' ?></a>
                        <a class="btn-danger" href="?del=<?= rawurlencode((string)$planoId) ?>" onclick="return confirm('Tem certeza que deseja excluir este plano? Essa ação não pode ser desfeita.')">Excluir</a>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<script>
function formatarMoeda(campo) {
    let valor = campo.value.replace(/\D/g, '');
    valor = (Number(valor) / 100).toFixed(2);
    campo.value = valor.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
const campoValor = document.getElementById('Valor');
if (campoValor) {
    campoValor.addEventListener('input', function () { formatarMoeda(this); });
}
</script>
</body>
</html>
