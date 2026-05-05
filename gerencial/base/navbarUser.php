<script src="../base/base.js"></script>
<?php
require_once '../base/verificaPlano.php';

$homeLink = '/gerencial/user/frmHome.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}
?>

<style>
    /* .navbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 240px;
        height: 100vh;
        background: linear-gradient(180deg, #ffffff, #fff7ed);
        border-right: 1px solid #fed7aa;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        box-shadow: 4px 0 18px rgba(0,0,0,0.04);
        font-family: 'Inter', sans-serif;
    } */
    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        width: 240px;
        height: 100vh;
        background: linear-gradient(180deg, #ffffff, #fff7ed);
        border-right: 1px solid #fed7aa;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        box-shadow: 4px 0 18px rgba(0,0,0,0.04);
        font-family: 'Inter', sans-serif;

        overflow-y: auto;     
        scrollbar-width: thin;         
        scrollbar-color: #fdba74 transparent;

    }


    /* LOGO */
    .navbar .logo {
        padding: 22px;
        text-align: center;
        font-size: 22px;
        font-weight: 800;
        color: #ea580c;
        border-bottom: 1px solid #fed7aa;
        letter-spacing: .5px;
    }

    /* MENU */
    /* .navbar ul {
        list-style: none;
        padding: 12px 0;
        flex: 1;
        overflow-y: auto;
    } */

    .navbar ul {
        list-style: none;
        padding: 12px 0;
        flex: 1;
    }
    /* GRUPO */
    .menu-group {
        padding: 14px 22px 6px;
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .6px;
    }

    /* LINKS */
    .navbar ul li a {
        display: block;
        padding: 13px 24px;
        margin: 4px 10px;
        border-radius: 10px;
        text-decoration: none;
        color: #334155;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.25s ease;
        position: relative;
    }

    /* HOVER */
    .navbar ul li a:hover {
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        color: #ea580c;
        transform: translateX(3px);
    }

    /* ATIVO */
    .navbar ul li a.active {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(249,115,22,0.35);
    }

    .navbar ul li a.active:hover {
        transform: none;
    }

    /* BOTÃO SAIR */
    .navbar .logout {
        margin: 14px;
        padding: 14px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
        text-align: center;
        font-weight: 700;
        text-decoration: none;
        transition: 0.25s;
        box-shadow: 0 6px 14px rgba(249,115,22,0.35);
    }

    .navbar .logout:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(249,115,22,0.45);
    }

    /* SCROLL BONITO */
    .navbar ul::-webkit-scrollbar {
        width: 6px;
    }

    .navbar ul::-webkit-scrollbar-thumb {
        background: #fdba74;
        border-radius: 10px;
    }

    /* ================= MOBILE ================= */

    @media (max-width: 768px) {

        .navbar {
            position: relative;
            width: 100%;
            height: auto;
            border-right: none;
            border-bottom: 1px solid #fed7aa;
            box-shadow: none;
        }

        .navbar ul li a {
            margin: 2px 8px;
        }

        .navbar .logout {
            margin: 10px;
        }

        .navbar .logo {
            padding: 22px;
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            color: #ea580c;
            border-bottom: 1px solid #fed7aa;
            letter-spacing: .5px;
            text-decoration: none;   
            display: block;        
        }
        .navbar .logo:hover {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
        }
        .navbar .logo,
        .navbar .logo:visited,
        .navbar .logo:hover,
        .navbar .logo:active {
            text-decoration: none;
        }

    }





    .main-content {
        margin-left: 240px;
        padding: 25px;
        transition: 0.3s ease;
    }

    @media (max-width: 768px) {

        .navbar {
            position: fixed;
            top: 0;
            left: -260px;
            width: 240px;
            height: 100vh;
            transition: 0.35s cubic-bezier(.77,0,.18,1);
            z-index: 2000;
            box-shadow: 6px 0 25px rgba(0,0,0,0.08);
        }

        .navbar.open {
            left: 0;
        }

        .main-content {
            margin-left: 0;
            padding: 80px 18px 20px 18px;
            transition: 0.3s ease;
        }

        /* BOTÃO MODERNO */
        .menu-toggle {
            position: fixed;
            top: 18px;
            left: 18px;
            width: 42px;
            height: 42px;
            background: white;
            border: 1px solid #fed7aa;
            border-radius: 12px;
            cursor: pointer;
            z-index: 3000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            transition: 0.3s ease;
        }

        .menu-toggle span {
            width: 20px;
            height: 2px;
            background: #ea580c;
            transition: 0.3s ease;
            border-radius: 2px;
        }

        /* animação virar X */
        .menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(4px,4px);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px,-5px);
        }

        /* overlay elegante */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.35);
            backdrop-filter: blur(3px);
            opacity: 0;
            visibility: hidden;
            transition: 0.3s ease;
            z-index: 1500;
        }

        .overlay.show {
            opacity: 1;
            visibility: visible;
        }
    }
</style>

<?php
  global $currentPage;
    $currentPage = basename($_SERVER['PHP_SELF']);
?>


<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<button class="menu-toggle" onclick="toggleMenu()">
    <span></span>
    <span></span>
    <span></span>
</button>
<div class="overlay" onclick="toggleMenu()"></div>

<div class="main-content">
<nav class="navbar">
    <!--<a href="<?= $homeLink ?>" class="logo">Autodoc</a>-->

    <!-- ==================== LOGO ==================== -->
    <a href="<?= $homeLink ?>" class="logo" style="padding: 20px 15px 15px; text-align: center; display: block;">
        <img src="../img/logoo.png" 
             alt="Autodoc Gerencial" 
             style="width: 180px; 
                    height: 180px; 
                    object-fit: contain; 
                    border-radius: 20px; 
                    background: white; 
                    padding: 12px; 
                    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
                    transition: transform 0.3s ease;">
