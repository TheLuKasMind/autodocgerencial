<?php
require_once __DIR__ . '/../base/connection.php';
require_once __DIR__ . '/../base/baseFuncoes.php';
require_once __DIR__ . '/../base/verificaPlano.php';
require_once __DIR__ . '/../base/permissoes.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['AdminGeral'])) {
    header('Location: Login');
    exit;
}

$msgRetorno = '';
$tipoMsg = '';
$idEmpresa = $_SESSION['idEmpresa'];
$busca = trim($_GET['busca'] ?? '');
$statusFiltro = $_GET['status'] ?? '';

$where = ' WHERE idEmpresa = ? ';
$params = [$idEmpresa];
if ($busca !== '') {
    $where .= ' AND (Nome LIKE ? OR Email LIKE ? OR Cargo LIKE ?) ';
    $termo = '%' . $busca . '%';
    array_push($params, $termo, $termo, $termo);
}
if ($statusFiltro !== '') {
    $where .= ' AND Inativo = ? ';
    $params[] = ($statusFiltro === 'INATIVO') ? 1 : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $acao = $_POST['acao'] ?? '';
    $nome = trim($_POST['Nome'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $senha = $_POST['Senha'] ?? '';
    $cargo = trim($_POST['Cargo'] ?? '');
    $inativo = isset($_POST['Inativo']) ? 1 : 0;
    $naoVerDadosFinanceiro = isset($_POST['NaoVerDadosFinanceiro']) ? 1 : 0;

    if ($acao === 'salvar') {
        $permissoes = $_POST['permissoes'] ?? [];

        if ($nome === '' || $email === '' || (!$id && $senha === '')) {
            $_SESSION['mensagem_erro'] = 'Preencha nome, e-mail e senha para cadastrar um novo usuário.';
            header('Location: Usuarios');
            exit;
        }

        if ($id) {
            if ($senha !== '') {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                ExSqlNET('UPDATE user SET Nome=?, Email=?, Senha=?, Cargo=?, Inativo=?, NaoVerDadosFinanceiro=? WHERE id=? AND idEmpresa=?', null, [
                    $nome, $email, $senhaHash, $cargo, $inativo, $naoVerDadosFinanceiro, $id, $idEmpresa
                ]);
            } else {
                ExSqlNET('UPDATE user SET Nome=?, Email=?, Cargo=?, Inativo=?, NaoVerDadosFinanceiro=? WHERE id=? AND idEmpresa=?', null, [
                    $nome, $email, $cargo, $inativo, $naoVerDadosFinanceiro, $id, $idEmpresa
                ]);
            }
            $idUsuario = $id;
            $_SESSION['mensagem_sucesso'] = 'Usuário atualizado com sucesso.';
        } else {
            $totalUsuarios = ExSqlNET('SELECT COUNT(*) AS total FROM user WHERE idEmpresa = ?', null, [$idEmpresa]);
            $totalUsuarios = (int)($totalUsuarios[0]['total'] ?? 0);
            $limiteUsuarios = (int)($_SESSION['empresa_limiteUsuarios'] ?? 0);
            if ($limiteUsuarios > 0 && $totalUsuarios >= $limiteUsuarios) {
                $_SESSION['mensagem_erro'] = 'Limite de usuários do seu plano foi atingido. Entre em contato com o administrador para liberar novos cadastros.';
                header('Location: Usuarios');
                exit;
            }

            $emailExiste = ExSqlNET('SELECT id FROM user WHERE Email = ? LIMIT 1', null, [$email]);
            if (!empty($emailExiste)) {
                $_SESSION['mensagem_erro'] = 'Já existe um usuário cadastrado com este e-mail.';
                header('Location: Usuarios');
                exit;
            }

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            ExSqlNET('INSERT INTO user (Nome, Email, Senha, Cargo, Inativo, NaoVerDadosFinanceiro, idEmpresa) VALUES (?,?,?,?,?,?,?)', null, [
                $nome, $email, $senhaHash, $cargo, $inativo, $naoVerDadosFinanceiro, $idEmpresa
            ]);
            $usuarioNovo = ExSqlNET('SELECT id FROM user WHERE idEmpresa = ? AND Email = ? ORDER BY id DESC LIMIT 1', null, [$idEmpresa, $email]);
            $idUsuario = (int)($usuarioNovo[0]['id'] ?? 0);
            $_SESSION['mensagem_sucesso'] = 'Usuário cadastrado com sucesso.';
        }

        ExSqlNET('DELETE FROM userpermissoes WHERE idEmpresa=? AND idUsuario=?', null, [$idEmpresa, $idUsuario]);
        foreach ($permissoes as $pagina) {
            ExSqlNET('INSERT INTO userpermissoes (idEmpresa, idUsuario, pagina) VALUES (?,?,?)', null, [$idEmpresa, $idUsuario, $pagina]);
        }
        header('Location: Usuarios');
        exit;
    }
}

if (isset($_GET['del'])) {
    $idDel = (int)$_GET['del'];
    ExSqlNET('DELETE FROM user WHERE id=? AND idEmpresa=?', null, [$idDel, $idEmpresa]);
    ExSqlNET('DELETE FROM userpermissoes WHERE idEmpresa=? AND idUsuario=?', null, [$idEmpresa, $idDel]);
    $_SESSION['mensagem_sucesso'] = 'Usuário excluído com sucesso.';
    header('Location: Usuarios');
    exit;
}

$usuarios = ExSqlNET("SELECT * FROM user $where ORDER BY id DESC", null, $params);
$totalAtivos = 0;
$totalInativos = 0;
foreach ($usuarios as $u) {
    if (!empty($u['Inativo'])) $totalInativos++; else $totalAtivos++;
}

$permissoesUsuarios = [];
foreach ($usuarios as $u) {
    $listaPermissoes = ExSqlNET('SELECT pagina FROM userpermissoes WHERE idEmpresa=? AND idUsuario=?', null, [$idEmpresa, $u['id']]);
    $permissoesUsuarios[$u['id']] = array_column($listaPermissoes, 'pagina');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro de Usuários</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/gerencial/css/base.css?v=15">
<link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
<style>
    .content{width:min(1180px,calc(100% - 40px));margin:30px auto;padding:0;}
    .page-title{font-size:28px;font-weight:700;line-height:1.25;color:#334155;margin:0;}
    .subtitle{color:#64748b;margin:6px 0 24px;font-size:14px;}
    .panel{background:#fff;border:1px solid #e9edf2;border-radius:14px;padding:22px;box-shadow:0 5px 16px rgba(15,23,42,.045);margin-bottom:22px;}
    .section-heading{font-size:16px;font-weight:700;color:#9a3412;background:#fff7ed;border-left:4px solid #f97316;padding:12px 14px;border-radius:8px;margin:0 0 18px;}
    .top-actions{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:18px;}
    .filters{display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;margin:0 0 22px;}
    .filters .field{display:flex;flex-direction:column;gap:6px;min-width:210px;flex:1;}
    .filters label,.modal-box label{font-size:13px;font-weight:600;color:#475569;}
    .filters input,.filters select,.modal-box input[type=text],.modal-box input[type=email],.modal-box input[type=password]{width:100%;box-sizing:border-box;border:1px solid #dbe2ea;border-radius:9px;background:#fff;padding:11px 12px;font-size:14px;color:#1e293b;transition:border-color .2s,box-shadow .2s;}
    .filters input:focus,.filters select:focus,.modal-box input:focus{outline:none;border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,.13);}
    .btn-new{display:inline-flex;align-items:center;justify-content:center;gap:7px;width:auto;min-height:42px;padding:10px 16px;border:0;border-radius:9px;background:#f97316;color:#fff;font-weight:700;text-decoration:none;cursor:pointer;transition:background .2s;}
    .btn-new:hover{background:#ea580c;}
    .metric-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-bottom:22px;}
    .metric-card{background:#fff;border:1px solid #e9edf2;border-left:4px solid #f97316;border-radius:12px;padding:18px 20px;box-shadow:0 4px 12px rgba(15,23,42,.035);}
    .metric-title{font-size:13px;color:#64748b;margin-bottom:7px;}
    .metric-value{font-size:27px;font-weight:750;color:#ea580c;line-height:1.1;}
    .table-wrap{width:100%;overflow-x:auto;}
    .table-modern{width:100%;border-collapse:collapse;min-width:720px;}
    .table-modern th{background:#fff7ed;color:#9a3412;padding:13px 14px;text-align:left;font-size:13px;font-weight:700;white-space:nowrap;}
    .table-modern td{padding:13px 14px;border-bottom:1px solid #edf0f4;vertical-align:middle;font-size:14px;color:#334155;}
    .table-modern tbody tr:hover{background:#fffaf5;}
    .table-modern tbody tr:last-child td{border-bottom:0;}
    .badge{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:700;white-space:nowrap;}
    .badge-ativo{background:#dcfce7;color:#166534;}
    .badge-inativo{background:#fee2e2;color:#991b1b;}
    .table-actions{display:flex;gap:8px;align-items:center;white-space:nowrap;}
    .btn-edit,.btn-delete,.btn-secondary{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:8px;padding:9px 12px;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer;}
    .btn-edit{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;}
    .btn-edit:hover{background:#ffedd5;}
    .btn-delete{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;}
    .btn-delete:hover{background:#fee2e2;}
    .btn-secondary{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;}
    .mobile-list{display:none;}
    .mobile-card{background:#fff;border:1px solid #e9edf2;border-left:4px solid #f97316;border-radius:12px;padding:16px;margin-bottom:12px;box-shadow:0 4px 12px rgba(15,23,42,.035);}
    .mobile-name{font-size:15px;font-weight:700;color:#1e293b;overflow-wrap:anywhere;}
    .mobile-row{font-size:13px;color:#64748b;margin-top:7px;overflow-wrap:anywhere;}
    .mobile-actions{display:flex;gap:8px;margin-top:14px;}
    .mobile-actions>*{flex:1;}
    .modal{display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);align-items:center;justify-content:center;z-index:9999;padding:18px;box-sizing:border-box;}
    .modal-box{background:#fff;border-radius:16px;width:min(620px,100%);max-height:calc(100vh - 36px);overflow:auto;padding:24px;box-shadow:0 20px 60px rgba(15,23,42,.25);box-sizing:border-box;}
    .modal-header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px;}
    .modal-title{font-size:21px;font-weight:750;color:#334155;margin:0;}
    .modal-subtitle{font-size:13px;color:#64748b;margin-top:5px;}
    .modal-fields{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .modal-field{display:flex;flex-direction:column;gap:6px;min-width:0;}
    .modal-field.full{grid-column:1/-1;}
    .modal-field input{min-width:0;}
    .check-setting{display:flex;align-items:center;justify-content:space-between;gap:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;color:#475569;font-size:13px;font-weight:600;}
    .check-setting input{width:18px;height:18px;accent-color:#ea580c;flex-shrink:0;}
    .modal-divider{border:0;border-top:1px solid #e9edf2;margin:20px 0;}
    .permissions-title{font-size:14px;font-weight:750;color:#334155;margin-bottom:4px;}
    .permissions-help{font-size:12px;color:#64748b;margin-bottom:12px;}
    .permissoes-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px;max-height:240px;overflow:auto;padding:2px 3px 2px 0;}
    .check-permissao{display:flex;align-items:center;gap:9px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:10px;font-size:13px;color:#475569;cursor:pointer;transition:background .2s,border-color .2s;min-width:0;}
    .check-permissao:hover{border-color:#fdba74;background:#fff7ed;}
    .check-permissao input{width:17px;height:17px;flex-shrink:0;accent-color:#ea580c;}
    .check-permissao span{overflow-wrap:anywhere;}
    .modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px;}
    .modal-actions .btn-new,.modal-actions .btn-secondary{min-width:100px;}
    .hide-id{display:none;}
    .empty-state{text-align:center;padding:30px 15px;color:#64748b;font-size:14px;}
    @media(max-width:900px){.table-desktop{display:none;}.mobile-list{display:block;}}
    @media(max-width:640px){
        .content{width:calc(100% - 28px);margin:22px auto;}
        .page-title{font-size:24px;}
        .panel{padding:15px;border-radius:12px;}
        .top-actions{justify-content:flex-start;}
        .btn-new{width:auto;}
        .filters{display:grid;grid-template-columns:1fr;width:100%;}
        .filters .field{min-width:0;width:100%;}
        .filters .btn-new{justify-self:start;}
        .metric-grid{gap:10px;}
        .metric-card{padding:15px;}
        .metric-value{font-size:24px;}
        .mobile-actions{flex-wrap:wrap;}
        .modal{padding:10px;align-items:flex-start;}
        .modal-box{padding:18px;max-height:calc(100vh - 20px);}
        .modal-fields,.permissoes-grid{grid-template-columns:1fr;}
        .modal-field.full{grid-column:auto;}
        .modal-actions{flex-direction:column-reverse;}
        .modal-actions>*{width:100%;}
    }
</style>
</head>
<body>
<?php include __DIR__ . '/../base/navbarUser.php'; ?>
<div class="content">
    <div class="page-title">Cadastro de Usuários</div>
    <div class="subtitle">Gerencie os usuários da sua empresa</div>

    <?php
    if (isset($_SESSION['mensagem_sucesso'])) {
        $msgRetorno = $_SESSION['mensagem_sucesso'];
        $tipoMsg = 'success';
        unset($_SESSION['mensagem_sucesso']);
    }
    if (isset($_SESSION['mensagem_erro'])) {
        $tipoMsg = 'error';
        $msgRetorno = $_SESSION['mensagem_erro'];
        unset($_SESSION['mensagem_erro']);
    }
    if ($msgRetorno): ?>
        <div class="alert <?= htmlspecialchars($tipoMsg, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($msgRetorno, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="top-actions">
        <button type="button" class="btn-new" onclick="abrirModal(0,'','','','','0','0')">+ Novo Usuário</button>
    </div>

    <form class="filters" method="get" id="filtroUsuarios">
        <div class="field">
            <label for="busca">Buscar usuário</label>
            <input id="busca" type="text" name="busca" placeholder="Nome, e-mail ou cargo" value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Todos os status</option>
                <option value="ATIVO" <?= $statusFiltro === 'ATIVO' ? 'selected' : '' ?>>Ativo</option>
                <option value="INATIVO" <?= $statusFiltro === 'INATIVO' ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>
        <button type="submit" class="btn-new">Pesquisar</button>
    </form>

    <div class="metric-grid">
        <div class="metric-card"><div class="metric-title">Usuários ativos nos resultados</div><div class="metric-value"><?= $totalAtivos ?></div></div>
        <div class="metric-card"><div class="metric-title">Usuários inativos nos resultados</div><div class="metric-value"><?= $totalInativos ?></div></div>
    </div>

    <div class="panel">
        <div class="section-heading">Usuários cadastrados</div>
        <div class="table-wrap">
            <table class="table-modern table-desktop">
                <thead><tr><th class="hide-id">ID</th><th>Nome</th><th>E-mail</th><th>Cargo</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6" class="empty-state">Nenhum usuário encontrado.</td></tr>
                <?php else: foreach ($usuarios as $u): $statusClass = !empty($u['Inativo']) ? 'badge-inativo' : 'badge-ativo'; ?>
                    <tr>
                        <td class="hide-id"><?= (int)$u['id'] ?></td>
                        <td><?= htmlspecialchars($u['Nome'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($u['Cargo'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge <?= $statusClass ?>"><?= !empty($u['Inativo']) ? 'Inativo' : 'Ativo' ?></span></td>
                        <td><div class="table-actions">
                            <button type="button" class="btn-edit" data-id="<?= (int)$u['id'] ?>" data-nome="<?= htmlspecialchars($u['Nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-email="<?= htmlspecialchars($u['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-cargo="<?= htmlspecialchars($u['Cargo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-inativo="<?= (int)$u['Inativo'] ?>" data-nao-ver-dados-financeiro="<?= (int)($u['NaoVerDadosFinanceiro'] ?? 0) ?>" onclick="abrirModalFromData(this)">Editar</button>
                            <a href="?del=<?= (int)$u['id'] ?>" class="btn-delete" onclick="return confirm('Excluir usuário?')">Excluir</a>
                        </div></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mobile-list">
        <?php if (empty($usuarios)): ?>
            <div class="panel empty-state">Nenhum usuário encontrado.</div>
        <?php else: foreach ($usuarios as $u): $statusClass = !empty($u['Inativo']) ? 'badge-inativo' : 'badge-ativo'; ?>
            <div class="mobile-card">
                <div class="mobile-name"><?= htmlspecialchars($u['Nome'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="mobile-row">E-mail: <?= htmlspecialchars($u['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="mobile-row">Cargo: <?= htmlspecialchars($u['Cargo'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="mobile-row">Status: <span class="badge <?= $statusClass ?>"><?= !empty($u['Inativo']) ? 'Inativo' : 'Ativo' ?></span></div>
                <div class="mobile-actions">
                    <button type="button" class="btn-edit" data-id="<?= (int)$u['id'] ?>" data-nome="<?= htmlspecialchars($u['Nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-email="<?= htmlspecialchars($u['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-cargo="<?= htmlspecialchars($u['Cargo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-inativo="<?= (int)$u['Inativo'] ?>" data-nao-ver-dados-financeiro="<?= (int)($u['NaoVerDadosFinanceiro'] ?? 0) ?>" onclick="abrirModalFromData(this)">Editar</button>
                    <a href="?del=<?= (int)$u['id'] ?>" class="btn-delete" onclick="return confirm('Excluir usuário?')">Excluir</a>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<div id="modal" class="modal" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-header">
            <div><h2 id="modalTitle" class="modal-title">Gerenciar usuário</h2><div class="modal-subtitle">Preencha os dados e defina as permissões de acesso.</div></div>
            <button type="button" class="btn-secondary" onclick="fecharModal()" aria-label="Fechar">✕</button>
        </div>
        <form method="post" id="formUsuario">
            <input type="hidden" name="id" id="mId">
            <input type="hidden" name="acao" value="salvar">
            <div class="modal-fields">
                <div class="modal-field"><label for="mNome">Nome</label><input type="text" name="Nome" id="mNome" required></div>
                <div class="modal-field"><label for="mEmail">E-mail</label><input type="email" name="Email" id="mEmail" required></div>
                <div class="modal-field"><label for="mSenha">Senha <span id="senhaAjuda" style="font-weight:400;color:#64748b;">(obrigatória no cadastro)</span></label><input type="password" name="Senha" id="mSenha" autocomplete="new-password"></div>
                <div class="modal-field"><label for="mCargo">Cargo</label><input type="text" name="Cargo" id="mCargo"></div>
                <div class="modal-field full"><label class="check-setting"><span>Usuário inativo</span><input type="checkbox" name="Inativo" id="mInativo"></label></div>
                <div class="modal-field full"><label class="check-setting"><span>Não ver dados financeiros na tela inicial</span><input type="checkbox" name="NaoVerDadosFinanceiro" id="mNaoVerDadosFinanceiro"></label></div>
            </div>
            <hr class="modal-divider">
            <div class="permissions-title">Permissões de acesso</div>
            <div class="permissions-help">Selecione as telas que este usuário poderá acessar.</div>
            <div class="permissoes-grid">
                <?php foreach ($paginasSistema as $pagina => $titulo): ?>
                    <?php if (strpos($pagina, 'frmLancamento.php') !== false || strpos($pagina, 'frmMulta.php') !== false || strpos($pagina, 'frmServProd.php') !== false || strpos($pagina, 'frmForcli.php') !== false || strpos($pagina, 'frmDespesas.php') !== false) continue; ?>
                    <label class="check-permissao"><input type="checkbox" name="permissoes[]" value="<?= htmlspecialchars($pagina, ENT_QUOTES, 'UTF-8') ?>" class="checkPermissao"><span><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></span></label>
                <?php endforeach; ?>
            </div>
            <div class="modal-actions"><button type="button" class="btn-secondary" onclick="fecharModal()">Cancelar</button><button type="submit" class="btn-new">Salvar usuário</button></div>
        </form>
    </div>
</div>

<script>
const permissoesUsuarios = <?= json_encode($permissoesUsuarios, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const modal = document.getElementById('modal');

function abrirModal(id, nome, email, senha, cargo, inativo, naoVerDadosFinanceiro) {
    const novo = Number(id) === 0;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    document.getElementById('mId').value = id;
    document.getElementById('mNome').value = novo ? '' : (nome || '');
    document.getElementById('mEmail').value = novo ? '' : (email || '');
    document.getElementById('mSenha').value = '';
    document.getElementById('mSenha').required = novo;
    document.getElementById('mCargo').value = novo ? '' : (cargo || '');
    document.getElementById('mInativo').checked = Number(inativo) === 1;
    document.getElementById('mNaoVerDadosFinanceiro').checked = Number(naoVerDadosFinanceiro) === 1;
    document.getElementById('modalTitle').textContent = novo ? 'Novo usuário' : 'Editar usuário';
    document.querySelectorAll('.checkPermissao').forEach(check => { check.checked = false; });
    const lista = permissoesUsuarios[id] || [];
    document.querySelectorAll('.checkPermissao').forEach(check => {
        if (lista.includes(check.value)) check.checked = true;
    });
    setTimeout(() => document.getElementById('mNome').focus(), 50);
}
function fecharModal() {
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
}
function abrirModalFromData(el) {
    abrirModal(el.dataset.id, el.dataset.nome, el.dataset.email, '', el.dataset.cargo, el.dataset.inativo, el.dataset.naoVerDadosFinanceiro);
}
window.addEventListener('keydown', function(e) { if (e.key === 'Escape') fecharModal(); });
modal.addEventListener('click', function(e) { if (e.target === modal) fecharModal(); });

document.getElementById('status').addEventListener('change', function() {
    document.getElementById('filtroUsuarios').requestSubmit();
});
let buscaTimer;
document.getElementById('busca').addEventListener('input', function() {
    clearTimeout(buscaTimer);
    buscaTimer = setTimeout(() => document.getElementById('filtroUsuarios').requestSubmit(), 450);
});
</script>
</body>
</html>
