<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Autodoc Gerencial</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="icon" href="/gerencial/img/logoo.png">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #fff;
            color: #1f2937;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* ===== GERAL ===== */

        .container {
            max-width: 1250px;
            margin: auto;
            padding: 0 24px;
        }

        section {
            position: relative;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff7ed;
            color: #ea580c;
            border: 1px solid #fed7aa;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 22px;
        }

        /* ===== HEADER ===== */

        header {
            position: sticky;
            top: 0;
            z-index: 999;
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, .05);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .logo img {
            width: 52px;
            transition: .3s;
        }

        .logo:hover img {
            transform: rotate(-6deg) scale(1.04);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-title {
            font-size: 21px;
            font-weight: 800;
            color: #f97316;
            line-height: 1;
        }

        .logo-sub {
            font-size: 12px;
            color: #6b7280;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .nav a {
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            transition: .2s;
        }

        .nav a:hover {
            color: #f97316;
        }

        .btn-login {
            background: linear-gradient(135deg, #fb923c, #f97316);
            color: #fff !important;
            padding: 13px 24px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 15px 30px rgba(249, 115, 22, .25);
            transition: .25s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 35px rgba(249, 115, 22, .35);
        }

        /* ===== HERO ===== */

        .hero {
            padding: 90px 0 70px;
            background:
                radial-gradient(circle at top left, rgba(251, 146, 60, .15), transparent 35%),
                radial-gradient(circle at bottom right, rgba(249, 115, 22, .10), transparent 35%),
                linear-gradient(180deg, #fff, #fffaf5);
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 70px;
            align-items: center;
        }

        .hero-text h1 {
            font-size: 58px;
            line-height: 1.08;
            margin-bottom: 24px;
            color: #111827;
            font-weight: 800;
        }

        .hero-text h1 span {
            color: #f97316;
        }

        .hero-text p {
            font-size: 18px;
            color: #4b5563;
            margin-bottom: 30px;
            max-width: 620px;
        }

        .hero-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-bottom: 35px;
        }

        .hero-list div {
            background: #fff;
            border: 1px solid #f3f4f6;
            border-radius: 16px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .04);
        }

        .hero-list span {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #fff7ed;
            color: #f97316;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-main {
            background: linear-gradient(135deg, #fb923c, #f97316);
            color: #fff;
            padding: 15px 30px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 18px 35px rgba(249, 115, 22, .28);
            transition: .25s;
        }

        .btn-main:hover {
            transform: translateY(-3px);
        }

        .btn-secondary {
            border: 2px solid #fdba74;
            background: #fff;
            color: #ea580c;
            padding: 14px 28px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            transition: .2s;
        }

        .btn-secondary:hover {
            background: #fff7ed;
        }

        /* ===== HERO VISUAL ===== */

        .hero-visual {
            position: relative;
        }

        .dashboard-card {
            background: #fff;
            border-radius: 28px;
            padding: 28px;
            box-shadow:
                0 25px 60px rgba(0, 0, 0, .08),
                0 8px 20px rgba(249, 115, 22, .08);
            border: 1px solid rgba(249, 115, 22, .08);
        }

        .dashboard-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .dashboard-top h3 {
            font-size: 20px;
        }

        .status-online {
            background: #dcfce7;
            color: #166534;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat {
            background: #fff7ed;
            border-radius: 18px;
            padding: 20px;
        }

        .stat strong {
            display: block;
            font-size: 28px;
            color: #ea580c;
        }

        .stat span {
            color: #7c2d12;
            font-size: 14px;
        }

        .mini-table {
            border: 1px solid #f3f4f6;
            border-radius: 18px;
            overflow: hidden;
        }

        .mini-row {
            display: grid;
            grid-template-columns: 1fr auto;
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }

        .mini-row:last-child {
            border-bottom: none;
        }

        .mini-row strong {
            color: #16a34a;
        }

        /* ===== SECTION TITLE ===== */

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 40px;
            margin-bottom: 12px;
            color: #111827;
        }

        .section-title p {
            color: #6b7280;
            max-width: 700px;
            margin: auto;
        }

        /* ===== BENEFÍCIOS ===== */

        .benefits {
            padding: 90px 0;
        }

        .benefit-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .benefit {
            background: #fff;
            border-radius: 24px;
            padding: 35px;
            border: 1px solid #f3f4f6;
            transition: .3s;
            position: relative;
            overflow: hidden;
        }

        .benefit::before {
            content: "";
            position: absolute;
            width: 140px;
            height: 140px;
            background: rgba(249, 115, 22, .06);
            border-radius: 50%;
            top: -60px;
            right: -60px;
        }

        .benefit:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, .06);
        }

        .benefit-icon {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: #fff7ed;
            color: #f97316;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 22px;
        }

        .benefit h3 {
            font-size: 22px;
            margin-bottom: 12px;
        }

        .benefit p {
            color: #6b7280;
        }

        /* ===== MÉTRICAS ===== */

        .metrics {
            padding: 90px 0;
            background: linear-gradient(135deg, #fff7ed, #ffffff);
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
        }

        .metric {
            background: #fff;
            border-radius: 22px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .05);
        }

        .metric h3 {
            font-size: 42px;
            color: #f97316;
            margin-bottom: 6px;
        }

        .metric p {
            color: #6b7280;
        }

        /* ===== DESTAQUE ===== */

        .highlight {
            padding: 100px 0;
            background:
                linear-gradient(135deg, rgba(249, 115, 22, .95), rgba(234, 88, 12, .95)),
                url('img/logoo.png');
            color: #fff;
            text-align: center;
            overflow: hidden;
        }

        .highlight::before {
            content: "";
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
            top: -180px;
            right: -180px;
        }

        .highlight h2 {
            font-size: 44px;
            margin-bottom: 22px;
            position: relative;
            z-index: 2;
        }

        .highlight p {
            max-width: 760px;
            margin: auto;
            margin-bottom: 35px;
            opacity: .95;
            position: relative;
            z-index: 2;
        }

        /* ===== CTA ===== */

        .cta {
            padding: 90px 0;
            text-align: center;
        }

        .cta-card {
            background: #fff;
            border: 1px solid #f3f4f6;
            border-radius: 32px;
            padding: 60px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .05);
        }

        .cta h2 {
            font-size: 42px;
            margin-bottom: 18px;
        }

        .cta p {
            color: #6b7280;
            max-width: 700px;
            margin: auto;
            margin-bottom: 30px;
        }

        /* ===== FOOTER ===== */

        footer {
            background: #fafafa;
            border-top: 1px solid #eee;
            padding: 28px 0;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }

        /* ===== RESPONSIVO ===== */

        @media(max-width:1100px) {

            .hero-content {
                grid-template-columns: 1fr;
                gap: 50px;
            }

            .hero-text {
                text-align: center;
            }

            .hero-text p {
                margin: auto auto 30px;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-list {
                grid-template-columns: 1fr;
            }

            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media(max-width:768px) {

            .header-content {
                flex-direction: column;
                gap: 16px;
            }

            .nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 14px;
            }

            .hero {
                padding-top: 60px;
            }

            .hero-text h1 {
                font-size: 38px;
            }

            .section-title h2,
            .highlight h2,
            .cta h2 {
                font-size: 30px;
            }

            .benefit-grid,
            .metrics-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-card {
                padding: 22px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .cta-card {
                padding: 35px 24px;
            }

        }
    </style>
</head>

<body>

    <!-- HEADER -->

    <header>

        <div class="container header-content">

            <a href="#" class="logo">

                <img src="/gerencial/img/logoo.png" alt="Autodoc">

                <div class="logo-text">
                    <span class="logo-title">Autodoc Gerencial</span>
                    <span class="logo-sub">Sistema para despachantes</span>
                </div>

            </a>

            <nav class="nav">
                <a href="#beneficios">Benefícios</a>
                <a href="#metricas">Resultados</a>
                <a href="#cta">Começar</a>
                <a class="btn-login" href="<?= dirname($_SERVER['PHP_SELF']) ?>/Login">
                    Acessar sistema
                </a>
            </nav>

        </div>

    </header>

    <!-- HERO -->

    <section class="hero">

        <div class="container hero-content">

            <div class="hero-text">

                <div class="badge">
                    🚀 Plataforma moderna para despachantes
                </div>

                <h1>
                    Controle todo o seu <span>escritório</span> em um único sistema
                </h1>

                <p>
                    O <strong>Autodoc Gerencial</strong> foi criado para simplificar o dia a dia
                    de despachantes e empresas automotivas com gestão financeira,
                    clientes, produtos, serviços e produtividade em um só lugar.
                </p>

                <div class="hero-list">

                    <div>
                        <span>✓</span>
                        <p>Controle financeiro completo</p>
                    </div>

                    <div>
                        <span>✓</span>
                        <p>Gestão de clientes e produtos</p>
                    </div>

                    <div>
                        <span>✓</span>
                        <p>Fluxo de caixa em tempo real</p>
                    </div>

                    <div>
                        <span>✓</span>
                        <p>Serviços organizados e seguros</p>
                    </div>

                </div>

                <div class="hero-buttons">
                    <a class="btn-main" href="Login">Entrar no sistema</a>
                    <a class="btn-secondary" href="#beneficios">Ver funcionalidades</a>
                </div>

            </div>

            <div class="hero-visual">

                <div class="dashboard-card">

                    <div class="dashboard-top">
                        <h3>Painel Gerencial</h3>
                        <div class="status-online">● Sistema online</div>
                    </div>

                    <div class="stats">

                        <div class="stat">
                            <strong>📋</strong>
                            <span>Gestão de processos</span>
                        </div>

                        <div class="stat">
                            <strong>💰</strong>
                            <span>Controle financeiro</span>
                        </div>

                        <div class="stat">
                            <strong>📦</strong>
                            <span>Cadastro de produtos</span>
                        </div>

                        <div class="stat">
                            <strong>📄</strong>
                            <span>Relatórios e consultas</span>
                        </div>

                    </div>

                    <div class="mini-table">

                        <div class="mini-row">
                            <span>Transferência concluída</span>
                            <strong>Finalizado</strong>
                        </div>

                        <div class="mini-row">
                            <span>Defesa de multa</span>
                            <strong>Em andamento</strong>
                        </div>

                        <div class="mini-row">
                            <span>Licenciamento anual</span>
                            <strong>Pago</strong>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>



    <!-- BENEFÍCIOS -->

    <section class="benefits" id="beneficios">
        <div class="container">

            <div class="section-title">
                <span class="section-badge">Funcionalidades</span>
                <h2>Ferramentas para facilitar sua rotina</h2>

                <p>
                    O Autodoc Gerencial foi pensado para organizar processos,
                    clientes e atendimentos em um único ambiente moderno.
                </p>
            </div>

            <div class="benefit-grid">

                <div class="benefit">
                    <div class="benefit-icon">📋</div>

                    <h3>Gestão de atendimentos</h3>

                    <p>
                        Cadastre processos, acompanhe serviços e mantenha todas
                        as informações organizadas de forma prática.
                    </p>
                </div>

                <div class="benefit">
                    <div class="benefit-icon">📦</div>

                    <h3>Controle de produtos</h3>

                    <p>
                        Gerencie seu catálogo de produtos, acompanhe informações,
                        preços e mantenha tudo organizado em um único lugar.
                    </p>
                </div>

                <div class="benefit">
                    <div class="benefit-icon">💰</div>

                    <h3>Financeiro simplificado</h3>

                    <p>
                        Acompanhe pagamentos, lançamentos e movimentações
                        financeiras em um único painel.
                    </p>
                </div>

                <div class="benefit">
                    <div class="benefit-icon">📄</div>

                    <h3>Relatórios organizados</h3>

                    <p>
                        Gere relatórios para consultas e acompanhe os registros
                        do sistema de forma rápida.
                    </p>
                </div>

                <div class="benefit">
                    <div class="benefit-icon">🔒</div>

                    <h3>Mais organização</h3>

                    <p>
                        Tenha seus dados centralizados com acesso simples
                        e visual moderno para o dia a dia.
                    </p>
                </div>

                <div class="benefit">
                    <div class="benefit-icon">⚡</div>

                    <h3>Fluxo mais ágil</h3>

                    <p>
                        Reduza tarefas repetitivas e tenha mais praticidade
                        na gestão do escritório.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- DESTAQUE -->

    <section class="highlight">

        <div class="container">

            <div class="highlight-box">

                <span class="highlight-badge">
                    Sistema para despachantes
                </span>

                <h2>
                    Organização, praticidade e controle em um só lugar
                </h2>

                <p>
                    O Autodoc Gerencial reúne ferramentas para auxiliar
                    na gestão do seu escritório com um visual moderno,
                    leve e fácil de utilizar.
                </p>

                <div class="highlight-features">

                    <div class="highlight-item">
                        <span>✔</span>
                        <p>Cadastro de clientes e produtos</p>
                    </div>

                    <div class="highlight-item">
                        <span>✔</span>
                        <p>Controle financeiro</p>
                    </div>

                    <div class="highlight-item">
                        <span>✔</span>
                        <p>Relatórios e consultas</p>
                    </div>

                    <div class="highlight-item">
                        <span>✔</span>
                        <p>Interface simples e moderna</p>
                    </div>

                </div>

                <a class="btn-main" href="/gerencial/Login">
                    Acessar sistema
                </a>

            </div>

        </div>
    </section>

    <!-- CTA FINAL -->

    <section class="cta">

        <div class="container">

            <div class="cta-box">

                <span class="section-badge">Autodoc Gerencial</span>

                <h2>
                    Leve mais organização para o seu escritório
                </h2>

                <p>
                    Centralize informações, acompanhe processos
                    e utilize uma plataforma desenvolvida para facilitar
                    sua rotina de trabalho.
                </p>

                <div class="cta-buttons">
                    <a class="btn-main" href="Login">
                        Entrar no sistema
                    </a>

                    <a class="btn-secondary" href="#">
                        Conhecer funcionalidades
                    </a>
                </div>

            </div>

        </div>

    </section>

    <!-- MÉTRICAS -->

    <section class="metrics" id="metricas">

        <div class="container">

            <div class="section-title">

                <h2>Feito para acelerar seu atendimento</h2>

                <p>
                    Mais produtividade para sua equipe e mais controle para seu negócio.
                </p>

            </div>

            <div class="metrics-grid">

                <div class="metric">
                    <h3>24h</h3>
                    <p>Acesso ao sistema</p>
                </div>

                <div class="metric">
                    <h3>100%</h3>
                    <p>Centralizado na nuvem</p>
                </div>

                <div class="metric">
                    <h3>Multiusuário</h3>
                    <p>Acesso para equipe e colaboradores</p>
                </div>

                <div class="metric">
                    <h3>PDF</h3>
                    <p>Relatórios e documentos para impressão</p>
                </div>

            </div>

        </div>

    </section>

    <!-- HIGHLIGHT -->

    <section class="highlight">

        <div class="container">

            <h2>
                O sistema completo para despachantes modernos
            </h2>

            <p>
                Tenha visão total do financeiro, organize processos,
                acompanhe clientes e aumente sua produtividade com
                uma plataforma rápida, intuitiva e segura.
            </p>

            <a class="btn-main" href="Login">
                Começar agora
            </a>

        </div>

    </section>

    <!-- CTA -->

    <section class="cta" id="cta">

        <div class="container">

            <div class="cta-card">

                <h2>
                    Pronto para modernizar seu escritório?
                </h2>

                <p>
                    Pare de perder tempo com planilhas e processos desorganizados.
                    Centralize tudo em uma única plataforma moderna e eficiente.
                </p>

                <a class="btn-main" href="Login">
                    Entrar no Autodoc Gerencial
                </a>

            </div>

        </div>

    </section>

    <?php include 'base/footer-new.php'; ?>

</body>

</html>