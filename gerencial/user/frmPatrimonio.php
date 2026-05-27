<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'] ?? 1;

/* SALVAR */
if(isset($_POST['descricao']))
{
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'] ?? 0;
    $data = $_POST['data'];

    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);

    $sql = "
    INSERT INTO patrimonio
    (idEmpresa,descricao,valor,dataCompra,dataAlt)
    VALUES
    ($idEmpresa,'$descricao','$valor','$data',NOW())
    ";

    ExSqlNet($sql);
}

/* EXCLUIR */
if(isset($_GET['del']))
{
    $id = intval($_GET['del']);
    ExSqlNet("DELETE FROM patrimonio WHERE id=$id");
}

/* LISTAR */
$lista = ExSqlNet("
    SELECT *
    FROM patrimonio
    WHERE idEmpresa=$idEmpresa
    ORDER BY dataCompra DESC
");

$total = 0;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Patrimônio - Autodoc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/home.css">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
<style>

    .tituloPagina {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 25px;
    }

    .cardForm {

        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;

    }

    .formLinha {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .formLinha input {
        flex: 1;
        min-width: 180px;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
    }

    .formLinha input:focus {
        border-color: #ff6b00;
        outline: none;
    }

    .btnAdicionar {
        background: #ff6b00;
        border: none;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .btnAdicionar:hover {
        background: #e25f00;
    }

    .tabelaPatrimonio {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }

    .tabelaPatrimonio thead {
        background: #f5f5f5;
    }

    .tabelaPatrimonio th {
        text-align: left;
        padding: 14px;
        font-weight: 600;
        font-size: 14px;
        color: #555;
    }

    .tabelaPatrimonio td {
        padding: 14px;
        border-top: 1px solid #eee;
        font-size: 14px;
    }

    .tabelaPatrimonio tr:hover {
        background: #fafafa;
    }

    .btnExcluir {
        color: #e53935;
        text-decoration: none;
        font-weight: 600;
    }

    .btnExcluir:hover {
        text-decoration: underline;
    }

    .totalBox {
        margin-top: 30px;
        background: linear-gradient(90deg, #ff6b00, #ff8c3a);
        color: white;
        padding: 30px;
        text-align: center;
        border-radius: 10px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .totalTitulo {
        font-size: 18px;
        opacity: 0.9;
    }

    .totalValor {
        font-size: 36px;
        font-weight: 700;
        margin-top: 10px;
    }

    .navbar {
        position: fixed;
        top: 0;
        width: 100%;
        height: 70px;
        z-index: 1000;
    }
    
    body{
        padding-top:70px;
    }
    /* RESPONSIVO */

    @media(max-width:700px) {

        .formLinha {
            flex-direction: column;
        }

        .btnAdicionar {
            width: 100%;
        }

    }
    </style>

</head>

<body>
   
    <?php include __DIR__ . '/../base/navbarUser.php'; ?>
    <div class="content">
 
        <div class="page-title">Patrimônio / Compras</div>

        <div class="cardForm">

            <form method="POST">

                <div class="formLinha">

                    <input type="text" name="descricao" placeholder="Descrição do item" required>

                    <input type="date" name="data" value="<?= date('Y-m-d') ?>" required>

                    <input type="text" id="valor" name="valor" placeholder="Valor (R$)" required>

                    <button class="btnAdicionar">
                        Adicionar
                    </button>

                </div>

            </form>

        </div>


        <table class="tabelaPatrimonio">

            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th width="80"></th>
                </tr>
            </thead>

            <tbody>

                <?php foreach($lista as $item):

$total += $item['valor'];
?>

                <tr>

                    <td>
                        <?= date('d/m/Y',strtotime($item['dataCompra'])) ?>
                    </td>

                    <td>
                        <?= $item['descricao'] ?>
                    </td>

                    <td>
                        <strong>
                            R$ <?= number_format($item['valor'],2,',','.') ?>
                        </strong>
                    </td>

                    <td>

                        <a class="btnExcluir" onclick="return confirm('Excluir este item do patrimônio?')"
                            href="?del=<?= $item['id'] ?>">

                            Excluir

                        </a>

                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>


        <div class="totalBox">

            <div class="totalTitulo">
                TOTAL GERAL DO PATRIMÔNIO
            </div>

            <div class="totalValor">
                R$ <?= number_format($total,2,',','.') ?>
            </div>

        </div>

    </div>

</body>

</html>

<script>
//======================================================================
function formatarMoeda(campo) {
    let v = campo.value.replace(/\D/g, '');

    v = (v / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    campo.value = v;
}

//======================================================================
document.getElementById('valor')?.addEventListener('input', function() {
    formatarMoeda(this);
});
</script>