</a>

    <ul>

        <div class="menu-group">Operacional</div>

        <li>
            <a href="../user/frmHome.php"
            class="<?= $currentPage == 'frmHome.php' ? 'active' : '' ?>">
            Inicial
            </a>
        </li>

        <li>
            <a href="../user/frmLancamentoLista.php"
            class="<?= in_array($currentPage, ['frmLancamentoLista.php','frmLancamento.php']) ? 'active' : '' ?>">
            Pedidos e Vendas
            </a>
        </li>

        <li>
            <a href="../user/frmMultaLista.php"
            class="<?= in_array($currentPage, ['frmMultaLista.php','frmMulta.php']) ? 'active' : '' ?>">
            Multas
            </a>
        </li>

        <div class="menu-group">Financeiro</div>

        <li> 
            <a href="../user/frmBoletimCaixa.php"
            class="<?= in_array($currentPage, ['frmBoletimCaixa.php','frmBoletimCaixaLcto.php']) ? 'active' : '' ?>">
            Boletim de Caixa
            </a>
        </li>

        <li>
            <a href="../user/frmFinanceiro.php"
            class="<?= $currentPage == 'frmFinanceiro.php' ? 'active' : '' ?>">
            Financeiro Geral
            </a>
        </li>

        <li>
            <a href="../user/frmPatrimonio.php"
            class="<?= in_array($currentPage, ['frmPatrimonio.php']) ? 'active' : '' ?>">
            Compras e Patrimônio
            </a>
        </li>

        <div class="menu-group">Cadastros</div>

        <li>
            <a href="../user/frmServProdLista.php"
            class="<?= in_array($currentPage, ['frmServProdLista.php','frmServProd.php']) ? 'active' : '' ?>">
            Serviços / Produtos
            </a>
        </li>

        <li>
            <a href="../user/frmForcliLista.php"
            class="<?= in_array($currentPage, ['frmForcliLista.php','frmForcli.php']) ? 'active' : '' ?>">
            Clientes / Fornecedores
            </a>
        </li>

        <li>
            <a href="../user/frmGrupo.php"
            class="<?= in_array($currentPage, ['frmGrupo.php']) ? 'active' : '' ?>">
            Grupos
            </a>
        </li>

        <li>
            <a href="../user/frmDespesasLista.php"
            class="<?= in_array($currentPage, ['frmDespesasLista.php','frmDespesas.php']) ? 'active' : '' ?>">
            Despesas e Contas
            </a>
        </li>


        <div class="menu-group">Sistema</div>

        <li>
            <a href="../user/frmForcliExtrato.php"
            class="<?= in_array($currentPage, ['frmForcliExtrato.php']) ? 'active' : '' ?>">
            Extrato Clientes
            </a>
        </li>
        
        <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] == 2): ?>
            <li>
                <a href="../user/frmUser.php"
                class="<?= $currentPage == 'frmUser.php' ? 'active' : '' ?>">
                Usuários
                </a>
            </li>
        <?php endif; ?>

        <li>
            <a href="../user/frmRel.php"
            class="<?= $currentPage == 'frmRel.php' ? 'active' : '' ?>">
            Relatórios e Dashboards
            </a>
        </li>

        <?php if (isset($_SESSION['AdminGeral']) && $_SESSION['AdminGeral'] == 1): ?>
            <li>
                <a href="../admin/frmGerencial.php"
                class="<?= $currentPage == 'frmGerencial.php' ? 'active' : '' ?>">
                Gerencial
                </a>
            </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['AdminGeral']) && $_SESSION['AdminGeral'] == 1): ?>
            <li>
                <a href="../admin/frmPlanos.php"
                class="<?= $currentPage == 'frmPlanos.php' ? 'active' : '' ?>">
                Planos
                </a>
            </li>
        <?php endif; ?>

        <li>
            <a href="../user/frmMeuCadastro.php"
            class="<?= in_array($currentPage, ['frmMeuCadastro.php']) ? 'active' : '' ?>">
            Meu Cadastro
            </a>
        </li>

        <li>
            <a href="../user/frmConfig.php"
            class="<?= $currentPage == 'frmConfig.php' ? 'active' : '' ?>">
            Configurações
            </a>
        </li>

    </ul>


    <a href="../frmLogin.php" class="logout">Sair</a>
</nav>
</div>
<script>
function toggleMenu() {
    const navbar = document.querySelector('.navbar');
    const overlay = document.querySelector('.overlay');
    const button = document.querySelector('.menu-toggle');

    navbar.classList.toggle('open');
    overlay.classList.toggle('show');
    button.classList.toggle('active');
}
</script>
<!--
<nav class="navbar">
    <div class="logo">Autodoc</div>

    <ul>
        <li><a href="../user/frmHome.php">Inicial</a></li>
        <li><a href="../user/frmServProdLista.php">Cadastro de Serviços / Produtos</a></li>
        <li><a href="../user/frmForcliLista.php">Cadastro de Clientes</a></li>
        <li><a href="../user/frmDespesasLista.php">Despesas e Contas</a></li>
        <li><a href="../user/frmBoletimCaixa.php">Boletim de Caixa</a></li>
        <li><a href="../user/frmBoletimCaixa.php">Relatórios e Dashboards</a></li>
        <li><a href="#">Configurações</a></li>
    </ul>

    <a href="../frmLogin.php" class="logout">Sair</a>
</nav>
-->