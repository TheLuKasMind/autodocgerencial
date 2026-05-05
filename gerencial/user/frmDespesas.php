<?php
include '../base/baseFuncoes.php'; 
require_once '../base/connection.php'; 
require_once '../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}
$id = $_GET['Codigo'] ?? null;


if ($id) {
    $dados = ExSqlNET(
        "SELECT *
         FROM tipodespesa
         WHERE id = ?
            AND idEmpresa = ?",
        null,
        [$id, $_SESSION['idEmpresa']]
    );

    $dados = $dados[0] ?? null; // pega o registro
    $Alterando = true;

} else {
    $Alterando = false;
}

$msgRetorno =  '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])  || isset($_POST['excluir'])) {
    
    $dados = [];

    $dados['Acao'] = $_POST['Acao'] ?? null;
    switch ($dados['Acao']) {
        case 1:
            $dados['Acao'] = -1;
            break;
        case 2:
            $dados['Acao'] = 1;
            break;
        case 3:
            $dados['Acao'] = 0;
            break;
    }

    $dados['idEmpresa'] = $_SESSION['idEmpresa']  ?? null;
    $dados['Categoria'] = $_POST['Categoria'] ?? null;
    
    $dados['Inativo'] = isset($_POST['Inativo']) ? 1 : 0;
    $dados['Descricao'] = $_POST['Nome'] ?? "";
    $dados['ValorBase'] = formataValorGravacao($_POST['ValorBase'] ?? 0); 
    $dados['id'] = $id;

    $dados['Nome'] = $_POST['Nome'] ?? "";
    if ($Alterando === false){
        $dados['Codigo'] = retornaProximoCod("tipodespesa");
        $retorno = Despesa($dados, "CADASTRAR");
        if ($retorno === "") {
            $msgRetorno = "Despesa / Conta cadastrada com sucesso!";
            $tipoMsg = "success";
        } else {
            $msgRetorno = "Erro ao cadastrar a despesa / conta. Erro -> ". $retorno;
            $tipoMsg = "error";
        }
    }else if ($Alterando === true && isset($_POST['salvar'])){
        $retorno = Despesa($dados, "ATUALIZAR");
        if ($retorno === "") {
            $msgRetorno = "Despesa / Conta atualizada com sucesso!";
            $tipoMsg = "success";
        } else {
            $msgRetorno = "Erro ao atualizar a despesa / conta. Erro -> ". $retorno;
            $tipoMsg = "error";
        }
    }else if(isset($_POST['excluir'])){
        $retorno = Despesa($dados, "EXCLUIR");
        if ($retorno === "") {
            $msgRetorno = "Despesa / Conta excluída com sucesso!";
            $_SESSION['mensagem_sucesso'] = $msgRetorno;
            $tipoMsg = "success";
            header('Location: frmDespesasLista.php');
            exit;
        } else {
            $msgRetorno = "Erro ao excluir  a despesa / conta. Erro -> ". $retorno;
            $tipoMsg = "error";
        }
    }

}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Despesas e Contas | Autodoc Gerencial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/favicon.png">
    <!-- CSS BASE DO SISTEMA -->
    <link rel="stylesheet" href="../css/base.css">

    <!-- CSS ESPECÍFICO DA HOME / PÁGINAS INTERNAS -->
    <link rel="stylesheet" href="../css/home.css">

    <style>
        /* ===== CONTEÚDO ===== */
        .content {
            margin-left: 240px;
            padding: 30px;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 20px;
        }

        .card {
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 6px 14px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 6px;
            display: block;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .btn {
            background-color: #f97316;
            color: #ffffff;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #ea580c;
        }

        .btn-secondary {
            background-color: #fff7ed;
            color: #f97316;
            border: 1px solid #f97316;
        }
    </style>
</head>

<body>

<?php include '../base/navbarUser.php'; ?>

<div class="content">
    <div class="page-title">Cadastro de Despesas / Contas</div>
    <?php
        if ($msgRetorno): ?>
        <div class="alert <?= $tipoMsg ?>">
            <?= htmlspecialchars($msgRetorno) ?>
        </div>
        <?php endif;

        if ($tipoMsg === 'success') {
            $_POST = [];
        }
    ?>

    <div class="card">
        <form method="post">

            <div class="form-grid">

                <!-- <div>
                    <label>Código</label>
                    <input type="text" readonly placeholder="Código interno" value="<?= $Alterando ? htmlspecialchars($dados['Codigo']) : '' ?>">
                </div> -->

                <div>
                    <label>Nome</label>
                    <input type="text" name="Nome" id="Nome" placeholder="Ex: Aluguel, Internet, Taxa Detran" value="<?= $Alterando ? htmlspecialchars($dados['Descricao']) : '' ?>">
                </div>

                <div>
                    <label>Valor(R$)</label>
                    <input 
                        type="text"
                        id="ValorBase"
                        name="ValorBase"
                        placeholder="0,00"
                        value="<?= $Alterando 
                            ? number_format(
                                (float) str_replace(',', '.', $dados['ValorBase']), 
                                2, ',', '.'
                            ) 
                            : '' 
                        ?>"
                    >
                </div>

                <div>
                    <label>Tipo no Boletim de Caixa</label>
                    <select name="Acao" id="Acao">
                        <option value="1" <?= ($Alterando && $dados['Acao'] == -1) ? 'selected' : '' ?>>
                            Saída
                        </option>
                        <option value="2" <?= ($Alterando && $dados['Acao'] == 1) ? 'selected' : '' ?>>
                            Entrada
                        </option>
                        <option value="3" <?= ($Alterando && $dados['Acao'] == 0) ? 'selected' : '' ?>>
                            Sem impacto no caixa
                        </option>
                    </select>
                </div>

                <div>
                    <label>Categoria de Despesa</label>
                    <select name="Categoria" id ="Categoria">
                        <option value="1" <?= ($Alterando && $dados['Categoria'] == 1) ? 'selected' : '' ?>>
                            Fixa
                        </option>
                        <option value="2" <?= ($Alterando && $dados['Categoria'] == 2) ? 'selected' : '' ?>>
                            Variável
                        </option>
                        <option value="3" <?= ($Alterando && $dados['Categoria'] == 3) ? 'selected' : '' ?>>
                           Impostos
                        </option>
                        <option value="4" <?= ($Alterando && $dados['Categoria'] == 4) ? 'selected' : '' ?>>
                           Serviços
                        </option>
                        <option value="5" <?= ($Alterando && $dados['Categoria'] == 5) ? 'selected' : '' ?>>
                           Outros
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
                <a href="frmDespesasLista.php" class="btn btn-secondary">
                    Listar Despesas
                </a>

                <?php if ($Alterando): ?>
                    <button 
                        type="submit"
                        name="excluir"
                        class="btn-excluir"
                        onclick="return confirm('Tem certeza que deseja excluir esta despesa / conta? Essa ação não pode ser desfeita.')"
                    >
                        Excluir
                    </button>
                <?php endif; ?>

                <button type="submit" name= "salvar" class="btn">Salvar</button>

            </div>

        </form>
    </div>
</div>

<!--<?php include '../base/footer.php'; ?> -->

</body>
</html>
<script>
function abrirListaProdutos() {
    window.location.href = 'servprodLista.php';
}

//======================================================================
function formatarMoeda(campo) {
    let v = campo.value.replace(/\D/g, '');

    v = (v / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    campo.value = v;
}

//======================================================================
document.getElementById('ValorBase')?.addEventListener('input', function () {
    formatarMoeda(this);
});
</script>
