<?php
require_once __DIR__ . '/../base/connection.php';
require_once __DIR__ . '/../base/baseFuncoes.php';
require_once __DIR__ . '/../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: Login');
    exit;
}

$id = $_GET['Codigo'] ?? null;
$dados = [];

if ($id) {
    $resultado = ExSqlNET(
        'SELECT * FROM tipodespesa WHERE id = ? AND idEmpresa = ?',
        null,
        [$id, $_SESSION['idEmpresa']]
    );
    $dados = $resultado[0] ?? [];
    $Alterando = !empty($dados);
} else {
    $Alterando = false;
}

$msgRetorno = '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['salvar']) || isset($_POST['excluir']))) {
    $dadosPost = [];
    $acaoSelecionada = (string)($_POST['Acao'] ?? '');
    switch ($acaoSelecionada) {
        case '1':
            $dadosPost['Acao'] = -1;
            break;
        case '2':
            $dadosPost['Acao'] = 1;
            break;
        case '3':
            $dadosPost['Acao'] = 0;
            break;
        default:
            $dadosPost['Acao'] = null;
    }

    $dadosPost['idEmpresa'] = $_SESSION['idEmpresa'] ?? null;
    $dadosPost['Categoria'] = $_POST['Categoria'] ?? null;
    $dadosPost['Inativo'] = isset($_POST['Inativo']) ? 1 : 0;
    $dadosPost['Descricao'] = trim($_POST['Nome'] ?? '');
    $dadosPost['Nome'] = trim($_POST['Nome'] ?? '');
    $dadosPost['ValorBase'] = formataValorGravacao($_POST['ValorBase'] ?? 0);
    $dadosPost['id'] = $id;

    if (isset($_POST['salvar']) && $dadosPost['Nome'] === '') {
        $_SESSION['mensagem_erro'] = 'Informe nome para a despesa / conta!';
        header('Location: Despesas');
        exit;
    }

    if (isset($_POST['salvar']) && !$Alterando) {
        $dadosPost['Codigo'] = retornaProximoCod('tipodespesa');
        $retorno = Despesa($dadosPost, 'CADASTRAR');
        if ($retorno === '') {
            $_SESSION['mensagem_sucesso'] = 'Despesa / Conta cadastrada com sucesso!';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao cadastrar a despesa / conta. Erro -> ' . $retorno;
        }
        header('Location: Despesas');
        exit;
    } elseif (isset($_POST['salvar']) && $Alterando) {
        $retorno = Despesa($dadosPost, 'ATUALIZAR');
        if ($retorno === '') {
            $_SESSION['mensagem_sucesso'] = 'Despesa / Conta atualizada com sucesso!';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao atualizar a despesa / conta. Erro -> ' . $retorno;
        }
        header('Location: Despesas');
        exit;
    } elseif (isset($_POST['excluir'])) {
        $retorno = Despesa($dadosPost, 'EXCLUIR');
        if ($retorno === '') {
            $_SESSION['mensagem_sucesso'] = 'Despesa / Conta excluída com sucesso!';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao excluir a despesa / conta. Erro -> ' . $retorno;
        }
        header('Location: Despesas');
        exit;
    }
}

$acaoAtual = $Alterando ? (string)($dados['Acao'] ?? '') : '-1';
$categoriaAtual = $Alterando ? (string)($dados['Categoria'] ?? '') : '1';
$valorBaseAtual = '';
if ($Alterando && isset($dados['ValorBase'])) {
    $valorBaseAtual = number_format((float)str_replace(',', '.', (string)$dados['ValorBase']), 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $Alterando ? 'Editar Despesa / Conta' : 'Cadastro de Despesas / Contas' ?> | Autodoc Gerencial</title>
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
    <div class="page-title"><?= $Alterando ? 'Editar Despesa / Conta' : 'Cadastro de Despesas / Contas' ?></div>
    <div class="page-subtitle"><?= $Alterando ? 'Atualize as informações desta despesa ou conta.' : 'Cadastre e organize as despesas e contas do seu sistema.' ?></div>

    <form method="post">
        <div class="card">
            <h2 class="section-heading">Informações da despesa / conta</h2>
            <p class="section-description">Defina a descrição, o valor, a categoria e o impacto no caixa.</p>
            <div class="form-grid">
                <div>
                    <label for="Nome">Nome / Descrição</label>
                    <input type="text" name="Nome" id="Nome" placeholder="Ex.: Aluguel, Internet, Taxa Detran" value="<?= $Alterando ? htmlspecialchars($dados['Descricao'] ?? '', ENT_QUOTES, 'UTF-8') : '' ?>" required>
                </div>
                <div>
                    <label for="ValorBase">Valor (R$)</label>
                    <input type="text" id="ValorBase" name="ValorBase" inputmode="decimal" placeholder="0,00" value="<?= htmlspecialchars($valorBaseAtual, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div>
                    <label for="Acao">Tipo no boletim de caixa</label>
                    <select name="Acao" id="Acao">
                        <option value="1" <?= $acaoAtual === '-1' ? 'selected' : '' ?>>Saída</option>
                        <option value="2" <?= $acaoAtual === '1' ? 'selected' : '' ?>>Entrada</option>
                        <option value="3" <?= $acaoAtual === '0' ? 'selected' : '' ?>>Sem impacto no caixa</option>
                    </select>
                </div>
                <div>
                    <label for="Categoria">Categoria da despesa</label>
                    <select name="Categoria" id="Categoria">
                        <option value="1" <?= $categoriaAtual === '1' ? 'selected' : '' ?>>Fixa</option>
                        <option value="2" <?= $categoriaAtual === '2' ? 'selected' : '' ?>>Variável</option>
                        <option value="3" <?= $categoriaAtual === '3' ? 'selected' : '' ?>>Impostos</option>
                        <option value="4" <?= $categoriaAtual === '4' ? 'selected' : '' ?>>Serviços</option>
                        <option value="5" <?= $categoriaAtual === '5' ? 'selected' : '' ?>>Outros</option>
                    </select>
                </div>
                <div class="checkbox">
                    <input type="checkbox" id="Inativo" name="Inativo" value="1" <?= ($Alterando && (int)($dados['Inativo'] ?? 0) === 1) ? 'checked' : '' ?>>
                    <label for="Inativo">Marcar despesa / conta como inativa</label>
                </div>
            </div>
            <div class="status-note">Despesas e contas inativas permanecem cadastradas, mas podem ser identificadas como inativas na listagem.</div>
        </div>

        <div class="actions">
            <a href="Despesas" class="btn-secondary">← Listar Despesas</a>
            <?php if ($Alterando): ?>
                <button type="submit" name="excluir" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta despesa / conta? Essa ação não pode ser desfeita.')">🗑 Excluir</button>
            <?php endif; ?>
            <button type="submit" name="salvar" class="btn">💾 Salvar</button>
        </div>
    </form>
</div>
<script>
function formatarMoeda(campo) {
    let v = campo.value.replace(/\D/g, '');
    v = (v / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    campo.value = v;
}
document.getElementById('ValorBase')?.addEventListener('input', function () {
    formatarMoeda(this);
});
</script>
</body>
</html>
