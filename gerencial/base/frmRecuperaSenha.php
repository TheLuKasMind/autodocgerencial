<?php

require_once '../base/connection.php';
require_once '../base/baseFuncoes.php';

$msg = "";
$sucesso = false;

/* =========================
   TOKEN
========================= */

$token = $_GET['token'] ?? $_POST['token'] ?? '';

if(!$token){
    die("Token inválido.");
}

/* =========================
   VERIFICA TOKEN
========================= */

$user = ExSqlNET("
    SELECT id
    FROM user
    WHERE tokenRecuperar = ?
    AND tokenExpira > NOW()
    LIMIT 1
", null, [$token]);

$user = $user[0] ?? null;

if(!$user){
    die("Token inválido ou expirado.");
}

/* =========================
   SALVAR NOVA SENHA
========================= */

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $senha = $_POST['senha'] ?? '';

    if(strlen($senha) < 4){

        $msg = "Senha muito curta.";

    }else{

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        ExSqlNET("
            UPDATE user
            SET senha = ?,
                tokenRecuperar = NULL,
                tokenExpira = NULL
            WHERE id = ?
        ", null, [$senhaHash, $user['id']]);

        $msg = "Senha atualizada com sucesso. Redirecionando para o login...";
        $sucesso = true;
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<title>Redefinir senha</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/base.css">
<link rel="icon" href="../img/favicon.png">
    
<?php if($sucesso): ?>
<meta http-equiv="refresh" content="3;url=../frmLogin.php">
<?php endif; ?>


<style>
body{
margin:0;
font-family:system-ui,-apple-system,Segoe UI,Roboto;
background:#f6f6f6;

min-height:100vh;

display:flex;
align-items:center;
justify-content:center;
}

.card{
display:flex;
flex-direction:column;
align-items:center;
}

.card h2{
width:100%;
text-align:left;
margin-bottom:12px;
}

.card input{
margin-top:0;
}
.wrapper{
width:100%;
display:flex;
align-items:center;
justify-content:center;
padding:20px;
margin:0;
}
/* TOPO LARANJA */

.topbar{
height:140px;
background:#f97316;
}

/* CONTAINER */

.wrapper{
flex:1;
display:flex;
justify-content:center;
margin-top:-70px;
padding:20px;
}

/* CARD */

.card{

width:100%;
max-width:420px;

background:white;

border-radius:12px;

padding:30px 24px;

box-shadow:0 10px 25px rgba(0,0,0,0.08);

}

/* TITULO */

h2{
margin:0 0 20px 0;
font-size:22px;
color:#333;
text-align:center;
}

/* INPUT */

input{

width:100%;

padding:14px;

border-radius:8px;

border:1px solid #ddd;

font-size:16px;

margin-top:10px;

}

input:focus{
outline:none;
border-color:#f97316;
}

/* BOTAO */

button{

width:100%;

margin-top:20px;

padding:14px;

border:none;

border-radius:8px;

background:#f97316;

color:white;

font-size:16px;

font-weight:600;

cursor:pointer;

}

button:hover{
background:#ea580c;
}

/* MENSAGEM */

.msg{
margin-top:15px;
text-align:center;
font-size:14px;
}

.msg.ok{
color:#16a34a;
}

.msg.erro{
color:#dc2626;
}

/* MOBILE */

@media (max-width:480px){

.card{
padding:25px 18px;
}

}

</style>


</head>

<body>



<p><?=htmlspecialchars($msg)?></p>

<?php if(!$sucesso): ?>

<form method="POST">
<h2>Redefinir senha</h2>
<input type="hidden" name="token" value="<?=htmlspecialchars($token)?>">

<input type="password" name="senha" placeholder="Nova senha" required>

<br><br>

<button type="submit">Salvar nova senha</button>

</form>

<?php endif; ?>

</body>
</html>