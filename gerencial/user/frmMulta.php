<?php

require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'];
$idUser = $_SESSION['usuario_id'];

$listaForcli = ExSqlNET(
    "SELECT Id, Nome, Documento
     FROM forcli
     WHERE Inativo = 0
       AND idEmpresa = ?
     ORDER BY Nome ASC",
    null,
    [$idEmpresa]
);

$id = $_GET['id'] ?? null;
$Alterando = false;
$dados = null;
$msgRetorno = '';
$tipoMsg = '';

if ($id) {
    $dadosBusca = ExSqlNET(
        "SELECT multa.*,
                c.Nome AS ClienteNome,
                c.Documento AS ClienteDocumento,
                u.Nome AS NomeUsuario
         FROM multa
         LEFT JOIN forcli c ON c.Id = multa.Forcli
         LEFT JOIN user u ON u.Id = multa.idUser
         WHERE multa.Id = ?
           AND multa.idEmpresa = ?",
        null,
        [$id, $idEmpresa]
    );

    $dados = $dadosBusca[0] ?? null;
    $Alterando = !empty($dados);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {

    $dadosCadastro = [
        'id' => $id,
        'DataCadastro' => !empty($_POST['data']) ? $_POST['data'] : null,
        'idEmpresa' => $idEmpresa,
        'Forcli' => $_POST['clienteId'] ?? 0,
        'SerieMulta' => trim($_POST['serieMulta'] ?? ''),
        'CodigoProcesso' => trim($_POST['codigoProcesso'] ?? ''),
        'OrgaoFiscalizador' => trim($_POST['orgaoFiscalizador'] ?? ''),
        'PlacaVeiculo' => strtoupper(trim($_POST['placaVeiculo'] ?? '')),
        'PlacasAdicionais' => strtoupper(trim($_POST['placasAdicionais'] ?? '')),
        'RegistroCNH' => trim($_POST['registroCNH'] ?? ''),
        'PrazoDefesa' => !empty($_POST['prazoDefesa']) ? $_POST['prazoDefesa'] : null,
        'AutoSuspensiva' => isset($_POST['autoSuspensiva']) ? 1 : 0,
        'RecursoMulta' => isset($_POST['recursoMulta']) ? 1 : 0,
        'StatusMulta' => (int)($_POST['statusMulta'] ?? 0),
        'Observacao' => trim($_POST['observacao'] ?? ''),
        'EnviarLembrete' => isset($_POST['lembrete']) ? 1 : 0,
        'idUser' => $idUser,
        'UserAlt' => $idUser
    ];

    try {

        if (empty($dadosCadastro['Forcli'])) {
            $_SESSION['mensagem_erro'] = 'Necessário selecionar um cliente!';
            $msgRetorno = $_SESSION['mensagem_erro'];
            $tipoMsg = "error";
            header('Location: Multa');
            exit;
        }

        if (empty($dadosCadastro['SerieMulta'])) {
            $_SESSION['mensagem_erro'] = 'Necessário informar a série da multa!';
            $msgRetorno = $_SESSION['mensagem_erro'];
            $tipoMsg = "error";
            header('Location: Multa' . ($id ? '?id=' . $id : ''));
            exit;
        }

        if (!$Alterando) {
            $retorno = Multa($dadosCadastro, 'CADASTRAR');
        } else {
            $retorno = Multa($dadosCadastro, 'ATUALIZAR');
        }

        if ($retorno === '') {
            $_SESSION['mensagem_sucesso'] = $Alterando
                ? 'Multa atualizada com sucesso!'
                : 'Multa cadastrada com sucesso!';

            $msgRetorno = $_SESSION['mensagem_sucesso'];
            $tipoMsg = "success";

            header('Location: Multas');
            exit;
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao salvar multa: ' . $retorno;
            $msgRetorno = $_SESSION['mensagem_erro'];
            $tipoMsg = "error";
        }

    } catch (Exception $e) {
        $_SESSION['mensagem_erro'] = 'Erro interno: ' . $e->getMessage();
        $msgRetorno = $_SESSION['mensagem_erro'];
        $tipoMsg = "error";
    }
}

// ========================= EXCLUIR MULTA =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir']) && $Alterando) {

    $dadosExcluir = [
        'id'        => (int)$id,
        'idEmpresa' => (int)$idEmpresa,
        'UserAlt'   => (int)$idUser
    ];
    $retorno = Multa($dadosExcluir, "EXCLUIR");
    if ($retorno === "") {
        $_SESSION['mensagem_sucesso'] = "Multa excluída com sucesso!";
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao excluir multa: " . $retorno;
    }
    header("Location: Multas");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Multa</title>

    <link rel="icon" href="../img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <link rel="stylesheet" href="/gerencial/css/home.css">

    <style>

        .btn-cliente-lateral {
            min-width: 160px;
        }

        .cliente-linha {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .cliente-linha input[type="text"] {
            flex: 1;
            height: 56px;
            border-radius: 10px;
            padding: 0 15px;
            font-size: 14px;
            background: #f3f4f6;
            border: 1px solid #ddd;
        }

        .btn-cliente-lateral {
            height: 56px;
            padding: 0 20px;
            background: #ea580c;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
        }

        .btn-cliente-lateral:hover {
            background: #c2410c;
        }

        .btn-cliente {
            background: #ea580c;
            color: #fff;
            border: none;
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-cliente:hover {
            background: #c2410c;
        }



        .multa-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .modal h3 {
            margin: 0 0 12px 0;
            font-size: 18px;
            color: #333;
        }

        .checkbox-group {
            display: flex;
            gap: 25px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            resize: vertical;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .alerta-autossuspensiva {
            background: #fff3cd;
            border: 1px solid #ffe69c;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            display: none;
            font-weight: 600;
        }

        .modal-bg {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(3px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal {
            background: #fff;
            width: 650px;
            max-width: 95%;
            border-radius: 14px;
            padding: 22px;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 12px 35px rgba(0,0,0,0.18);
        }

        .modal-search {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }

        .modal-table-wrapper {
            max-height: 380px;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .modal table {
            width: 100%;
            border-collapse: collapse;
        }

        .modal th,
        .modal td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        .modal thead {
            background: #fff7ed;
            position: sticky;
            top: 0;
        }

        .modal tr:hover {
            background: #fff7ed;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../base/navbarUser.php'; ?>

<div class="content">

    <div class="page-title">Cadastro de Multa</div>

        <?php
            $msgRetorno = "";
            $tipoMsg = "";
            if (isset($_SESSION['mensagem_sucesso'])) {
                $msgRetorno = $_SESSION['mensagem_sucesso'];
                $tipoMsg = "success";
                unset($_SESSION['mensagem_sucesso']);
            }
            if (isset($_SESSION['mensagem_erro'])) {
                $tipoMsg = "error";
                $msgRetorno = $_SESSION['mensagem_erro'];
                unset($_SESSION['mensagem_erro']);
            }

            if ($msgRetorno): ?>
            <div class="alert <?= $tipoMsg ?>">
                <?= htmlspecialchars($msgRetorno) ?>
            </div>
            <?php endif;

            if ($tipoMsg === 'success') {
                $_POST = [];
            }
        ?>

    <?php if ($Alterando && !empty($dados['NomeUsuario'])): ?>
        <div style="font-size:12px;color:#999;text-align:right;margin-bottom:15px;">
            👤 <?= htmlspecialchars($dados['NomeUsuario']) ?> •
            <?= !empty($dados['DataAlt']) ? date('d/m/Y H:i', strtotime($dados['DataAlt'])) : '' ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <div class="card">
            <label>Cliente</label>

            <div class="cliente-linha">
                <input type="text"
                    id="clienteNome"
                    readonly
                    placeholder="Selecione um cliente..."
                    value="<?= $Alterando ? htmlspecialchars($dados['ClienteNome'] ?? '') : '' ?>">

                <input type="hidden"
                    name="clienteId"
                    id="clienteId"
                    value="<?= $Alterando ? $dados['Forcli'] ?? '' : '' ?>">

                <button type="button" class="btn" onclick="abrirModal()">
                    Selecionar Cliente
                </button>
            </div>
        </div>

        <div class="card">
            <div class="multa-grid">

                <div>
                    <label>Data</label>
                    <input type="date" name="data"
                        value="<?= 
                            $Alterando && !empty($dados['DataCadastro']) 
                                ? date('Y-m-d', strtotime($dados['DataCadastro'])) 
                                : date('Y-m-d') 
                        ?>">
                </div>

                <div>
                    <label>Série da Multa</label>
                    <input type="text" name="serieMulta" required
                        value="<?= $Alterando ? htmlspecialchars($dados['SerieMulta'] ?? '') : '' ?>">
                </div>

                <div>
                    <label>Número do Processo</label>
                    <input type="text" name="codigoProcesso"
                        value="<?= $Alterando ? htmlspecialchars($dados['CodigoProcesso'] ?? '') : '' ?>">
                </div>

                <div>
                    <label>Orgão Fiscalizador</label>
                    <select name="orgaoFiscalizador">
                        <option value="">Selecione</option>
                        <?php
                        $orgaos = ['Brigada Militar', 'PRF', 'DAER', 'DETRAN'];
                        foreach ($orgaos as $orgao):
                            $selected = ($Alterando && ($dados['OrgaoFiscalizador'] ?? '') === $orgao) ? 'selected' : '';
                        ?>
                            <option value="<?= $orgao ?>" <?= $selected ?>><?= $orgao ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Placa Principal</label>
                    <input type="text" name="placaVeiculo" maxlength="10"
                        value="<?= $Alterando ? htmlspecialchars($dados['PlacaVeiculo'] ?? '') : '' ?>">
                </div>

                <div>
                    <label>Placas Adicionais</label>
                    <input type="text" name="placasAdicionais"
                           placeholder="AAA0A00, BBB1B11"
                        value="<?= $Alterando ? htmlspecialchars($dados['PlacasAdicionais'] ?? '') : '' ?>">
                </div>

                <div>
                    <label>Registro CNH</label>
                    <input type="text" name="registroCNH"
                        value="<?= $Alterando ? htmlspecialchars($dados['RegistroCNH'] ?? '') : '' ?>">
                </div>

                <div>
                    <label>Prazo de Defesa</label>
                    <input type="date" name="prazoDefesa"
                        value="<?= $Alterando && !empty($dados['PrazoDefesa']) ? date('Y-m-d', strtotime($dados['PrazoDefesa'])) : '' ?>">
                </div>

                <div>
                    <label>Status</label>
                    <select name="statusMulta">
                        <?php
                        $statusList = [
                            0 => 'Em Aberto',
                            1 => 'Defesa Enviada',
                            2 => 'Em Recurso',
                            3 => 'Deferida',
                            4 => 'Indeferida',
                            5 => 'Finalizada'
                        ];
                        foreach ($statusList as $valor => $nome):
                            $selected = ($Alterando && (int)($dados['StatusMulta'] ?? 0) === $valor) ? 'selected' : '';
                        ?>
                            <option value="<?= $valor ?>" <?= $selected ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="checkbox-group">

                <label>
                    <input type="checkbox" name="autoSuspensiva" id="autoSuspensiva"
                        <?= $Alterando && !empty($dados['AutoSuspensiva']) ? 'checked' : '' ?>>
                    AutoSuspensiva
                </label>

                <label>
                    <input type="checkbox" name="recursoMulta"
                        <?= $Alterando && !empty($dados['RecursoMulta']) ? 'checked' : '' ?>>
                    Recurso de Multa
                </label>

                <label>
                    <input type="checkbox" name="lembrete"
                        <?= $Alterando && !empty($dados['EnviarLembrete']) ? 'checked' : '' ?>>
                    Ativar Lembrete
                </label>

            </div>

            <div class="alerta-autossuspensiva" id="alertaAuto">
                ⚠ Cliente possui multa autossuspensiva — atenção ao prazo de defesa.
            </div>
        </div>

        <div class="card">
            <label>Observação</label>
            <textarea name="observacao"><?= $Alterando ? htmlspecialchars($dados['Observacao'] ?? '') : '' ?></textarea>
        </div>

        <?php if ($Alterando): ?>
            <button type="submit"
                    name="excluir"
                    class="btn-excluir"
                    onclick="return confirm('Deseja excluir esta multa?')">
                Excluir
            </button>
        <?php endif; ?>

        <button class="btn-imprimir"
            onclick="window.open('Multa/Imprimir?id=<?= $id ?>', '_blank')">
            🖨 Gerar PDF
        </button>

        <button type="submit" name="salvar" class="btn">
            Salvar Multa
        </button>

    </form>
</div>

<!-- MODAL PADRÃO -->
<div class="modal-bg" id="modalBg">
    <div class="modal">
        <h3 id="modalTitulo"></h3>
        <input type="text" id="modalBusca" class="modal-search" placeholder="Digite para buscar...">

        <div class="modal-table-wrapper">
            <table>
                <thead>
                    <tr id="modalHead"></tr>
                </thead>
                <tbody id="modalBody"></tbody>
            </table>
        </div>

        <button class="btn" onclick="fecharModal()">Fechar</button>
    </div>
</div>

<script>
const clientes = <?= json_encode($listaForcli) ?>;

function abrirModal() {
    document.getElementById('modalTitulo').innerText = 'Selecionar Cliente';
    document.getElementById('modalBg').style.display = 'flex';

    document.getElementById('modalHead').innerHTML = `
        <th>Nome</th>
        <th>Documento</th>
    `;

    carregarClientes('');
    document.getElementById('modalBusca').value = '';

    setTimeout(() => {
        document.getElementById('modalBusca').focus();
    }, 50);
}

function fecharModal() {
    document.getElementById('modalBg').style.display = 'none';
}

function carregarClientes(filtro = '') {
    filtro = filtro.toLowerCase();
    let html = '';
    clientes
        .filter(c =>
            (c.Nome || '').toLowerCase().includes(filtro) ||
            (c.Documento || '').includes(filtro)
        )
        .forEach(c => {
            let nomeSeguro = (c.Nome || '').replace(/'/g, "\\'");
            html += `
                <tr onclick="selecionarCliente(${c.Id}, '${nomeSeguro}')">
                    <td>${c.Nome}</td>
                    <td>${c.Documento || ''}</td>
                </tr>
            `;
        });

    document.getElementById('modalBody').innerHTML = html;
}

function selecionarCliente(id, nome) {
    document.getElementById('clienteId').value = id;
    document.getElementById('clienteNome').value = nome;
    fecharModal();
}

document.getElementById('autoSuspensiva').addEventListener('change', function() {
    document.getElementById('alertaAuto').style.display = this.checked ? 'block' : 'none';
});

window.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('autoSuspensiva').checked) {
        document.getElementById('alertaAuto').style.display = 'block';
    }
});

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('modalBusca').addEventListener('input', function () {
        carregarClientes(this.value);
    });
    document.getElementById('modalBg').addEventListener('click', function (e) {
        if (e.target === this) fecharModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') fecharModal();
    });
});
</script>

</body>
</html>