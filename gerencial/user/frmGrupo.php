<?php
require_once __DIR__ . '/../base/connection.php';
require_once __DIR__ . '/../base/baseFuncoes.php';
require_once __DIR__ . '/../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

$id = $_GET['Codigo'] ?? null;

if ($id) {

    $dados = ExSqlNET(
        "SELECT *
        FROM grupos
        WHERE id = ?
        AND idEmpresa = ?",
        null,
        [$id, $_SESSION['idEmpresa']]
    );

    $dados = $dados[0] ?? null;
    $Alterando = true;

} else {
    $Alterando = false;
}

$msgRetorno = '';
$tipoMsg = '';

if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) || isset($_POST['excluir'])) {

    $dados = [];

    $dados['idEmpresa'] = $_SESSION['idEmpresa'] ?? null;
    $dados['Nome']      = trim($_POST['Nome'] ?? '');
    $dados['Tipo']      = $_POST['Tipo'] ?? '';
    $dados['Inativo']   = isset($_POST['Inativo']) ? 1 : 0;
    $dados['id']        = $id;

    if ($dados['Nome'] === '') {
        $_SESSION['mensagem_erro'] = "Informe o nome do grupo.";
        header("Location: Grupos");
        exit;
    }

    if ($dados['Tipo'] === '') {
        $_SESSION['mensagem_erro'] = "Informe o tipo do grupo.";
        header("Location: Grupos");
        exit;
    }

    if ($Alterando === false) {

        $retorno = Grupo($dados, "CADASTRAR");

        if ($retorno === "") {

            $_SESSION['mensagem_sucesso'] = "Grupo cadastrado com sucesso!";
            header("Location: Grupos");
            exit;

        } else {

            $_SESSION['mensagem_erro'] = "Erro ao cadastrar o grupo. Erro -> " . $retorno;
            header("Location: Grupos");
            exit;
        }

    } else if ($Alterando === true && isset($_POST['salvar'])) {

        $retorno = Grupo($dados, "ATUALIZAR");

        if ($retorno === "") {

            $_SESSION['mensagem_sucesso'] = "Grupo atualizado com sucesso!";
            header("Location: Grupos");
            exit;

        } else {

            $_SESSION['mensagem_erro'] = "Erro ao atualizar o grupo. Erro -> " . $retorno;
            header("Location: Grupos");
            exit;
        }

    } else if (isset($_POST['excluir'])) {

        $retorno = Grupo($dados, "EXCLUIR");

        if ($retorno === "") {

            $_SESSION['mensagem_sucesso'] = "Grupo excluído com sucesso!";
            header("Location: Grupos");
            exit;

        } else {

            $_SESSION['mensagem_erro'] = "Erro ao excluir o grupo. Erro -> " . $retorno;
            header("Location: Grupos");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Grupos | Autodoc Gerencial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">

    <style>

        .content{
            margin-left:240px;
            padding:30px;
        }

        @media(max-width:768px){
            .content{
                margin-left:0;
                padding:20px;
            }
        }

        .page-title{
            font-size:24px;
            font-weight:700;
            color:#334155;
            margin-bottom:20px;
        }

        .card{
            background:#ffffff;
            padding:24px;
            border-radius:12px;
            box-shadow:0 6px 14px rgba(0,0,0,0.05);
            margin-bottom:30px;
        }

        .form-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:20px;
        }

        label{
            font-size:14px;
            color:#64748b;
            margin-bottom:6px;
            display:block;
        }

        input,
        select{
            width:100%;
            padding:10px 12px;
            border:1px solid #e5e7eb;
            border-radius:8px;
            font-size:14px;
        }

        .actions{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            margin-top:20px;
        }

        .checkbox{
            display:flex;
            align-items:center;
            gap:8px;
            margin-top:28px;
        }

        .checkbox input{
            width:auto;
        }

    </style>
</head>

<body>

<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">

    <div class="page-title">Cadastro de Grupos</div>

    <?php if ($msgRetorno): ?>

        <div class="alert <?= $tipoMsg ?>">
            <?= htmlspecialchars($msgRetorno) ?>
        </div>

    <?php endif; ?>

    <?php
        if ($tipoMsg === 'success') {
            $_POST = [];
        }
    ?>

    <div class="card">

        <form method="post">

            <div class="form-grid">

                <div>
                    <label>Nome</label>
                    <input
                        type="text"
                        name="Nome"
                        id="Nome"
                        placeholder="Ex: Eletrônicos, Clientes VIP..."
                        value="<?= $Alterando ? htmlspecialchars($dados['Nome']) : '' ?>"
                    >
                </div>

                <div>
                    <label>Tipo do Grupo</label>
                    <select name="Tipo" id="Tipo">

                        <option value="">Selecione</option>

                        <option
                            value="P"
                            <?= ($Alterando && $dados['Tipo'] == 'P') ? 'selected' : '' ?>
                        >
                            Produtos / Serviços
                        </option>

                        <option
                            value="C"
                            <?= ($Alterando && $dados['Tipo'] == 'C') ? 'selected' : '' ?>
                        >
                            Clientes
                        </option>

                    </select>
                </div>

                <div class="checkbox">

                    <input
                        type="checkbox"
                        id="Inativo"
                        name="Inativo"
                        value="1"
                        <?= ($Alterando && $dados['Inativo'] == 1) ? 'checked' : '' ?>
                    >

                    <label for="Inativo">Inativo</label>

                </div>

            </div>

            <div class="actions">

                <a href="Grupos" class="btn btn-secondary">
                    ← Listar Grupos
                </a>

                <?php if ($Alterando): ?>

                    <button
                        type="submit"
                        name="excluir"
                        class="btn-excluir"
                        onclick="return confirm('Tem certeza que deseja excluir este grupo? Essa ação não pode ser desfeita.')"
                    >
                        🗑 Excluir
                    </button>

                <?php endif; ?>

                <button
                    type="submit"
                    name="salvar"
                    class="btn"
                >
                    💾 Salvar
                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>