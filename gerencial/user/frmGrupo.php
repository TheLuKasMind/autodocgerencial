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
        "SELECT * FROM grupos WHERE id = ? AND idEmpresa = ?",
        null,
        [$id, $_SESSION['idEmpresa']]
    );
    $dados = $dados[0] ?? null;
    $Alterando = true;
} else {
    $Alterando = false;
    $dados = [];
}

$msgRetorno = '';
$tipoMsg = '';

if ((($_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['salvar'])) || isset($_POST['excluir'])) {
    $dados = [];
    $dados['idEmpresa'] = $_SESSION['idEmpresa'] ?? null;
    $dados['Nome'] = trim($_POST['Nome'] ?? '');
    $dados['Tipo'] = $_POST['Tipo'] ?? '';
    $dados['Inativo'] = isset($_POST['Inativo']) ? 1 : 0;
    $dados['id'] = $id;

    if (isset($_POST['salvar']) && $dados['Nome'] === '') {
        $_SESSION['mensagem_erro'] = "Informe o nome do grupo.";
        header("Location: Grupos");
        exit;
    }

    if (isset($_POST['salvar']) && $dados['Tipo'] === '') {
        $_SESSION['mensagem_erro'] = "Informe o tipo do grupo.";
        header("Location: Grupos");
        exit;
    }

    if ($Alterando === false && isset($_POST['salvar'])) {
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
    } elseif ($Alterando === true && isset($_POST['salvar'])) {
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
    } elseif (isset($_POST['excluir'])) {
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
    <title><?= $Alterando ? 'Editar Grupo' : 'Cadastro de Grupos' ?> | Autodoc Gerencial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <style>
        .content{width:min(1180px,calc(100% - 40px));max-width:1180px;margin:30px auto 48px;padding:0}
        .content>form{display:flex;flex-direction:column;gap:20px}
        .page-title{font-size:28px;line-height:1.25;font-weight:750;letter-spacing:-.7px;color:#0f172a;margin:0 0 10px}
        .page-subtitle{font-size:14px;line-height:1.6;color:#64748b;margin:0 0 2px;padding-top:2px}
        .content .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 4px 18px rgba(15,23,42,.045);overflow:hidden}
        .section-heading{display:flex;align-items:center;gap:10px;margin:0 0 22px;color:#1e293b;font-size:16px;font-weight:750;line-height:1.4}
        .section-heading:before{content:"";display:block;width:4px;height:22px;border-radius:5px;background:#f97316;flex:0 0 4px}
        .section-description{margin:-12px 0 22px;color:#64748b;font-size:13px;line-height:1.6}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px 18px}
        .form-grid>div{min-width:0}
        .content label{display:block;margin:0 0 7px;color:#334155;font-size:13px;font-weight:650}
        .content input:not([type="checkbox"]),.content select{box-sizing:border-box;width:100%;min-height:43px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-family:inherit;font-size:14px;line-height:1.4;outline:none;transition:border-color .18s,box-shadow .18s,background .18s}
        .content input:not([type="checkbox"]):focus,.content select:focus{border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,.13)}
        .content input[type="checkbox"]{width:17px;height:17px;accent-color:#ea580c;cursor:pointer;margin:0;flex:0 0 auto}
        .content .checkbox{display:flex;align-items:center;gap:9px;align-self:end;min-height:43px;padding:0 2px}
        .content .checkbox label{margin:0;cursor:pointer;font-weight:550;color:#475569}
        .content .actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap;padding:0 2px}
        .content .btn,.content .btn-secondary,.content .btn-excluir{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:10px 16px;border:1px solid transparent;border-radius:10px;font-size:14px;font-weight:700;text-decoration:none;cursor:pointer;transition:transform .15s,box-shadow .15s,background .15s;box-sizing:border-box}
        .content .btn{background:#f97316;color:#fff;box-shadow:0 3px 8px rgba(249,115,22,.18)}
        .content .btn:hover{background:#ea580c;box-shadow:0 5px 12px rgba(249,115,22,.23);transform:translateY(-1px)}
        .content .btn-secondary{background:#fff;color:#334155;border-color:#cbd5e1}
        .content .btn-secondary:hover{background:#f8fafc;border-color:#94a3b8}
        .content .btn-excluir{background:#fff;color:#dc2626;border-color:#fecaca}
        .content .btn-excluir:hover{background:#fef2f2;border-color:#fca5a5}
        .content .alert{padding:13px 16px;border-radius:11px;border:1px solid #e2e8f0;font-size:14px;margin-bottom:18px}
        .content .alert.success{background:#f0fdf4;color:#166534;border-color:#bbf7d0}
        .content .alert.error{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
        .status-note{margin-top:18px;padding:13px 15px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;font-size:13px;line-height:1.55}
        @media(max-width:850px){.content{width:calc(100% - 28px);margin-top:22px}.content .card{padding:20px}}
        @media(max-width:560px){.content{width:calc(100% - 22px);margin:16px auto 30px}.page-title{font-size:23px;margin-bottom:8px}.page-subtitle{padding-top:3px;margin-bottom:3px}.content .card{padding:16px;border-radius:13px}.form-grid{grid-template-columns:1fr;gap:16px}.content .actions{align-items:stretch;flex-direction:column}.content .actions>*{width:100%;box-sizing:border-box}}
    </style>
</head>
<body>
<?php include __DIR__ . '/../base/navbarUser.php'; ?>
<div class="content">
    <div class="page-title"><?= $Alterando ? 'Editar Grupo' : 'Cadastro de Grupos' ?></div>
    <div class="page-subtitle"><?= $Alterando ? 'Atualize as informações deste grupo.' : 'Organize produtos e clientes em grupos para facilitar a gestão.' ?></div>

    <form method="post">
        <div class="card">
            <h2 class="section-heading">Informações do grupo</h2>
            <p class="section-description">Defina o nome, a categoria e o status do grupo.</p>
            <div class="form-grid">
                <div>
                    <label for="Nome">Nome do grupo</label>
                    <input type="text" name="Nome" id="Nome" placeholder="Ex.: Eletrônicos, Clientes VIP..." value="<?= $Alterando ? htmlspecialchars($dados['Nome'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" required>
                </div>
                <div>
                    <label for="Tipo">Tipo do grupo</label>
                    <select name="Tipo" id="Tipo" required>
                        <option value="">Selecione o tipo</option>
                        <option value="P" <?= ($Alterando && ($dados['Tipo'] ?? '') === 'P') ? 'selected' : '' ?>>Produtos / Serviços</option>
                        <option value="C" <?= ($Alterando && ($dados['Tipo'] ?? '') === 'C') ? 'selected' : '' ?>>Clientes</option>
                    </select>
                </div>
                <div class="checkbox">
                    <input type="checkbox" id="Inativo" name="Inativo" value="1" <?= ($Alterando && (int)($dados['Inativo'] ?? 0) === 1) ? 'checked' : '' ?>>
                    <label for="Inativo">Marcar grupo como inativo</label>
                </div>
            </div>
            <div class="status-note">Grupos inativos permanecem cadastrados, mas ficam identificados como inativos na listagem.</div>
        </div>

        <div class="actions">
            <a href="Grupos" class="btn-secondary">← Listar Grupos</a>
            <?php if ($Alterando): ?>
                <button type="submit" name="excluir" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir este grupo? Essa ação não pode ser desfeita.')">🗑 Excluir</button>
            <?php endif; ?>
            <button type="submit" name="salvar" class="btn">💾 Salvar</button>
        </div>
    </form>
</div>
</body>
</html>
