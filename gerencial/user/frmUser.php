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

if (!isset($_SESSION['AdminGeral'])) {
    header("Location: ../frmLogin.php");
    exit;
}

/* ================= FILTROS ================= */
$busca = $_GET['busca'] ?? '';
$statusFiltro = $_GET['status'] ?? '';

$where = " WHERE idEmpresa = ? ";
$params = [$idEmpresa = $_SESSION['idEmpresa']];

if ($busca) {
    $where .= " AND (Nome LIKE ? OR Email LIKE ? OR Cargo LIKE ?) ";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if ($statusFiltro) {
    $where .= " AND Inativo = ? ";
    $params[] = ($statusFiltro == 'INATIVO') ? 1 : 0;
}

/* ================= AÇÕES ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? 0;
    $acao = $_POST['acao'] ?? '';
    $nome = $_POST['Nome'] ?? '';
    $email = $_POST['Email'] ?? '';
    $senha = $_POST['Senha'] ?? '';

    $senha = password_hash($senha, PASSWORD_DEFAULT);


    $cargo = $_POST['Cargo'] ?? '';
    $inativo = isset($_POST['Inativo']) ? 1 : 0;

    if ($acao == 'salvar') {
        if ($id) {
            // UPDATE
            ExSqlNET("UPDATE user SET Nome=?, Email=?, Senha=?, Cargo=?, Inativo=? WHERE id=? AND idEmpresa=?", null, [$nome,$email,$senha,$cargo,$inativo,$id,$idEmpresa]);
            $_SESSION['mensagem_sucesso'] = "Usuário atualizado com sucesso.";
        } else {
            // INSERT
            ExSqlNET("INSERT INTO user (Nome, Email, Senha, Cargo, Inativo, idEmpresa) VALUES (?,?,?,?,?,?)", null, [$nome,$email,$senha,$cargo,$inativo,$idEmpresa]);
            $_SESSION['mensagem_sucesso'] = "Usuário cadastrado com sucesso.";
        }
        header("Location: frmUser.php");
        exit;
    }
}

/* ================= EXCLUIR ================= */
if (isset($_GET['del'])) {
    $idDel = $_GET['del'];
    ExSqlNET("DELETE FROM user WHERE id=? AND idEmpresa=?", null, [$idDel,$idEmpresa]);
    $_SESSION['mensagem_sucesso'] = "Usuário excluído com sucesso.";
    header("Location: frmUser.php");
    exit;
}

/* ================= LISTA ================= */
$usuarios = ExSqlNET("SELECT * FROM user $where ORDER BY id DESC", null, $params);

/* ================= MÉTRICAS ================= */
$totalAtivos = 0;
$totalInativos = 0;
foreach($usuarios as $u){
    if($u['Inativo']) $totalInativos++; else $totalAtivos++;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro de Usuários</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/base.css?v=15">
<link rel="icon" href="../img/favicon.png">

<style>
    /* ===== FILTROS ===== */
    .filtros { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; }
    .filtros input, .filtros select { padding:9px 12px; border-radius:8px; border:1px solid #e2e8f0; }

    /* ===== CARDS MÉTRICAS ===== */
    .cards { display:flex; gap:20px; flex-wrap:wrap; margin-bottom:20px; }
    .card-metric { flex:1; min-width:180px; background:white; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05); border-left:4px solid #f97316; }
    .metric-title { font-size:13px; color:#64748b; }
    .metric-value { font-size:26px; font-weight:700; color:#ea580c; }

    /* ===== TABELA ===== */
    .table-modern { width:100%; border-collapse:collapse; }
    .table-modern th { background:#fff7ed; color:#c2410c; padding:12px; text-align:left; font-weight:600; }
    .table-modern td { padding:12px; border-bottom:1px solid #e5e7eb; vertical-align:middle; }
    .table-modern tr:hover { background:#fffaf5; }

    /* ===== BADGES ===== */
    .badge { padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }
    .badge-ativo { background:#dcfce7; color:#166534; }
    .badge-inativo { background:#fee2e2; color:#991b1b; }

    /* ===== MOBILE ===== */
    .mobile-card { display:none; background:white; padding:15px; border-radius:12px; margin-bottom:12px; box-shadow:0 4px 10px rgba(0,0,0,0.05); border-left:4px solid #f97316; }
    .mobile-row { font-size:13px; margin-top:4px; color:#475569; }

    /* ===== MODAL ===== */
    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); align-items:center; justify-content:center; z-index:999; }
    .modal-box { background:white; padding:20px; border-radius:10px; width:100%; max-width:420px; }
    .modal-actions { display:flex; gap:10px; margin-top:10px; }

    /* ===== RESPONSIVO ===== */
    @media(max-width:900px){
        .table-desktop{display:none;}
        .mobile-card{display:block;}
        .btn{width:100%;}
    }

    /* ===== FILTROS MELHORADOS ===== */

    .filtros {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;                 /* espaço real entre os campos */
        align-items: flex-end;
        margin: 20px 0 25px 0;     /* respiro em cima e embaixo */
    }

    .filtros input,
    .filtros select {
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 14px;
        background: #f8fafc;
        min-width: 220px;
        transition: 0.2s;
    }

    /* foco mais elegante */
    .filtros input:focus,
    .filtros select:focus {
        outline: none;
        border-color: #ea580c;
        background: #fff;
        box-shadow: 0 0 0 2px rgba(234,88,12,0.15);
    }

    /* botão alinhado melhor */
    .filtros .btn {
        height: 42px;
        padding: 0 18px;
    }

    /* ===== CHECKBOX INATIVO ALINHADO ===== */

    .modal-box label:has(#mInativo) {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
        font-weight: 500;
    }

    .modal-box #mInativo {
        width: 18px;
        height: 18px;
    }
</style>
</head>

<body>
<?php include '../base/navbarUser.php'; ?>

<div class="content">
    <div class="page-title">Cadastro de Usuários</div>
    <div class="subtitle">Gerencie os usuários da sua empresa</div>

    <?php if(isset($_SESSION['mensagem_sucesso'])): ?>
        <div class="alert success"><?= $_SESSION['mensagem_sucesso'] ?></div>
        <?php unset($_SESSION['mensagem_sucesso']); ?>
    <?php endif; ?>

    <!-- BOTÃO NOVO USUÁRIO -->
    <button class="btn btn-salvar" onclick="abrirModal(0,'','','','','0')">+ Novo Usuário</button>

    <!-- FILTROS -->
    <form class="filtros" method="get">
        <input type="text" name="busca" placeholder="Nome, Email ou Cargo" value="<?= htmlspecialchars($busca) ?>">
        <select name="status">
            <option value="">Status</option>
            <option value="ATIVO" <?= $statusFiltro=='ATIVO'?'selected':'' ?>>Ativo</option>
            <option value="INATIVO" <?= $statusFiltro=='INATIVO'?'selected':'' ?>>Inativo</option>
        </select>
        <button class="btn btn-salvar">Pesquisar</button>
    </form>

    <!-- MÉTRICAS -->
    <div class="cards">
        <div class="card-metric">
            <div class="metric-title">Usuários Ativos</div>
            <div class="metric-value"><?= $totalAtivos ?></div>
        </div>
        <div class="card-metric">
            <div class="metric-title">Usuários Inativos</div>
            <div class="metric-value"><?= $totalInativos ?></div>
        </div>
    </div>

    <!-- TABELA DESKTOP -->
    <div class="card">
        <table class="table-modern table-desktop">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Cargo</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($usuarios as $u):
                $statusClass = $u['Inativo'] ? 'badge-inativo' : 'badge-ativo';
            ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= $u['Nome'] ?></td>
                    <td><?= $u['Email'] ?></td>
                    <td><?= $u['Cargo'] ?></td>
                    <td><span class="badge <?= $statusClass ?>"><?= $u['Inativo']?'Inativo':'Ativo' ?></span></td>
                    <td>
                        <button class="btn btn-editar"
                            data-id="<?= $u['id'] ?>"
                            data-nome="<?= htmlspecialchars($u['Nome'], ENT_QUOTES) ?>"
                            data-email="<?= htmlspecialchars($u['Email'], ENT_QUOTES) ?>"
                            data-senha="<?= htmlspecialchars("", ENT_QUOTES) ?>"
                            data-cargo="<?= htmlspecialchars($u['Cargo'], ENT_QUOTES) ?>"
                            data-inativo="<?= $u['Inativo'] ?>"
                            onclick="abrirModalFromData(this)">
                            Editar
                        </button>
                        <a href="?del=<?= $u['id'] ?>" class="btn-excluir" onclick="return confirm('Excluir usuário?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- MOBILE -->
    <?php foreach($usuarios as $u):
        $statusClass = $u['Inativo'] ? 'badge-inativo' : 'badge-ativo';
    ?>
    <div class="mobile-card">
        <strong><?= $u['Nome'] ?></strong>
        <div class="mobile-row">Email: <?= $u['Email'] ?></div>
        <div class="mobile-row">Cargo: <?= $u['Cargo'] ?></div>
        <div class="mobile-row">Status: <span class="badge <?= $statusClass ?>"><?= $u['Inativo']?'Inativo':'Ativo' ?></span></div>
        <div style="margin-top:10px; display:flex; flex-direction:column; gap:8px;">
            <button class="btn btn-editar"
                data-id="<?= $u['id'] ?>"
                data-nome="<?= htmlspecialchars($u['Nome'], ENT_QUOTES) ?>"
                data-email="<?= htmlspecialchars($u['Email'], ENT_QUOTES) ?>"
                data-senha="<?= htmlspecialchars("", ENT_QUOTES) ?>"
                data-cargo="<?= htmlspecialchars($u['Cargo'], ENT_QUOTES) ?>"
                data-inativo="<?= $u['Inativo'] ?>"
                onclick="abrirModalFromData(this)">
                Editar
            </button>
            <button href="?del=<?= $u['id'] ?>" class="btn-excluir" onclick="return confirm('Excluir usuário?')">Excluir</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- MODAL -->
<div id="modal" class="modal">
    <div class="modal-box">
        <h3>Gerenciar Usuário</h3>
        <form method="post">
            <input type="hidden" name="id" id="mId">
            <input type="hidden" name="acao" value="salvar">
            <label>Nome</label>
            <input type="text" name="Nome" id="mNome" required>
            <label>Email</label>
            <input type="email" name="Email" id="mEmail" required>
            <label>Senha</label>
            <input type="text" name="Senha" id="mSenha" required>
            <label>Cargo</label>
            <input type="text" name="Cargo" id="mCargo" value="<?= htmlspecialchars($u['Cargo'] ?? '') ?>">
            <!-- <label><input type="checkbox" name="Inativo" id="mInativo"> Inativo</label> -->
            <label class="label-inativo">
                <span>Inativo</span>
                <input type="checkbox" name="Inativo" id="mInativo">
            </label>
            <div class="modal-actions">
                <button class="btn btn-salvar">Salvar</button>
                <button type="button" class="btn" onclick="fecharModal()">Fechar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModal(id, nome, email, senha, cargo, inativo){
        document.getElementById('modal').style.display='flex';
        document.getElementById('mId').value = id;
        document.getElementById('mNome').value = id == 0 ? '' : nome;
        document.getElementById('mEmail').value = id == 0 ? '' : email;
        document.getElementById('mSenha').value = id == 0 ? '' : senha;
        document.getElementById('mCargo').value = id == 0 ? '' : cargo;
        document.getElementById('mInativo').checked = (inativo==1);
    }
    function fecharModal(){
        document.getElementById('modal').style.display='none';
    }

    function abrirModalFromData(el) {
        document.getElementById('modal').style.display = 'flex';
        document.getElementById('mId').value = el.dataset.id;
        document.getElementById('mNome').value = el.dataset.nome;
        document.getElementById('mEmail').value = el.dataset.email;
        document.getElementById('mSenha').value = el.dataset.senha;
        document.getElementById('mCargo').value = el.dataset.cargo;
        document.getElementById('mInativo').checked = el.dataset.inativo == 1;
    }
</script>
</body>
</html>