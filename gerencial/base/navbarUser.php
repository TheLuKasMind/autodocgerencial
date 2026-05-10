<script src="../base/base.js"></script>

<?php
require_once '../base/verificaPlano.php';
require_once '../base/permissoes.php';

require_once '../base/verificaAcessoTela.php';

$homeLink = '/gerencial/user/frmHome.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

global $currentPage;
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>

body{
    margin:0;
    font-family:'Inter', sans-serif;
}

/* SIDEBAR */

.navbar{
    position:fixed;
    top:0;
    left:0;
    width:220px;
    height:100vh;

    background:linear-gradient(180deg,#ffffff,#fff7ed);

    border-right:1px solid #fed7aa;

    display:flex;
    flex-direction:column;

    z-index:2000;

    transition:0.3s ease;

    overflow:visible;

    box-shadow:4px 0 18px rgba(0,0,0,0.04);
}

/* COLAPSADA */

.navbar.collapsed{
    width:0;
    border-right:none;
}


/* LINKS */

.navbar ul{
    list-style:none;
    padding:10px 0;
    margin:0;
    flex:1;
}

.menu-group{
    padding:14px 22px 6px;
    font-size:11px;
    font-weight:700;
    color:#94a3b8;
    text-transform:uppercase;
}

.navbar ul li a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:13px 24px;
    margin:4px 10px;
    border-radius:10px;
    text-decoration:none;
    color:#334155;
    font-size:14px;
    font-weight:600;
    transition:0.25s;
}

.navbar ul li a:hover{
    background:#fff7ed;
    color:#ea580c;
}

