<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])){
    header("Location: Login");
    exit;
}

if (!isset($_SESSION['AdminGeral']) || $_SESSION['AdminGeral'] != 1) {
    header("Location: Login");
    exit;
}
/* ================= FILTROS ================= */

$busca = $_GET['busca'] ?? '';
$statusFiltro = $_GET['status'] ?? '';
$planoFiltro = $_GET['plano'] ?? '';

$where = " WHERE 1=1 ";
$params = [];

if ($busca) {
    $where .= " AND (e.Nome LIKE ? OR e.Documento LIKE ? OR e.Email LIKE ?) ";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

if ($statusFiltro) {
    $where .= " AND e.Status = ? ";
    $params[] = $statusFiltro;
}

if ($planoFiltro) {
    $where .= " AND e.Plano = ? ";
    $params[] = $planoFiltro;
}


/* ================= PLANOS ================= */
$planosLista = ExSqlNET("
    SELECT id, Nome
    FROM planos
    WHERE Status = 1
    ORDER BY Valor ASC
");

/* ================= AÇÕES ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? 0;
    $acao = $_POST['acao'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($acao == 'aprovar') {

        $empresa = ExSqlNET(
            "SELECT Status FROM empresa WHERE id = ? LIMIT 1",
            null,
            [$id]
        );

        if (!empty($empresa) && $empresa[0]['Status'] != 'ATIVA') {

            ExSqlNET("
                UPDATE empresa 
                SET Status = 'ATIVA',
                    ValidadePlano = DATE_ADD(NOW(), INTERVAL 30 DAY)
                WHERE id = ?
            ", null, [$id]);

            ExSqlNET("
                UPDATE user 
                SET Inativo = 0
                WHERE idEmpresa = ?
            ", null, [$id]);

            enviaEmailCadastroAprovado($email);

            $_SESSION['mensagem_sucesso'] = "Empresa aprovada com sucesso.";
        }
    }

    if ($acao == 'salvar') {

        ExSqlNET("
            UPDATE empresa 
            SET Plano = ?, Status = ?, ValidadePlano = ?, LimiteUsuarios = ?
            WHERE id = ?
        ", null, [
            $_POST['Plano'],
            $_POST['Status'],
            $_POST['ValidadePlano'],
            $_POST['LimiteUsuarios'],
            $id
        ]);

        $_SESSION['mensagem_sucesso'] = "Dados atualizados com sucesso.";
    }
}

/* ================= LISTA ================= */

$empresas = ExSqlNET("
    SELECT e.*,
    p.Nome AS NomePlano,
    (SELECT COUNT(*) FROM user u WHERE u.idEmpresa = e.id) AS TotalUsuarios
    FROM empresa e
    LEFT JOIN planos p ON p.id = e.Plano
    $where
    ORDER BY e.id DESC
", null, $params);

/* ================= MÉTRICAS ================= */

$totalAtivas = 0;
$totalPendentes = 0;
$totalVencidas = 0;

foreach ($empresas as $e) {

    if ($e['Status'] == 'ATIVA') $totalAtivas++;
    if ($e['Status'] == 'PENDENTE') $totalPendentes++;

    if (!empty($e['ValidadePlano']) && strtotime($e['ValidadePlano']) < time()) {
        $totalVencidas++;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Empresas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
<style>

:root{
    --primary:#f97316;
    --primary-dark:#ea580c;

    --success:#22c55e;
    --danger:#ef4444;
    --warning:#f59e0b;

    --bg:#f8fafc;
    --card:#ffffff;

    --text:#0f172a;
    --muted:#64748b;

    --border:#e2e8f0;

    --shadow:
        0 10px 30px rgba(15,23,42,.06);

    --radius:16px;
}

body{
    background:var(--bg);
}

/* ===== CONTENT ===== */

.content{
    padding:30px;
}

.page-title{
    font-size:32px;
    font-weight:800;
    color:var(--text);
    margin-bottom:4px;
}

.subtitle{
    color:var(--muted);
    margin-bottom:28px;
    font-size:15px;
}

/* ===== FILTROS ===== */

.filtros{
    display:flex;
    flex-wrap:wrap;
    gap:14px;

    background:var(--card);

    padding:18px;
    border-radius:var(--radius);

    box-shadow:var(--shadow);

    margin-bottom:25px;
    align-items:flex-end;
}

.filtros input,
.filtros select{
    min-width:220px;

    height:46px;

    padding:0 14px;

    border:1px solid var(--border);
    border-radius:12px;

    background:#fff;

    font-size:14px;
    color:var(--text);

    transition:.2s;
}

.filtros input:focus,
.filtros select:focus{
    outline:none;

    border-color:var(--primary);

    box-shadow:
        0 0 0 4px rgba(249,115,22,.12);
}

/* ===== CARDS ===== */

.cards{
    display:grid;

    grid-template-columns:
        repeat(auto-fit,minmax(220px,1fr));

    gap:18px;

    margin-bottom:28px;
}

.card-metric{
    position:relative;

    overflow:hidden;

    background:var(--card);

    padding:24px;

    border-radius:var(--radius);

    box-shadow:var(--shadow);

    border:1px solid rgba(226,232,240,.7);

    transition:.25s;
}

.card-metric:hover{
    transform:translateY(-3px);
}

.card-metric::before{
    content:'';

    position:absolute;

    top:0;
    left:0;

    width:100%;
    height:4px;

    background:
        linear-gradient(
            90deg,
            var(--primary),
            var(--primary-dark)
        );
}

.metric-title{
    color:var(--muted);
    font-size:13px;
    margin-bottom:10px;
}

.metric-value{
    font-size:34px;
    font-weight:800;
    color:var(--text);
}

/* ===== CARD ===== */

.card{
    background:var(--card);

    border-radius:var(--radius);

    padding:24px;

    box-shadow:var(--shadow);

    border:1px solid rgba(226,232,240,.7);

    margin-bottom:24px;
}

/* ===== TABELA ===== */

.table-modern{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
}

.table-modern thead th{
    background:#fff7ed;

    color:var(--primary-dark);

    font-size:13px;
    font-weight:700;

    padding:16px 14px;

    text-align:left;

    border-bottom:1px solid #fed7aa;
}

.table-modern thead th:first-child{
    border-top-left-radius:12px;
}

.table-modern thead th:last-child{
    border-top-right-radius:12px;
}

.table-modern tbody td{
    padding:16px 14px;

    border-bottom:1px solid #f1f5f9;

    vertical-align:middle;

    color:#334155;
}

.table-modern tbody tr{
    transition:.18s;
}

.table-modern tbody tr:hover{
    background:#fffaf5;
}

.table-modern small{
    color:var(--muted);
    font-size:12px;
}

/* ===== BADGES ===== */

.badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;

    padding:6px 12px;

    border-radius:999px;

    font-size:11px;
    font-weight:700;

    letter-spacing:.3px;
}

.badge-ativa{
    background:#dcfce7;
    color:#166534;
}

.badge-pendente{
    background:#fef3c7;
    color:#92400e;
}

.badge-inativa{
    background:#fee2e2;
    color:#991b1b;
}

.badge-vencido{
    background:#e2e8f0;
    color:#334155;
}

/* ===== BOTÕES ===== */

.btn{
    display:inline-flex;

    align-items:center;
    justify-content:center;
    gap:8px;

    height:42px;

    padding:0 16px;

    border:none;
    border-radius:12px;

    font-size:13px;
    font-weight:700;

    cursor:pointer;

    transition:.22s;

    text-decoration:none;
}

.btn:hover{
    transform:translateY(-1px);
}

.btn-aprovar{
    background:
        linear-gradient(
            135deg,
            #22c55e,
            #16a34a
        );

    color:white;

    box-shadow:
        0 8px 18px rgba(34,197,94,.22);
}

.btn-gerenciar{
    background:
        linear-gradient(
            135deg,
            var(--primary),
            var(--primary-dark)
        );

    color:white;

    box-shadow:
        0 8px 18px rgba(249,115,22,.24);
}

.btn-aprovar:hover,
.btn-gerenciar:hover{
    filter:brightness(1.03);
}

/* ===== MOBILE CARD ===== */

.mobile-card{
    display:none;

    background:var(--card);

    padding:18px;

    border-radius:16px;

    margin-bottom:16px;

    box-shadow:var(--shadow);

    border:1px solid rgba(226,232,240,.7);
}

.mobile-card strong{
    font-size:16px;
    color:var(--text);
}

.mobile-row{
    margin-top:7px;

    font-size:13px;
    color:#475569;
}

/* ===== MODAL ===== */

.modal{
    display:none;

    position:fixed;

    inset:0;

    background:rgba(15,23,42,.45);

    backdrop-filter:blur(3px);

    z-index:999;

    align-items:center;
    justify-content:center;

    padding:20px;
}

.modal-box{
    width:100%;
    max-width:460px;

    background:white;

    border-radius:20px;

    padding:28px;

    box-shadow:
        0 20px 50px rgba(0,0,0,.18);

    animation:fadeUp .2s ease;
}

.modal-box h3{
    margin-top:0;
    margin-bottom:22px;

    font-size:22px;
    color:var(--text);
}

.modal-box label{
    display:block;

    margin-bottom:6px;
    margin-top:14px;

    font-size:13px;
    font-weight:700;

    color:#475569;
}

.modal-box input,
.modal-box select{
    width:100%;
    height:46px;

    padding:0 14px;

    border:1px solid var(--border);
    border-radius:12px;

    font-size:14px;

    transition:.2s;
}

.modal-box input:focus,
.modal-box select:focus{
    outline:none;

    border-color:var(--primary);

    box-shadow:
        0 0 0 4px rgba(249,115,22,.12);
}

.modal-actions{
    display:flex;
    gap:12px;

    margin-top:24px;
}

/* ===== ANIMAÇÃO ===== */

@keyframes fadeUp{

    from{
        opacity:0;
        transform:translateY(10px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* ===== RESPONSIVO ===== */

@media(max-width:900px){

    .table-desktop{
        display:none;
    }

    .mobile-card{
        display:block;
    }

    .btn{
        width:100%;
    }
}

@media(max-width:768px){

    .content{
        padding:18px;
    }

    .filtros{
        flex-direction:column;
        align-items:stretch;
    }

    .filtros input,
    .filtros select,
    .filtros .btn{
        width:100%;
        min-width:unset;
    }

    .cards{
        grid-template-columns:1fr;
    }

    .modal-actions{
        flex-direction:column;
    }
}

.btn-operando{
    background:#dcfce7;
    color:#166534;
    cursor:default;
    box-shadow:none;
    opacity:.9;
}

.btn-operando:hover{
    transform:none;
}
</style>
</head>

<body>

    <?php include __DIR__ . '/../base/navbarUser.php'; ?>

    <div class="content">

        <div class="page-title">Gestão de Empresas</div>
        <div class="subtitle">Controle de clientes cadastrados</div>

        <?php
            if (isset($_SESSION['mensagem_sucesso'])) {
                echo '<div class="alert success">'.$_SESSION['mensagem_sucesso'].'</div>';
                unset($_SESSION['mensagem_sucesso']);
            }
        ?>

        <!-- FILTROS -->

        <form class="filtros" method="get">

            <input type="text" name="busca" placeholder="Nome, Documento ou Email"
                value="<?= htmlspecialchars($busca) ?>">

            <select name="status">
                <option value="">Status</option>
                <option value="ATIVA">ATIVA</option>
                <option value="PENDENTE">PENDENTE</option>
                <option value="INATIVA">INATIVA</option>
            </select>

            <select name="plano">
                <option value="">Plano</option>

                <?php foreach ($planosLista as $pl): ?>

                    <option value="<?= $pl['id'] ?>"
                    <?= $planoFiltro == $pl['id'] ? 'selected' : '' ?>>

                        <?= $pl['Nome'] ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <button class="btn btn-gerenciar">Pesquisar</button>

        </form>

        <!-- MÉTRICAS -->

        <div class="cards">

            <div class="card-metric">
                <div class="metric-title">Empresas Ativas</div>
                <div class="metric-value"><?= $totalAtivas ?></div>
            </div>

            <div class="card-metric">
                <div class="metric-title">Pendentes</div>
                <div class="metric-value"><?= $totalPendentes ?></div>
            </div>

            <div class="card-metric">
                <div class="metric-title">Planos Vencidos</div>
                <div class="metric-value"><?= $totalVencidas ?></div>
            </div>

        </div>

        <!-- TABELA DESKTOP -->

        <div class="card">

            <table class="table-modern table-desktop">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Empresa</th>
                        <th>Plano</th>
                        <th>Status</th>
                        <th>Validade</th>
                        <th>Usuários</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($empresas as $emp):

                    $statusClass = 'badge-pendente';
                    if ($emp['Status'] == 'ATIVA') $statusClass = 'badge-ativa';
                    if ($emp['Status'] == 'INATIVA') $statusClass = 'badge-inativa';

                    $vencido = (!empty($emp['ValidadePlano']) && strtotime($emp['ValidadePlano']) < time());
                    ?>

                    <tr>

                        <td><?= $emp['id'] ?></td>

                        <td>
                            <strong><?= $emp['Nome'] ?></strong><br>
                            <small><?= $emp['Documento'] ?></small><br>
                            <small><?= $emp['Email'] ?></small><br>
                            <small><?= $emp['Telefone'] ?></small><br>
                            <small>
                                <?= htmlspecialchars($emp['Cidade'] ?? '') ?>
                                <?= !empty($emp['UF']) ? ' - ' . htmlspecialchars($emp['UF']) : '' ?>
                            </small><br>
                        </td>

                        <td><?= $emp['NomePlano'] ?></td>

                        <td>
                            <span class="badge <?= $statusClass ?>">
                                <?= $emp['Status'] ?>
                            </span>
                        </td>

                        <td>
                            <?= $emp['ValidadePlano'] ?>
                            <?php if ($vencido): ?>
                            <span class="badge badge-vencido">Vencido</span>
                            <?php endif; ?>
                        </td>

                        <td><?= $emp['TotalUsuarios'] ?></td>

                        <td>

                            <!-- <form method="post" style="display:inline">
                                <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                                <input type="hidden" name="email" value="<?= $emp['Email'] ?>">
                                <input type="hidden" name="acao" value="aprovar">
                                <button class="btn btn-aprovar">✔ Aprovar</button>
                            </form> -->

                            <?php if ($emp['Status'] != 'ATIVA'): ?>

                                <form method="post" style="display:inline">
                                    <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                                    <input type="hidden" name="email" value="<?= $emp['Email'] ?>">
                                    <input type="hidden" name="acao" value="aprovar">

                                    <button class="btn btn-aprovar">
                                        ✔ Aprovar
                                    </button>
                                </form>

                            <?php else: ?>

                                <button class="btn btn-operando" disabled>
                                    ✅ Operando
                                </button>

                            <?php endif; ?>

                            <button class="btn btn-gerenciar" onclick="abrirModal(
                                <?= $emp['id'] ?>,
                                '<?= $emp['Plano'] ?>',
                                '<?= $emp['Status'] ?>',
                                '<?= $emp['ValidadePlano'] ?>',
                                '<?= $emp['LimiteUsuarios'] ?>'
                                )">
                                ⚙ Gerenciar
                            </button>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <!-- MOBILE -->

        <?php foreach ($empresas as $emp):
            $statusClass = 'badge-pendente';
            if ($emp['Status'] == 'ATIVA') $statusClass = 'badge-ativa';
            if ($emp['Status'] == 'INATIVA') $statusClass = 'badge-inativa';

            $vencido = (!empty($emp['ValidadePlano']) && strtotime($emp['ValidadePlano']) < time());
        ?>

        <div class="mobile-card">

            <strong><?= $emp['Nome'] ?></strong>

            <div class="mobile-row">Documento: <?= $emp['Documento'] ?></div>
            <div class="mobile-row">Email: <?= $emp['Email'] ?></div>
            <div class="mobile-row">Telefone: <?= $emp['Telefone'] ?></div>
            <div class="mobile-row">Plano: <?= $emp['Plano'] ?></div>

            <div class="mobile-row">
                Status:
                <span class="badge <?= $statusClass ?>">
                    <?= $emp['Status'] ?>
                </span>
            </div>

            <div class="mobile-row">
                Validade: <?= $emp['ValidadePlano'] ?>
                <?php if ($vencido): ?>
                <span class="badge badge-vencido">Vencido</span>
                <?php endif; ?>
            </div>

            <div class="mobile-row">Usuários: <?= $emp['TotalUsuarios'] ?></div>

            <div style="margin-top:10px; display:flex; flex-direction:column; gap:8px;">

                <!-- <form method="post">
                    <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                    <input type="hidden" name="acao" value="aprovar">
                    <button class="btn btn-aprovar">✔ Aprovar</button>
                </form> -->

                <?php if ($emp['Status'] != 'ATIVA'): ?>
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                        <input type="hidden" name="acao" value="aprovar">

                        <button class="btn btn-aprovar">
                            ✔ Aprovar
                        </button>
                    </form>

                <?php else: ?>
                    <button class="btn btn-operando" disabled>
                        ✅ Operando
                    </button>
                <?php endif; ?>

                <button class="btn btn-gerenciar" onclick="abrirModal(
                    <?= $emp['id'] ?>,
                    '<?= $emp['Plano'] ?>',
                    '<?= $emp['Status'] ?>',
                    '<?= $emp['ValidadePlano'] ?>',
                    '<?= $emp['LimiteUsuarios'] ?>'
                    )">
                    ⚙ Gerenciar
                </button>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <!-- MODAL -->

    <div id="modal" class="modal">

        <div class="modal-box">

            <h3>Gerenciar Empresa</h3>

            <form method="post">

                <input type="hidden" name="id" id="mId">
                <input type="hidden" name="acao" value="salvar">

                <label>Plano</label>
                <select name="Plano" id="mPlano">

                <?php foreach ($planosLista as $pl): ?>

                    <option value="<?= $pl['id'] ?>">
                        <?= $pl['Nome'] ?>
                    </option>

                <?php endforeach; ?>

                </select>

                <label>Status</label>
                <select name="Status" id="mStatus">
                    <option value="ATIVA">ATIVA</option>
                    <option value="PENDENTE">PENDENTE</option>
                    <option value="INATIVA">INATIVA</option>
                </select>

                <label>Validade</label>
                <input type="date" name="ValidadePlano" id="mValidade">

                <label>Limite Usuários</label>
                <input type="number" name="LimiteUsuarios" id="mLimiteUsuarios">

                <br><br>

                <div class="modal-actions">
                    <button class="btn btn-gerenciar">Salvar</button>
                    <button type="button" class="btn" onclick="fecharModal()">Fechar</button>
                </div>

            </form>

        </div>
    </div>

<script>
    function abrirModal(id, plano, status, validade, limiteUsuarios) {

        document.getElementById('modal').style.display = 'flex';

        mId.value = id;
        mPlano.value = plano;
        mStatus.value = status;
        mValidade.value = validade;
        mLimiteUsuarios.value = limiteUsuarios;

    }

    function fecharModal() {
        document.getElementById('modal').style.display = 'none';
    }
    
    
    //FECHANDO MODAL NO ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('modal');
            if (modal && modal.style.display === 'flex') {
                fecharModal();
            }
        }
    
    }); 
    
    // FECHAR CLICANDO FORA DO MODAL
    document.getElementById('modal').addEventListener('click', function(e) {

        if (e.target === this) {
            fecharModal();
        }

    });

</script>

</body>

</html>