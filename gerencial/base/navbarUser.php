<script src="/gerencial/base/base.js"></script>

<?php
require_once  __DIR__ .'/../base/verificaPlano.php';
require_once __DIR__ . '/../base/permissoes.php';

require_once  __DIR__ .'/../base/verificaAcessoTela.php';
$homeLink = 'Home';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: /gerencial/Login");
    exit;
}

global $currentPage;
// $currentPage = basename($_SERVER['PHP_SELF']);
$currentPage = $_GET['url'] ?? '';

?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
:root {
    --nav-bg: #ffffff;
    --nav-text: #334155;
    --nav-muted: #94a3b8;
    --nav-border: #e8edf3;
    --nav-accent: #ea580c;
    --nav-accent-dark: #c2410c;
    --nav-accent-soft: #fff7ed;
    --nav-shadow: 8px 0 28px rgba(15, 23, 42, .06);
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Inter', sans-serif;
}

.navbar {
    position: fixed;
    inset: 0 auto 0 0;
    width: 258px;
    height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--nav-bg);
    border-right: 1px solid var(--nav-border);
    box-shadow: var(--nav-shadow);
    z-index: 2000;
    overflow: visible;
    transition: width .28s ease, transform .28s ease, left .28s ease;
}

.navbar.collapsed {
    width: 0;
    border-right: 0;
    box-shadow: none;
}

.navbar-scroll {
    height: 100%;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.navbar-scroll::-webkit-scrollbar { width: 5px; }
.navbar-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 20px;
}

.logo {
    min-height: 124px;
    margin: 0 16px 12px;
    padding: 20px 14px 16px !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
}

.logo-img {
    display: block;
    width: 100%;
    max-width: 164px;
    height: 92px;
    object-fit: contain;
    padding: 4px;
    border-radius: 14px;
    background: #fff;
}

.navbar ul {
    list-style: none;
    padding: 8px 0 14px;
    margin: 0;
}

.menu-group {
    padding: 20px 22px 8px;
    color: #94a3b8;
    font-size: 10px;
    line-height: 1.2;
    font-weight: 700;
    letter-spacing: .11em;
    text-transform: uppercase;
}

.navbar ul li { margin: 2px 0; }

.navbar ul li a {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 43px;
    margin: 3px 12px;
    padding: 11px 14px;
    border: 1px solid transparent;
    border-radius: 10px;
    color: var(--nav-text);
    text-decoration: none;
    font-size: 13px;
    line-height: 1.35;
    font-weight: 500;
    transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease;
}

.navbar ul li a i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 19px;
    width: 19px;
    margin: 0;
    color: #64748b;
    font-size: 15px;
    transition: color .18s ease;
}

.navbar ul li a:hover {
    background: #f8fafc;
    border-color: #eef2f7;
    color: var(--nav-accent-dark);
    transform: translateX(2px);
}

.navbar ul li a:hover i { color: var(--nav-accent); }

.navbar ul li a.active {
    background: var(--nav-accent-soft);
    border-color: #fed7aa;
    color: var(--nav-accent-dark);
    font-weight: 650;
    box-shadow: 0 2px 5px rgba(234, 88, 12, .045);
}

.navbar ul li a.active::before {
    content: "";
    position: absolute;
    left: -1px;
    top: 8px;
    bottom: 8px;
    width: 3px;
    border-radius: 0 4px 4px 0;
    background: var(--nav-accent);
}

.navbar ul li a.active i { color: var(--nav-accent); }

.logout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    margin: 10px 12px 16px;
    padding: 12px 16px;
    border: 1px solid #fed7d7;
    border-radius: 10px;
    background: #fff;
    color: #b91c1c;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: background .18s ease, border-color .18s ease, transform .18s ease;
}

.logout::before {
    content: "\f2f5";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 14px;
}

.logout:hover {
    background: #fef2f2;
    border-color: #fecaca;
    transform: translateY(-1px);
}

.content {
    margin-left: 258px;
    padding: 25px;
    transition: margin-left .28s ease;
}

.content.expanded { margin-left: 0; }

.collapse-btn {
    position: fixed;
    top: 24px;
    left: 246px;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    border-radius: 50%;
    background: #fff;
    color: #64748b;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .12);
    cursor: pointer;
    font-size: 10px;
    z-index: 5000;
    transition: left .28s ease, color .18s ease, background .18s ease;
}

.collapse-btn:hover {
    color: var(--nav-accent);
    background: #fff7ed;
}

.navbar.collapsed .collapse-btn { left: 12px; }

.menu-toggle {
    display: none;
    position: fixed;
    top: 16px;
    left: 16px;
    width: 44px;
    height: 44px;
    padding: 0;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, .10);
    cursor: pointer;
    z-index: 3000;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
}

.menu-toggle span {
    display: block;
    width: 19px;
    height: 2px;
    border-radius: 3px;
    background: var(--nav-accent);
}

