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

$msgRetorno = '';
$tipoMsg = '';

// Recupera mensagens temporárias definidas pelo cadastro/edição/exclusão do grupo.
if (isset($_SESSION['mensagem_sucesso'])) {
    $msgRetorno = (string)$_SESSION['mensagem_sucesso'];
    $tipoMsg = 'success';
    unset($_SESSION['mensagem_sucesso']);
}

if (isset($_SESSION['mensagem_erro'])) {
    $msgRetorno = (string)$_SESSION['mensagem_erro'];
    $tipoMsg = 'error';
    unset($_SESSION['mensagem_erro']);
}

$idEmpresa = (int)($_SESSION['idEmpresa'] ?? 0);

$listaGrupos = ExSqlNET(
    "SELECT Id, Inativo, Nome AS Descricao,
        CASE Tipo WHEN 'P' THEN 'Produtos' WHEN 'C' THEN 'Clientes' ELSE 'Outros' END AS TipoLiteral
     FROM grupos
     WHERE idEmpresa = ?
     ORDER BY Descricao",
    null,
    [$idEmpresa]
);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Grupos | Autodoc Gerencial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <style>
        :root {
            --pd-orange: #f97316;
            --pd-orange-dark: #ea580c;
            --pd-orange-soft: #fff7ed;
            --pd-text: #1f2937;
            --pd-muted: #6b7280;
            --pd-border: #e5e7eb;
            --pd-bg: #f8fafc;
            --pd-green: #15803d;
            --pd-red: #b91c1c;
        }
        body { background: var(--pd-bg); color: var(--pd-text); }
        .content { max-width: 1440px; margin: 0 auto; padding: 28px 24px 40px; }
        .grupos-header { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:24px; }
        .grupos-titulo { margin:0; color:#111827; font-size:clamp(23px,3vw,29px); font-weight:750; letter-spacing:-.7px; line-height:1.2; }
        .grupos-subtitulo { margin:8px 0 0; color:var(--pd-muted); font-size:14px; line-height:1.5; }
        .btn-novo-grupo { display:inline-flex; align-items:center; justify-content:center; gap:8px; flex-shrink:0; padding:11px 16px; border:1px solid var(--pd-orange); border-radius:9px; background:var(--pd-orange); color:#fff!important; font-size:14px; font-weight:650; text-decoration:none; box-shadow:0 2px 4px rgba(234,88,12,.12); transition:background .18s ease,transform .18s ease,box-shadow .18s ease; }
        .btn-novo-grupo:hover { background:var(--pd-orange-dark); box-shadow:0 4px 10px rgba(234,88,12,.18); transform:translateY(-1px); }
        .mensagem-grupo { margin:0 0 18px; padding:12px 15px; border:1px solid transparent; border-radius:9px; font-size:14px; }
        .mensagem-grupo.success { color:#166534; background:#f0fdf4; border-color:#bbf7d0; }
        .mensagem-grupo.error { color:#991b1b; background:#fef2f2; border-color:#fecaca; }
        .grupos-card { overflow:hidden; border:1px solid var(--pd-border); border-radius:13px; background:#fff; box-shadow:0 3px 12px rgba(15,23,42,.035); }
        .grupos-toolbar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; padding:18px 20px; border-bottom:1px solid #f1f5f9; }
        .grupos-toolbar-titulo { margin:0; color:#111827; font-size:15px; font-weight:700; }
        .grupos-toolbar-descricao { margin:4px 0 0; color:var(--pd-muted); font-size:12px; }
        .pesquisa-grupos { position:relative; width:min(100%,340px); }
        .pesquisa-grupos svg { position:absolute; left:12px; top:50%; width:17px; height:17px; color:#9ca3af; transform:translateY(-50%); pointer-events:none; }
        .pesquisa-grupos input { display:block; width:100%; height:40px; box-sizing:border-box; padding:9px 12px 9px 38px; border:1px solid #d1d5db; border-radius:8px; outline:none; background:#fff; color:var(--pd-text); font-family:inherit; font-size:13px; transition:border-color .18s ease,box-shadow .18s ease; }
        .pesquisa-grupos input:focus { border-color:var(--pd-orange); box-shadow:0 0 0 3px rgba(249,115,22,.13); }
        .tabela-grupos-wrap { width:100%; overflow-x:auto; }
        .tabela-grupos { width:100%; border-collapse:separate; border-spacing:0; text-align:left; }
        .tabela-grupos thead th { padding:12px 18px; border-bottom:1px solid var(--pd-border); background:#f9fafb; color:#6b7280; font-size:11px; font-weight:750; letter-spacing:.055em; text-transform:uppercase; white-space:nowrap; }
        .tabela-grupos tbody td { padding:15px 18px; border-bottom:1px solid #f1f5f9; color:#374151; font-size:13px; vertical-align:middle; }
        .tabela-grupos tbody tr { cursor:pointer; transition:background .15s ease; }
        .tabela-grupos tbody tr:hover { background:#fffaf5; }
        .tabela-grupos tbody tr:last-child td { border-bottom:0; }
        .nome-grupo { color:#111827!important; font-weight:650; min-width:180px; }
        .tipo-badge { display:inline-flex; align-items:center; padding:4px 9px; border:1px solid #fed7aa; border-radius:999px; background:var(--pd-orange-soft); color:#c2410c; font-size:11px; font-weight:650; white-space:nowrap; }
        .status-badge { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:999px; font-size:11px; font-weight:650; white-space:nowrap; }
        .status-badge::before { width:6px; height:6px; border-radius:50%; content:""; }
        .status-badge.ativo { background:#f0fdf4; color:var(--pd-green); }
        .status-badge.ativo::before { background:#22c55e; }
        .status-badge.inativo { background:#fef2f2; color:var(--pd-red); }
        .status-badge.inativo::before { background:#ef4444; }
        .empty-state { padding:38px 20px!important; color:#9ca3af!important; text-align:center; cursor:default!important; }
        .grupos-footer { padding:12px 20px; border-top:1px solid #f1f5f9; color:#9ca3af; font-size:12px; }
        @media(max-width:700px) { .content { padding:20px 12px 30px; } .grupos-header { flex-direction:column; align-items:stretch; gap:16px; } .btn-novo-grupo { align-self:flex-start; } .grupos-toolbar { align-items:stretch; padding:16px; } .pesquisa-grupos { width:100%; max-width:none; } }
        @media(max-width:560px) { .content { padding:18px 12px 28px; } .btn-novo-grupo { width:auto; max-width:100%; box-sizing:border-box; align-self:flex-start; } .grupos-toolbar { padding:14px; } .tabela-grupos { min-width:520px; } .tabela-grupos thead th,.tabela-grupos tbody td { padding:12px; } }
        @media(prefers-reduced-motion:reduce) { *,*::before,*::after { transition:none!important; animation:none!important; scroll-behavior:auto!important; } }
    </style>
</head>
<body>
<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">
    <header class="grupos-header">
        <div>
            <h1 class="grupos-titulo">Grupos</h1>
            <p class="grupos-subtitulo">Gerencie os grupos de produtos e clientes cadastrados no sistema.</p>
        </div>
        <a href="Grupo" class="btn-novo-grupo"><span aria-hidden="true">+</span> Novo cadastro</a>
    </header>

    <?php if ($msgRetorno !== ''): ?>
        <div class="mensagem-grupo <?= htmlspecialchars($tipoMsg, ENT_QUOTES, 'UTF-8') ?>" role="status">
            <?= htmlspecialchars($msgRetorno, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <section class="grupos-card">
        <div class="grupos-toolbar">
            <div>
                <h2 class="grupos-toolbar-titulo">Grupos cadastrados</h2>
                <p class="grupos-toolbar-descricao" id="descricaoGrupos">Consulte e gerencie os grupos.</p>
            </div>
            <div class="pesquisa-grupos" role="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path>
                </svg>
                <input type="search" id="filtroTabela" placeholder="Buscar grupo..." autocomplete="off" aria-label="Buscar grupo por descrição, tipo ou status" autofocus>
            </div>
        </div>

        <div class="tabela-grupos-wrap">
            <table class="tabela-grupos">
                <thead><tr><th>Descrição</th><th>Tipo</th><th>Status</th></tr></thead>
                <tbody id="gruposTabela">
                <?php if (!empty($listaGrupos)): ?>
                    <?php foreach ($listaGrupos as $grupo): ?>
                        <?php
                            $grupoId = (int)($grupo['Id'] ?? 0);
                            $descricao = (string)($grupo['Descricao'] ?? '');
                            $inativo = (int)($grupo['Inativo'] ?? 0) === 1;
                        ?>
                        <tr tabindex="0" role="link" aria-label="Editar grupo <?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?>" onclick="selecionarGrupo(<?= $grupoId ?>)" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();selecionarGrupo(<?= $grupoId ?>)}">
                            <td class="nome-grupo"><?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="tipo-badge"><?= htmlspecialchars((string)($grupo['TipoLiteral'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><span class="status-badge <?= $inativo ? 'inativo' : 'ativo' ?>"><?= $inativo ? 'Inativo' : 'Ativo' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="empty-state">Nenhum grupo cadastrado.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="grupos-footer" id="contadorGrupos"></div>
    </section>
</div>

<script>
function selecionarGrupo(codigo) {
    window.location.href = 'Grupo?Codigo=' + encodeURIComponent(codigo);
}

function filtrarTabela() {
    const input = document.getElementById('filtroTabela');
    const filtro = input.value.trim().toLocaleLowerCase('pt-BR');
    const tabela = document.getElementById('gruposTabela');
    const linhas = Array.from(tabela.querySelectorAll('tr'));
    let encontrados = 0;
    const linhasDados = linhas.filter(linha => !linha.querySelector('.empty-state'));

    linhasDados.forEach(linha => {
        const corresponde = linha.innerText.toLocaleLowerCase('pt-BR').includes(filtro);
        linha.style.display = corresponde ? '' : 'none';
        if (corresponde) encontrados++;
    });

    const contador = document.getElementById('contadorGrupos');
    const descricao = document.getElementById('descricaoGrupos');
    if (linhasDados.length) {
        contador.textContent = encontrados + (encontrados === 1 ? ' grupo encontrado' : ' grupos encontrados');
        descricao.textContent = 'Total de ' + linhasDados.length + (linhasDados.length === 1 ? ' grupo cadastrado' : ' grupos cadastrados');
    } else {
        contador.textContent = '';
        descricao.textContent = 'Nenhum grupo cadastrado';
    }
}

document.getElementById('filtroTabela').addEventListener('input', filtrarTabela);
filtrarTabela();
</script>
</body>
</html>
