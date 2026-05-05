<!DOCTYPE html>
<html lang="pt-BR">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Autodoc Gerencial</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="img/logoo.png">

<style>

/* ===== RESET ===== */

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Inter',sans-serif;
    background:#fff;
    color:#333;
    line-height:1.6;
}

/* ===== CONTAINER ===== */

.container{
    max-width:1200px;
    margin:auto;
    padding:0 20px;
}

/* ===== HEADER ===== */

header{
    background:#fff;
    position:sticky;
    top:0;
    z-index:100;
    box-shadow:0 5px 20px rgba(0,0,0,0.05);
}

.header-content{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:18px 0;
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
}

.logo img{
    width:50px;
}

.logo span{
    font-weight:600;
    font-size:20px;
    color:#f57c00;
}

.btn-login{
    background:linear-gradient(135deg,#fb8c00,#f57c00);
    color:#fff;
    padding:10px 22px;
    border-radius:30px;
    text-decoration:none;
    font-weight:600;
    box-shadow:0 10px 20px rgba(245,124,0,0.3);
}

/* ===== HERO ===== */

.hero{
    padding:70px 0;
    background:linear-gradient(135deg,#fff,#fff3e0);
}

.hero-content{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:50px;
    align-items:center;
}

.hero-text h1{
    font-size:42px;
    margin-bottom:20px;
}

.hero-text p{
    font-size:18px;
    margin-bottom:25px;
}

.hero-text ul{
    list-style:none;
    margin-bottom:25px;
}

.hero-text li{
    margin-bottom:10px;
    padding-left:25px;
    position:relative;
}

.hero-text li::before{
    content:"✓";
    color:#f57c00;
    position:absolute;
    left:0;
    font-weight:bold;
}

.hero-buttons{
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}

.btn-main{
    background:linear-gradient(135deg,#fb8c00,#f57c00);
    color:#fff;
    padding:14px 28px;
    border-radius:30px;
    text-decoration:none;
    font-weight:600;
    box-shadow:0 15px 30px rgba(245,124,0,0.3);
}

.btn-secondary{
    border:2px solid #f57c00;
    color:#f57c00;
    padding:12px 28px;
    border-radius:30px;
    text-decoration:none;
    font-weight:600;
}

/* ===== HERO IMAGE ===== */

.hero-image{
    text-align:center;
}

.hero-image img{
    width:380px;
    max-width:100%;
    filter:drop-shadow(0 20px 40px rgba(0,0,0,0.15));
}

/* ===== BENEFICIOS ===== */

.benefits{
    padding:70px 0;
}

.section-title{
    text-align:center;
    margin-bottom:40px;
}

.section-title h2{
    font-size:32px;
    color:#f57c00;
}

.benefit-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:30px;
}

.benefit{
    background:#fff;
    padding:30px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
    text-align:center;
}

.benefit h3{
    color:#f57c00;
    margin-bottom:10px;
}

/* ===== CARD CENTRAL ===== */

.highlight{
    background:linear-gradient(135deg,#fb8c00,#f57c00);
    color:#fff;
    padding:60px 0;
    text-align:center;
}

.highlight h2{
    font-size:32px;
    margin-bottom:20px;
}

.highlight p{
    max-width:700px;
    margin:auto;
    margin-bottom:25px;
}

/* ===== CTA FINAL ===== */

.cta{
    padding:60px 0;
    text-align:center;
}

.cta h2{
    font-size:30px;
    margin-bottom:20px;
}

/* ===== FOOTER ===== */

footer{
    background:#fafafa;
    padding:20px;
    text-align:center;
    font-size:13px;
    color:#777;
}

/* ===== RESPONSIVO ===== */

@media(max-width:900px){

.hero-content{
    grid-template-columns:1fr;
    text-align:center;
}

.hero-text h1{
    font-size:28px;
}

.hero-buttons{
    justify-content:center;
}

.benefit-grid{
    grid-template-columns:1fr;
}

.header-content{
    flex-direction:column;
    gap:10px;
}

.logo span{
    font-size:18px;
}

}

</style>
</head>

<body>

<!-- HEADER -->

<header>
<div class="container header-content">

<div class="logo">
<img src="img/logoo.png">
<span>Autodoc Gerencial</span>
</div>

<a class="btn-login" href="frmLogin.php">
Acessar sistema
</a>

</div>
</header>

<!-- HERO -->

<section class="hero">
<div class="container hero-content">

<div class="hero-text">

<h1>Gestão simples, moderna e eficiente para despachantes</h1>

<p>
O <strong>Autodoc Gerencial</strong> foi desenvolvido para facilitar o dia a dia
de despachantes e empresas automotivas, oferecendo controle total
sobre clientes, serviços e finanças.
</p>

<ul>
<li>Cadastro de clientes e veículos</li>
<li>Gestão de produtos e serviços</li>
<li>Contas a pagar e receber</li>
<li>Fluxo de caixa em tempo real</li>
<li>Organização e segurança das informações</li>
</ul>

<div class="hero-buttons">
<a class="btn-main" href="frmLogin.php">Entrar no Autodoc</a>
<a class="btn-secondary" href="#">Ver funcionalidades</a>
</div>

</div>

<div class="hero-image">
<img src="img/logoo.png">
</div>

</div>
</section>

<!-- BENEFICIOS -->

<section class="benefits">
<div class="container">

<div class="section-title">
<h2>Controle total do seu negócio</h2>
</div>

<div class="benefit-grid">

<div class="benefit">
<h3>Financeiro organizado</h3>
<p>Controle contas a pagar, receber e fluxo de caixa em tempo real.</p>
</div>

<div class="benefit">
<h3>Clientes e veículos</h3>
<p>Cadastre e acompanhe todos os atendimentos com facilidade.</p>
</div>

<div class="benefit">
<h3>Gestão completa</h3>
<p>Tudo centralizado em um único sistema moderno e seguro.</p>
</div>

</div>
</div>
</section>

<!-- DESTAQUE -->

<section class="highlight">

<div class="container">

<h2>Centralize todo o seu despacho em um único sistema</h2>

<p>
Tenha visão clara do financeiro, organize seus atendimentos
e aumente sua produtividade com uma ferramenta pensada
para o dia a dia do despachante.
</p>

<a class="btn-main" href="frmLogin.php">
Começar agora
</a>

</div>
</section>

<!-- CTA FINAL -->

<section class="cta">

<div class="container">

<h2>Pronto para organizar seu despacho?</h2>

<a class="btn-main" href="frmLogin.php">
Entrar no Autodoc Gerencial
</a>

</div>

</section>

<?php include 'base/footer-new.php'; ?>

</body>
</html>