.overlay { display: none; }

@media (max-width: 768px) {
    .content {
        margin-left: 0 !important;
        padding: 76px 16px 20px;
    }

    .collapse-btn { display: none; }

    .menu-toggle { display: flex; }

    .navbar {
        left: 0;
        width: min(286px, 86vw);
        transform: translateX(-105%);
        box-shadow: 12px 0 32px rgba(15, 23, 42, .16);
    }

    .navbar.open { transform: translateX(0); }

    .navbar.collapsed {
        width: min(286px, 86vw);
        transform: translateX(-105%);
    }

    .overlay {
        position: fixed;
        inset: 0;
        display: block;
        background: rgba(15, 23, 42, .42);
        opacity: 0;
        visibility: hidden;
        transition: opacity .25s ease, visibility .25s ease;
        z-index: 1500;
    }

    .overlay.show {
        opacity: 1;
        visibility: visible;
    }
}

@media (prefers-reduced-motion: reduce) {
    .navbar, .content, .navbar ul li a, .logout, .overlay {
        transition: none !important;
    }
}
</style>



<button class="menu-toggle" onclick="toggleMenu()">
    <span></span>
    <span></span>
    <span></span>
</button>

<div class="overlay" onclick="toggleMenu()"></div>

<nav class="navbar">

    <div class="navbar-scroll">

        <button class="collapse-btn" onclick="toggleSidebarDesktop()">
        ❮
        </button>

        <a href="<?= $homeLink ?>" class="logo" aria-label="Página inicial">
            <img src="/gerencial/img/logoo.png" class="logo-img" alt="Logo do sistema">
        </a>

        <ul>

            <?php
                $isAdminGeral = isset($_SESSION['AdminGeral']) && $_SESSION['AdminGeral'] == 1;
                $isUsuarioAdmin = isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] == 2;
            ?>

            <div class="menu-group">Operacional</div>

            <?php if ($isAdminGeral || podeAcessar('frmHome.php')): ?>
            <li>
                <a href="/gerencial/Home"
                class="<?= $currentPage == 'gerencial/Home' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i>
                Inicial
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmLancamentoLista.php')): ?>
            <li>
                <a href="/gerencial/Pedidos?limparFiltros=1"
                class="<?= in_array($currentPage, ['gerencial/Pedidos','gerencial/Pedido']) ? 'active' : '' ?>">
                <i class="fa-solid fa-cart-shopping"></i>
                    Pedidos e Vendas
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmMultaLista.php')): ?>
            <li>
                <a href="/gerencial/Multas?limparFiltros=1"
                class="<?= in_array($currentPage, ['gerencial/Multas','gerencial/Multa']) ? 'active' : '' ?>">
                <i class="fa-solid fa-car-burst"></i>
                Multas
                </a>
            </li>
            <?php endif; ?>

            <div class="menu-group">Financeiro</div>

            <?php if ($isAdminGeral || podeAcessar('frmBoletimCaixa.php')): ?>
            <li>
                <a href="/gerencial/BoletimCaixa"
                class="<?= in_array($currentPage, ['gerencial/BoletimCaixa','gerencial/BoletimCaixa/Lancamento']) ? 'active' : '' ?>">
                <i class="fa-solid fa-cash-register"></i>
                Boletim de Caixa
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmFinanceiro.php')): ?>
            <li>
                <a href="/gerencial/Financeiro"
                class="<?= $currentPage == 'gerencial/Financeiro' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i>
                Financeiro Geral
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmPatrimonio.php')): ?>
            <li>
                <a href="/gerencial/Patrimonio"
                class="<?= $currentPage == 'gerencial/Patrimonio' ? 'active' : '' ?>">
                <i class="fa-solid fa-boxes-stacked"></i>
                Compras e Patrimônio
                </a>
            </li>
            <?php endif; ?>

            <div class="menu-group">Cadastros</div>

            <?php if ($isAdminGeral || podeAcessar('frmServProdLista.php')): ?>
            <li>
                <a href="/gerencial/Produtos"
                class="<?= in_array($currentPage, ['gerencial/Produtos','gerencial/Produto']) ? 'active' : '' ?>">
                <i class="fa-solid fa-box"></i>
                Produtos / Serviços
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmForcliLista.php')): ?>
            <li>
                <a href="/gerencial/Clientes"
                class="<?= in_array($currentPage, ['gerencial/Clientes','gerencial/Cliente']) ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                Clientes / Fornecedores
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmGrupo.php')): ?>
            <li>
                <a href="/gerencial/Grupos"
                class="<?= in_array($currentPage, ['gerencial/Grupos','gerencial/Grupo']) ? 'active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i>
                Grupos
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmDespesasLista.php')): ?>
            <li>
                <a href="/gerencial/Despesas"
                class="<?= in_array($currentPage, ['gerencial/Despesas','gerencial/Despesa']) ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                Despesas e Contas
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmLembrete.php')): ?>
            <li>
                <a href="/gerencial/Lembretes"
                class="<?= in_array($currentPage, ['gerencial/Lembretes']) ? 'active' : '' ?>">
                <i class="fa-solid fa-bell"></i>
                Lembretes
                </a>
            </li>
            <?php endif; ?>

            <div class="menu-group">Sistema</div>

            <?php if ($isAdminGeral || podeAcessar('frmForcliExtrato.php')): ?>
            <li>
                <a href="/gerencial/Cliente/Extrato"
                class="<?= $currentPage == 'gerencial/Cliente/Extrato' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                Extrato Clientes
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmDocumentos.php')): ?>
            <li>
                <a href="/gerencial/Documentos"
                class="<?= $currentPage == 'gerencial/Documentos' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-signature"></i>
                Geração de Documentos
                </a>
            </li>
            <?php endif; ?>

            <?php if (($isAdminGeral || $isUsuarioAdmin) && ($isAdminGeral || podeAcessar('frmUser.php'))): ?>
            <li>
                <a href="/gerencial/Usuarios"
                class="<?= $currentPage == 'gerencial/Usuarios' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-gear"></i>
                Usuários
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmRel.php')): ?>
            <li>
                <a href="/gerencial/Relatorios"
                class="<?= $currentPage == 'gerencial/Relatorios' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-column"></i>
                Relatórios e Dashboards
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral): ?>
            <li>
                <a href="/gerencial/Gerencial"
                class="<?= $currentPage == 'gerencial/Gerencial' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i>
                Gerencial
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral): ?>
            <li>
                <a href="/gerencial/Planos"
                class="<?= $currentPage == 'gerencial/Planos' ? 'active' : '' ?>">
                <i class="fa-solid fa-credit-card"></i>
                Planos
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmMeuCadastro.php')): ?>
            <li>
                <a href="/gerencial/MeuCadastro"
                class="<?= $currentPage == 'gerencial/MeuCadastro' ? 'active' : '' ?>">
                <i class="fa-solid fa-building"></i>
                Meu Cadastro
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmConfig.php')): ?>
            <li>
                <a href="/gerencial/Configuracao"
                class="<?= $currentPage == 'gerencial/Configuracao' ? 'active' : '' ?>">
                <i class="fa-solid fa-gear"></i>
                Configurações
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral): ?>
            <li>
                <a href="/gerencial/Backup"
                class="<?= $currentPage == 'gerencial/Backup' ? 'active' : '' ?>">
                <i class="fa-solid fa-database"></i>
                Backup
                </a>
            </li>
            <?php endif; ?>
            
        </ul>

        <a href="/gerencial/Login" class="logout">Sair</a>

    </div>