.navbar ul li a.active{
    background:linear-gradient(135deg,#f97316,#ea580c);
    color:white;
}

.logout{
    display:block;

    margin:14px 10px;
    padding:13px 24px;

    border-radius:10px;

    background:linear-gradient(135deg,#f97316,#ea580c);

    color:white;

    text-decoration:none;

    font-size:14px;
    font-weight:700;

    text-align:center;

    transition:all .25s ease;

    box-shadow:0 6px 14px rgba(249,115,22,0.35);
}

.logout:hover{
    background:linear-gradient(135deg,#fb923c,#f97316);

    transform:translateX(3px);

    box-shadow:0 10px 20px rgba(249,115,22,0.45);
}

/* CONTEÚDO */

.content{
    margin-left:240px;
    padding:25px;
    transition:0.3s ease;
}

.content.expanded{
    margin-left:0;
}


.navbar-scroll::-webkit-scrollbar{
    width:6px;
}

.navbar-scroll::-webkit-scrollbar-thumb{
    background:#fdba74;
    border-radius:10px;
}

.navbar-scroll{
    scrollbar-width:thin;
    scrollbar-color:#fdba74 transparent;
}

/* BOTÃO DESKTOP */

.collapse-btn{
    position:absolute;

    top:18px;
    right:-10px;

    width:22px;
    height:22px;

    border:none;
    border-radius:50%;

    background:#ffffff;

    color:#94a3b8;

    cursor:pointer;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:11px;
    font-weight:700;

    border:1px solid #e2e8f0;

    box-shadow:0 2px 6px rgba(0,0,0,.08);

    transition:.2s ease;

    z-index:5000;
}

.collapse-btn:hover{
    background:#f8fafc;

    color:#ea580c;

    transform:scale(1.05);
}

/* MOBILE */

.menu-toggle{
    display:none;
}

.overlay{
    display:none;
}

@media(max-width:768px){

    .content{
        margin-left:0 !important;
        padding-top:80px;
    }

    .collapse-btn{
        display:none;
    }

    .menu-toggle{
        position:fixed;
        top:18px;
        left:18px;
        width:42px;
        height:42px;
        background:white;
        border:1px solid #fed7aa;
        border-radius:12px;
        cursor:pointer;
        z-index:3000;
        display:flex;
        flex-direction:column;
        justify-content:center;
        align-items:center;
        gap:5px;
    }

    .menu-toggle span{
        width:20px;
        height:2px;
        background:#ea580c;
    }

    .navbar{
        left:-260px;
    }

    .navbar.open{
        left:0;
    }

    .overlay{
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0.35);
        display:block;
        opacity:0;
        visibility:hidden;
        transition:0.3s;
        z-index:1500;
    }

    .overlay.show{
        opacity:1;
        visibility:visible;
    }
}

.navbar-scroll{
    height:100%;
    overflow-y:auto;
    overflow-x:hidden;
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

        <a href="<?= $homeLink ?>" class="logo" style="padding:20px;text-align:center;display:block;">
            <img src="../img/logoo.png"
            style="width:180px;height:180px;object-fit:contain;border-radius:20px;background:white;padding:12px;">
        </a>

        <ul>

            <?php
                $isAdminGeral = isset($_SESSION['AdminGeral']) && $_SESSION['AdminGeral'] == 1;
                $isUsuarioAdmin = isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] == 2;
            ?>

            <div class="menu-group">Operacional</div>

            <?php if ($isAdminGeral || podeAcessar('frmHome.php')): ?>
            <li>
                <a href="../user/frmHome.php"
                class="<?= $currentPage == 'frmHome.php' ? 'active' : '' ?>">
                Inicial
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmLancamentoLista.php')): ?>
            <li>
                <a href="../user/frmLancamentoLista.php"
                class="<?= in_array($currentPage, ['frmLancamentoLista.php','frmLancamento.php']) ? 'active' : '' ?>">
                Pedidos e Vendas
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmMultaLista.php')): ?>
            <li>
                <a href="../user/frmMultaLista.php"
                class="<?= in_array($currentPage, ['frmMultaLista.php','frmMulta.php']) ? 'active' : '' ?>">
                Multas
                </a>
            </li>
            <?php endif; ?>

            <div class="menu-group">Financeiro</div>

            <?php if ($isAdminGeral || podeAcessar('frmBoletimCaixa.php')): ?>
            <li>
                <a href="../user/frmBoletimCaixa.php"
                class="<?= in_array($currentPage, ['frmBoletimCaixa.php','frmBoletimCaixaLcto.php']) ? 'active' : '' ?>">
                Boletim de Caixa
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmFinanceiro.php')): ?>
            <li>
                <a href="../user/frmFinanceiro.php"
                class="<?= $currentPage == 'frmFinanceiro.php' ? 'active' : '' ?>">
                Financeiro Geral
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmPatrimonio.php')): ?>
            <li>
                <a href="../user/frmPatrimonio.php"
                class="<?= $currentPage == 'frmPatrimonio.php' ? 'active' : '' ?>">
                Compras e Patrimônio
                </a>
            </li>
            <?php endif; ?>

            <div class="menu-group">Cadastros</div>

            <?php if ($isAdminGeral || podeAcessar('frmServProdLista.php')): ?>
            <li>
                <a href="../user/frmServProdLista.php"
                class="<?= in_array($currentPage, ['frmServProdLista.php','frmServProd.php']) ? 'active' : '' ?>">
                Serviços / Produtos
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmForcliLista.php')): ?>
            <li>
                <a href="../user/frmForcliLista.php"
                class="<?= in_array($currentPage, ['frmForcliLista.php','frmForcli.php']) ? 'active' : '' ?>">
                Clientes / Fornecedores
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmGrupo.php')): ?>
            <li>
                <a href="../user/frmGrupo.php"
                class="<?= $currentPage == 'frmGrupo.php' ? 'active' : '' ?>">
                Grupos
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmDespesasLista.php')): ?>
            <li>
                <a href="../user/frmDespesasLista.php"
                class="<?= in_array($currentPage, ['frmDespesasLista.php','frmDespesas.php']) ? 'active' : '' ?>">
                Despesas e Contas
                </a>
            </li>
            <?php endif; ?>

            <div class="menu-group">Sistema</div>

            <?php if ($isAdminGeral || podeAcessar('frmForcliExtrato.php')): ?>
            <li>
                <a href="../user/frmForcliExtrato.php"
                class="<?= $currentPage == 'frmForcliExtrato.php' ? 'active' : '' ?>">
                Extrato Clientes
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmDocumentos.php')): ?>
            <li>
                <a href="../user/frmDocumentos.php"
                class="<?= $currentPage == 'frmDocumentos.php' ? 'active' : '' ?>">
                Geração de Documentos
                </a>
            </li>
            <?php endif; ?>

            <?php if (($isAdminGeral || $isUsuarioAdmin) && ($isAdminGeral || podeAcessar('frmUser.php'))): ?>
            <li>
                <a href="../user/frmUser.php"
                class="<?= $currentPage == 'frmUser.php' ? 'active' : '' ?>">
                Usuários
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmRel.php')): ?>
            <li>
                <a href="../user/frmRel.php"
                class="<?= $currentPage == 'frmRel.php' ? 'active' : '' ?>">
                Relatórios e Dashboards
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral): ?>
            <li>
                <a href="../admin/frmGerencial.php"
                class="<?= $currentPage == 'frmGerencial.php' ? 'active' : '' ?>">
                Gerencial
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral): ?>
            <li>
                <a href="../admin/frmPlanos.php"
                class="<?= $currentPage == 'frmPlanos.php' ? 'active' : '' ?>">
                Planos
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmMeuCadastro.php')): ?>
            <li>
                <a href="../user/frmMeuCadastro.php"
                class="<?= $currentPage == 'frmMeuCadastro.php' ? 'active' : '' ?>">
                Meu Cadastro
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral || podeAcessar('frmConfig.php')): ?>
            <li>
                <a href="../user/frmConfig.php"
                class="<?= $currentPage == 'frmConfig.php' ? 'active' : '' ?>">
                Configurações
                </a>
            </li>
            <?php endif; ?>

            <?php if ($isAdminGeral): ?>
            <li>
                <a href="../admin/frmBackup.php"
                class="<?= $currentPage == 'frmBackup.php' ? 'active' : '' ?>">
                Backup
                </a>
            </li>
            <?php endif; ?>
            
        </ul>

        <a href="../frmLogin.php" class="logout">Sair</a>

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

    if(href.includes('frmHome.php')) return;

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