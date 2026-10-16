<?php
require_once __DIR__ . '/../base/connection.php';
require_once __DIR__ . '/../base/baseFuncoes.php';
require_once __DIR__ . '/../base/verificaPlano.php';

$msgRetorno = '';
$tipoMsg = '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: Login');
    exit;
}

$registrosPorPagina = 30;

$idEmpresa = (int)($_SESSION['idEmpresa'] ?? 0);
$busca = trim((string)($_GET['busca'] ?? ''));
$pagina = max(1, (int)($_GET['pagina'] ?? 1));

// Filtro aplicado tanto à contagem quanto à consulta paginada.
$filtroSQL = ' WHERE idEmpresa = ? ';
$parametros = [$idEmpresa];

if ($busca !== '') {
    $filtroSQL .= ' AND (Descricao LIKE ? OR CAST(Categoria AS CHAR) LIKE ? OR '
        . "CASE Categoria WHEN 1 THEN 'Fixa' WHEN 2 THEN 'Variável' WHEN 3 THEN 'Impostos' WHEN 4 THEN 'Serviços' WHEN 5 THEN 'Outros' END LIKE ? "
        . " OR CASE Acao WHEN 1 THEN 'Entrada' WHEN -1 THEN 'Saída' ELSE 'Sem impacto' END LIKE ? "
        . " OR CASE Inativo WHEN 0 THEN 'Ativo' ELSE 'Inativo' END LIKE ? ) ";
    $termoBusca = '%' . $busca . '%';
    array_push($parametros, $termoBusca, $termoBusca, $termoBusca, $termoBusca, $termoBusca);
}