</nav>

<script>

function toggleMenu(){
    document.querySelector('.navbar').classList.toggle('open');
    document.querySelector('.overlay').classList.toggle('show');
}

function toggleSidebarDesktop(){

    const navbar = document.querySelector('.navbar');
    const content = document.querySelector('.content');
    const button = document.querySelector('.collapse-btn');

    navbar.classList.toggle('collapsed');
    content.classList.toggle('expanded');

    button.innerHTML =
    navbar.classList.contains('collapsed')
    ? '❯'
    : '❮';

    localStorage.setItem(
        'sidebarCollapsed',
        navbar.classList.contains('collapsed')
    );
}

window.onload = () => {

    const collapsed =
    localStorage.getItem('sidebarCollapsed') === 'true';

    if(collapsed){

        document.querySelector('.navbar')
        .classList.add('collapsed');

        document.querySelector('.content')
        .classList.add('expanded');

        document.querySelector('.collapse-btn')
        .innerHTML = '❯';

        document.querySelector('.collapse-btn')
        .classList.add('collapsed');
    }
}

window.addEventListener('load', () => {

    const activeItem =
    document.querySelector('.navbar a.active');

    const scrollContainer =
    document.querySelector('.navbar-scroll');

    if(!activeItem || !scrollContainer) return;

    const href = activeItem.getAttribute('href');

    if(href.includes('Home')) return;

    setTimeout(() => {

        const target =
        activeItem.offsetTop
        - scrollContainer.offsetTop
        - 120;

        smoothScroll(scrollContainer, target, 600);

    }, 250);

});

function smoothScroll(container, target, duration){

    const start = container.scrollTop;
    const change = target - start;
    const startTime = performance.now();

    function animateScroll(currentTime){

        const elapsed = currentTime - startTime;

        const progress =
        Math.min(elapsed / duration, 1);

        const ease =
        1 - Math.pow(1 - progress, 3);

        container.scrollTop =
        start + change * ease;

        if(progress < 1){
            requestAnimationFrame(animateScroll);
        }
    }

    requestAnimationFrame(animateScroll);
}

</script>