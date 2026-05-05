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

    if ($acao == 'salvar') {

        ExSqlNET("
            UPDATE empresa 
            SET Plano = ?, Status = ?, ValidadePlano = ?
            WHERE id = ?
        ", null, [
            $_POST['Plano'],
            $_POST['Status'],
            $_POST['ValidadePlano'],
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

    <link rel="stylesheet" href="../css/base.css">
    <link rel="icon" href="../img/favicon.png">

<style>
    /* ===== FILTROS ===== */

    .filtros {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .filtros input,
    .filtros select {
        padding: 9px 12px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    /* ===== CARDS ===== */

    .cards {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .card-metric {
        flex: 1;
        min-width: 180px;
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #f97316;
    }

    .metric-title {
        font-size: 13px;
        color: #64748b;
    }

    .metric-value {
        font-size: 26px;
        font-weight: 700;
        color: #ea580c;
    }

    /* ===== TABELA ===== */

    .table-modern {
        width: 100%;
        border-collapse: collapse;
    }

    .table-modern th {
        background: #fff7ed;
        color: #c2410c;
        padding: 12px;
        text-align: left;
        font-weight: 600;
    }

    .table-modern td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .table-modern tr:hover {
        background: #fffaf5;
    }

    /* ===== BADGES ===== */

    .badge {
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-ativa {
        background: #dcfce7;
        color: #166534;
    }

    .badge-pendente {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-inativa {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-vencido {
        background: #e5e7eb;
        color: #334155;
    }

    /* ===== BOTÕES LARANJA ===== */

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 14px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.25s;
    }

    .btn-aprovar {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
    }

    .btn-gerenciar {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        box-shadow: 0 2px 6px rgba(249, 115, 22, 0.3);
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* ===== MOBILE ===== */

    .mobile-card {
        display: none;
        background: white;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #f97316;
    }

    .mobile-row {
        font-size: 13px;
        margin-top: 4px;
        color: #475569;
    }

    /* ===== MODAL ===== */

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    .modal-box {
        background: white;
        padding: 20px;
        border-radius: 10px;
        width: 100%;
        max-width: 420px;
    }

    .modal-actions {
        display: flex;
        gap: 10px; /* espaço entre os botões */
    }
    
    /* ===== RESPONSIVO ===== */

    @media(max-width:900px) {

        .table-desktop {
            display: none;
        }

        .mobile-card {
            display: block;
        }

        .btn {
            width: 100%;
            padding: 12px;
            font-size: 14px;
        }

    }

    .filtros {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 25px;
        background: #ffffff;
        padding: 18px;
        border-radius: 12px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
        align-items: flex-end;
    }

    .filtros input,
    .filtros select {
        min-width: 200px;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 14px;
        transition: 0.2s;
    }

    .filtros input:focus,
    .filtros select:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249,115,22,0.15);
    }

    /* botão alinhado corretamente */
    .filtros .btn {
        height: 40px;
        padding: 0 18px;
        white-space: nowrap;
    }

    /* RESPONSIVO */

    @media (max-width: 768px) {
        .filtros {
            flex-direction: column;
            align-items: stretch;
        }

        .filtros input,
        .filtros select,
        .filtros .btn {
            width: 100%;
            min-width: unset;
        }
    }
    </style>
</head>

<body>

    <?php include '../base/navbarUser.php'; ?>

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
                            <small><?= $emp['Email'] ?></small>
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

                            <form method="post" style="display:inline">
                                <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                                <input type="hidden" name="email" value="<?= $emp['Email'] ?>">
                                <input type="hidden" name="acao" value="aprovar">
                                <button class="btn btn-aprovar">✔ Aprovar</button>
                            </form>

                            <button class="btn btn-gerenciar" onclick="abrirModal(
                                <?= $emp['id'] ?>,
                                '<?= $emp['Plano'] ?>',
                                '<?= $emp['Status'] ?>',
                                '<?= $emp['ValidadePlano'] ?>'
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

                <form method="post">
                    <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                    <input type="hidden" name="acao" value="aprovar">
                    <button class="btn btn-aprovar">✔ Aprovar</button>
                </form>

                <button class="btn btn-gerenciar" onclick="abrirModal(
                    <?= $emp['id'] ?>,
                    '<?= $emp['Plano'] ?>',
                    '<?= $emp['Status'] ?>',
                    '<?= $emp['ValidadePlano'] ?>'
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

                <br><br>

                <div class="modal-actions">
                    <button class="btn btn-gerenciar">Salvar</button>
                    <button type="button" class="btn" onclick="fecharModal()">Fechar</button>
                </div>

            </form>

        </div>
    </div>

<script>
    function abrirModal(id, plano, status, validade) {

        document.getElementById('modal').style.display = 'flex';

        mId.value = id;
        mPlano.value = plano;
        mStatus.value = status;
        mValidade.value = validade;

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
    
</script>

</body>

</html>