$resultadoContagem = ExSqlNET(
    'SELECT COUNT(*) AS total FROM tipodespesa ' . $filtroSQL,
    null,
    $parametros
);
$totalRegistros = (int)($resultadoContagem[0]['total'] ?? 0);
$totalPaginas = max(1, (int)ceil($totalRegistros / $registrosPorPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $registrosPorPagina;

// Consulta somente os registros da página atual.
$listaDespesa = ExSqlNET(" 
    SELECT *,
        CASE Categoria
            WHEN 1 THEN 'Fixa'
            WHEN 2 THEN 'Variável'
            WHEN 3 THEN 'Impostos'
            WHEN 4 THEN 'Serviços'
            WHEN 5 THEN 'Outros'
        END AS CategoriaLiteral
    FROM tipodespesa
    $filtroSQL
    ORDER BY Descricao, id
    LIMIT $registrosPorPagina OFFSET $offset
", null, $parametros);

$parametrosBase = [];
if ($busca !== '') {
    $parametrosBase['busca'] = $busca;
}
$urlPagina = function (int $numeroPagina) use ($parametrosBase): string {
    return '?' . http_build_query(array_merge($parametrosBase, ['pagina' => $numeroPagina]));
};
$urlPrimeira = $urlPagina(1);
$urlAnterior = $urlPagina(max(1, $pagina - 1));
$urlProxima = $urlPagina(min($totalPaginas, $pagina + 1));
$urlUltima = $urlPagina($totalPaginas);
$inicioExibicao = $totalRegistros > 0 ? $offset + 1 : 0;
$fimExibicao = min($offset + $registrosPorPagina, $totalRegistros);

if (isset($_SESSION['mensagem_sucesso'])) {
    $msgRetorno = $_SESSION['mensagem_sucesso'];
    $tipoMsg = 'success';
    unset($_SESSION['mensagem_sucesso']);
}

if (isset($_SESSION['mensagem_erro'])) {
    $msgRetorno = $_SESSION['mensagem_erro'];
    $tipoMsg = 'error';
    unset($_SESSION['mensagem_erro']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Despesas e Contas | Autodoc</title>
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

        body {
            background: var(--pd-bg);
            color: var(--pd-text);
        }

        .content {
            max-width: 1440px;
            margin: 0 auto;
            padding: 28px 24px 40px;
        }

        .clientes-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
        }

        .clientes-titulo {
            margin: 0;
            color: #111827;
            font-size: clamp(23px, 3vw, 29px);
            font-weight: 750;
            letter-spacing: -0.7px;
            line-height: 1.2;
        }

        .clientes-subtitulo {
            margin: 8px 0 0;
            color: var(--pd-muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .btn-novo-cliente {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-shrink: 0;
            padding: 11px 16px;
            border: 1px solid var(--pd-orange);
            border-radius: 9px;
            background: var(--pd-orange);
            color: #fff !important;
            font-size: 14px;
            font-weight: 650;
            text-decoration: none;
            box-shadow: 0 2px 4px rgba(234, 88, 12, .12);
            transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
        }

        .btn-novo-cliente:hover {
            background: var(--pd-orange-dark);
            box-shadow: 0 4px 10px rgba(234, 88, 12, .18);
            transform: translateY(-1px);
        }

        .mensagem-cliente {
            margin: 0 0 18px;
            padding: 12px 15px;
            border: 1px solid transparent;
            border-radius: 9px;
            font-size: 14px;
        }

        .mensagem-cliente.success {
            color: #166534;
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .mensagem-cliente.error {
            color: #991b1b;
            background: #fef2f2;
            border-color: #fecaca;
        }

        .clientes-card {
            overflow: hidden;
            border: 1px solid var(--pd-border);
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
        }

        .clientes-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .clientes-toolbar-titulo {
            margin: 0;
            color: #111827;
            font-size: 15px;
            font-weight: 700;
        }

        .clientes-toolbar-descricao {
            margin: 4px 0 0;
            color: var(--pd-muted);
            font-size: 12px;
        }

        .pesquisa-clientes {
            position: relative;
            width: min(100%, 340px);
        }

        .pesquisa-clientes svg {
            position: absolute;
            left: 12px;
            top: 50%;
            width: 17px;
            height: 17px;
            color: #9ca3af;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .pesquisa-clientes input {
            display: block;
            width: 100%;
            height: 40px;
            box-sizing: border-box;
            padding: 9px 12px 9px 38px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            background: #fff;
            color: var(--pd-text);
            font-family: inherit;
            font-size: 13px;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .pesquisa-clientes input:focus {
            border-color: var(--pd-orange);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, .13);
        }

        .tabela-clientes-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .tabela-clientes {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
        }

        .tabela-clientes thead th {
            padding: 12px 18px;
            border-bottom: 1px solid var(--pd-border);
            background: #f9fafb;
            color: #6b7280;
            font-size: 11px;
            font-weight: 750;
            letter-spacing: .055em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .tabela-clientes tbody td {
            padding: 15px 18px;
            border-bottom: 1px solid #f1f5f9;
            color: #374151;
            font-size: 13px;
            vertical-align: middle;
        }

        .tabela-clientes tbody tr {
            transition: background .15s ease;
        }

        .tabela-clientes tbody tr:hover {
            background: #fffaf5;
        }

        .tabela-clientes tbody tr:last-child td {
            border-bottom: 0;
        }

        .nome-cliente {
            color: #111827 !important;
            font-weight: 650;
            min-width: 180px;
        }

        .tipo-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 9px;
            border: 1px solid #fed7aa;
            border-radius: 999px;
            background: var(--pd-orange-soft);
            color: #c2410c;
            font-size: 11px;
            font-weight: 650;
            white-space: nowrap;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 650;
            white-space: nowrap;
        }

        .status-badge::before {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            content: "";
        }

        .status-badge.ativo {
            background: #f0fdf4;
            color: var(--pd-green);
        }

        .status-badge.ativo::before {
            background: #22c55e;
        }

        .status-badge.inativo {
            background: #fef2f2;
            color: var(--pd-red);
        }

        .status-badge.inativo::before {
            background: #ef4444;
        }

        .sem-registros {
            padding: 38px 20px !important;
            color: var(--pd-muted) !important;
            text-align: center;
        }

        .paginacao-clientes {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            padding: 15px 20px;
            border-top: 1px solid var(--pd-border);
            background: #fff;
        }

        .paginacao-info {
            color: var(--pd-muted);
            font-size: 12px;
        }

        .paginacao-info strong {
            color: #374151;
            font-weight: 700;
        }

        .paginacao-controles {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .paginacao-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 33px;
            height: 33px;
            box-sizing: border-box;
            padding: 0 9px;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            background: #fff;
            color: #4b5563;
            text-decoration: none;
            font-size: 15px;
            transition: .15s ease;
        }

        .paginacao-link:hover:not(.desabilitado) {
            border-color: #fdba74;
            background: var(--pd-orange-soft);
            color: var(--pd-orange-dark);
        }

        .paginacao-link.desabilitado {
            pointer-events: none;
            color: #cbd5e1;
            background: #f9fafb;
        }

        .paginacao-pagina {
            padding: 0 9px;
            color: #6b7280;
            font-size: 12px;
            white-space: nowrap;
        }

        .paginacao-pagina strong {
            color: #111827;
        }

        @media (max-width: 700px) {
            .content {
                padding: 20px 12px 30px;
            }

            .clientes-header {
                align-items: stretch;
                flex-direction: column;
                gap: 15px;
                margin-bottom: 18px;
            }

            .btn-novo-cliente {
                align-self: flex-start;
            }

            .clientes-toolbar {
                align-items: stretch;
                flex-direction: column;
                padding: 16px;
            }

            .pesquisa-clientes {
                width: 100%;
            }

            .tabela-clientes thead th,
            .tabela-clientes tbody td {
                padding: 12px;
            }

            .paginacao-clientes {
                align-items: flex-start;
                flex-direction: column;
                padding: 14px 16px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                transition-duration: .01ms !important;
            }
        }

        .valor-devedor {
            font-weight: 650;
            white-space: nowrap;
        }

        .valor-devedor.devedor {
            color: #b91c1c;
        }

        .valor-devedor.sem-debito {
            color: #15803d;
        }

        .tipo-badge {
            border-color: #e5e7eb;
            background: #f8fafc;
            color: #475569;
        }

        .tipo-badge.destaque {
            border-color: #fed7aa;
            background: var(--pd-orange-soft);
            color: #c2410c;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">
    <header class="clientes-header">
        <div>
            <h1 class="clientes-titulo">Despesas e Contas</h1>
            <p class="clientes-subtitulo">
                Cadastro de todas as despesas e contas do sistema.
            </p>
        </div>

        <a href="Despesa" class="btn-novo-cliente">
            <span aria-hidden="true">+</span>
            Nova despesa
        </a>
    </header>

    <?php if ($msgRetorno !== ''): ?>
        <div class="mensagem-cliente <?= htmlspecialchars($tipoMsg, ENT_QUOTES, 'UTF-8') ?>" role="status">
            <?= htmlspecialchars((string)$msgRetorno, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <section class="clientes-card">
        <div class="clientes-toolbar">
            <div>
                <h2 class="clientes-toolbar-titulo">Despesas cadastradas</h2>
                <p class="clientes-toolbar-descricao">
                    <?= number_format($totalRegistros, 0, ',', '.') ?>
                    <?= $totalRegistros === 1 ? 'despesa encontrada' : 'despesas encontradas' ?>
                </p>
            </div>

            <form method="GET" id="formPesquisa" class="pesquisa-clientes" role="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m20 20-4-4"></path>
                </svg>

                <input
                    type="search"
                    name="busca"
                    id="filtroTabela"
                    value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Buscar despesa..."
                    autocomplete="off"
                    aria-label="Buscar despesa por descrição, categoria, ação ou status"
                >
                <input type="hidden" name="pagina" value="1">
            </form>
        </div>

        <div class="tabela-clientes-wrap">
            <table class="tabela-clientes">
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Ação</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="corpoTabela">
                <?php if (!empty($listaDespesa)): ?>
                    <?php foreach ($listaDespesa as $despesa): ?>
                        <?php
                            $idDespesa = (int)($despesa['id'] ?? 0);
                            $descricao = (string)($despesa['Descricao'] ?? '');
                            $categoria = (string)($despesa['CategoriaLiteral'] ?? '');
                            $acao = (int)($despesa['Acao'] ?? 0);
                            $inativo = (int)($despesa['Inativo'] ?? 0);

                            if ($acao === 1) {
                                $impactoTexto = 'Entrada';
                                $impactoClasse = 'impacto-entrada';
                            } elseif ($acao === -1) {
                                $impactoTexto = 'Saída';
                                $impactoClasse = 'impacto-saida';
                            } else {
                                $impactoTexto = 'Sem impacto';
                                $impactoClasse = 'impacto-neutro';
                            }
                        ?>
                        <tr onclick="abrirDespesa(<?= $idDespesa ?>)" tabindex="0"
                            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();abrirDespesa(<?= $idDespesa ?>)}"
                            style="cursor:pointer;">
                            <td class="nome-cliente">
                                <?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <span class="tipo-badge">
                                    <?= htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <span class="tipo-badge <?= $impactoClasse ?>">
                                    <?= htmlspecialchars($impactoTexto, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?= $inativo ? 'inativo' : 'ativo' ?>">
                                    <?= $inativo ? 'Inativo' : 'Ativo' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="sem-registros"><?= $busca !== '' ? 'Nenhuma despesa encontrada para essa pesquisa.' : 'Nenhuma despesa cadastrada ainda.' ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <footer class="paginacao-clientes">
            <div class="paginacao-info">
                Exibindo <strong><?= number_format($inicioExibicao, 0, ',', '.') ?>–<?= number_format($fimExibicao, 0, ',', '.') ?></strong>
                de <strong><?= number_format($totalRegistros, 0, ',', '.') ?></strong> despesas
                <span> · <?= number_format($registrosPorPagina, 0, ',', '.') ?> por página</span>
            </div>
            <nav class="paginacao-controles" aria-label="Paginação de despesas">
                <a class="paginacao-link <?= $pagina <= 1 ? 'desabilitado' : '' ?>" href="<?= htmlspecialchars($urlPrimeira, ENT_QUOTES, 'UTF-8') ?>" aria-label="Primeira página" title="Primeira página">«</a>
                <a class="paginacao-link <?= $pagina <= 1 ? 'desabilitado' : '' ?>" href="<?= htmlspecialchars($urlAnterior, ENT_QUOTES, 'UTF-8') ?>" aria-label="Página anterior" title="Página anterior">‹</a>
                <span class="paginacao-pagina">Página <strong><?= number_format($pagina, 0, ',', '.') ?></strong> de <strong><?= number_format($totalPaginas, 0, ',', '.') ?></strong></span>
                <a class="paginacao-link <?= $pagina >= $totalPaginas ? 'desabilitado' : '' ?>" href="<?= htmlspecialchars($urlProxima, ENT_QUOTES, 'UTF-8') ?>" aria-label="Próxima página" title="Próxima página">›</a>
                <a class="paginacao-link <?= $pagina >= $totalPaginas ? 'desabilitado' : '' ?>" href="<?= htmlspecialchars($urlUltima, ENT_QUOTES, 'UTF-8') ?>" aria-label="Última página" title="Última página">»</a>
            </nav>
        </footer>
    </section>
</div>

<style>
    /* Cores específicas dos marcadores de ação; o restante segue o CSS da lista de clientes. */
    .tipo-badge.impacto-entrada { border-color:#bbf7d0; background:#f0fdf4; color:#166534; }
    .tipo-badge.impacto-saida { border-color:#fecdd3; background:#fff1f2; color:#9f1239; }
    .tipo-badge.impacto-neutro { border-color:#e5e7eb; background:#f9fafb; color:#4b5563; }
</style>

<script>
const campoPesquisa = document.getElementById('filtroTabela');
const formularioPesquisa = document.getElementById('formPesquisa');
let temporizadorPesquisa;

campoPesquisa.addEventListener('input', function () {
    clearTimeout(temporizadorPesquisa);
    temporizadorPesquisa = setTimeout(function () {
        formularioPesquisa.requestSubmit();
    }, 400);
});

<?php if ($busca !== ''): ?>
window.addEventListener('DOMContentLoaded', function () {
    campoPesquisa.focus();
    const tamanhoTexto = campoPesquisa.value.length;
    campoPesquisa.setSelectionRange(tamanhoTexto, tamanhoTexto);
});
<?php endif; ?>

function abrirDespesa(id) {
    window.location.href = 'Despesa?Codigo=' + encodeURIComponent(id);
}
</script>
</body>
</html>
