<?php
require_once "../config/conexao.php";
require_once "../includes/header.php";

$sql = "
    SELECT 
        vendas.id,
        vendas.produto_id,
        vendas.cliente_nome,
        vendas.forma_pagamento,
        vendas.status_pagamento,
        vendas.quantidade,
        vendas.valor_venda_unitario,
        vendas.valor_total,
        vendas.lucro_total,
        vendas.criado_em,
        produtos.nome AS produto_nome,
        produtos.valor_compra
    FROM vendas
    INNER JOIN produtos ON produtos.id = vendas.produto_id
    ORDER BY vendas.criado_em DESC
";

$resultado = $conn->query($sql);

$sqlResumo = "
    SELECT 
        SUM(valor_total) AS total_vendido,
        SUM(quantidade) AS total_itens,

        SUM(CASE 
            WHEN status_pagamento = 'Pago' 
            THEN valor_total 
            ELSE 0 
        END) AS total_recebido,

        SUM(CASE 
            WHEN status_pagamento = 'Pago' 
            THEN lucro_total 
            ELSE 0 
        END) AS lucro_recebido,

        SUM(CASE 
            WHEN status_pagamento = 'Pendente' 
            THEN valor_total 
            ELSE 0 
        END) AS total_a_receber,

        SUM(CASE 
            WHEN status_pagamento = 'Pendente' 
            THEN lucro_total 
            ELSE 0 
        END) AS lucro_a_receber

    FROM vendas
";

$resumo = $conn->query($sqlResumo)->fetch_assoc();

$totalVendido = $resumo["total_vendido"] ?? 0;
$totalItens = $resumo["total_itens"] ?? 0;

$totalRecebido = $resumo["total_recebido"] ?? 0;
$lucroRecebido = $resumo["lucro_recebido"] ?? 0;

$totalAReceber = $resumo["total_a_receber"] ?? 0;
$lucroAReceber = $resumo["lucro_a_receber"] ?? 0;
?>

<h2>Vendas Realizadas</h2>

<div class="cards">
    <div class="card">
        <h3>Total vendido</h3>
        <p><?= formatarMoeda($totalVendido) ?></p>
    </div>

    <div class="card">
        <h3>Total recebido</h3>
        <p><?= formatarMoeda($totalRecebido) ?></p>
    </div>

    <div class="card">
        <h3>Valores a receber</h3>
        <p><?= formatarMoeda($totalAReceber) ?></p>
    </div>

    <div class="card">
        <h3>Lucro recebido</h3>
        <p><?= formatarMoeda($lucroRecebido) ?></p>
    </div>

    <div class="card">
        <h3>Lucro a receber</h3>
        <p><?= formatarMoeda($lucroAReceber) ?></p>
    </div>

    <div class="card">
        <h3>Itens vendidos</h3>
        <p><?= $totalItens ?: 0 ?></p>
    </div>
</div>

<a class="botao" href="registrar.php" title="Registrar nova venda">
    <i class="fa-solid fa-plus"></i>
</a>

<div class="table-responsive">


    <table id="tabelaVendas">
        <thead>
            <tr>
                <th>Data</th>
                <th>Cliente</th>
                <th>Produto</th>
                <th>Pagamento</th>
                <th>Status</th>
                <th>Qtd.</th>
                <th>Custo Unitário</th>
                <th>Venda Unitária</th>
                <th>Total Venda</th>
                <th>Lucro</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($venda = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= date("d/m/Y H:i", strtotime($venda["criado_em"])) ?></td>

                        <td>
                            <?= limparTexto($venda["cliente_nome"] ?: "Não informado") ?>
                        </td>

                        <td>
                            <?= limparTexto($venda["produto_nome"]) ?>
                        </td>

                        <td>
                            <?= limparTexto($venda["forma_pagamento"] ?: "-") ?>
                        </td>

                        <td>
                            <?php if ($venda["status_pagamento"] === "Pago"): ?>
                                <span class="status-pago">Pago</span>
                            <?php else: ?>
                                <span class="status-pendente">Pendente</span>
                            <?php endif; ?>
                        </td>

                        <td><?= $venda["quantidade"] ?></td>
                        <td><?= formatarMoeda($venda["valor_compra"]) ?></td>
                        <td><?= formatarMoeda($venda["valor_venda_unitario"]) ?></td>
                        <td><?= formatarMoeda($venda["valor_total"]) ?></td>
                        <td><?= formatarMoeda($venda["lucro_total"]) ?></td>

                        <td class="acoes">
                            <?php if ($venda["status_pagamento"] === "Pendente"): ?>
                                <button
                                    type="button"
                                    class="botao-icone pago"
                                    title="Marcar como pago"
                                    onclick="confirmarPagamento(<?= $venda['id'] ?>)">
                                    <i class="fa-solid fa-circle-check"></i>
                                </button>
                            <?php endif; ?>

                            <button
                                type="button"
                                class="botao-icone excluir"
                                title="Excluir venda"
                                onclick="confirmarExclusaoVenda(<?= $venda['id'] ?>)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#tabelaVendas').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json'
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [
                [0, 'desc']
            ],
            columnDefs: [{
                orderable: false,
                targets: 10
            }]
        });
    });

    function confirmarExclusaoVenda(id) {
        Swal.fire({
            title: "Excluir venda?",
            text: "A venda será apagada e a quantidade voltará para o estoque.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sim, excluir",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#d33",
            cancelButtonColor: "#8a2a61"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "excluir.php?id=" + id;
            }
        });
    }

    function confirmarPagamento(id) {
        Swal.fire({
            title: "Confirmar pagamento",
            text: "Escolha a forma de pagamento recebida:",
            icon: "question",
            input: "select",
            inputOptions: {
                "Dinheiro": "Dinheiro",
                "Pix": "Pix",
                "Cartão de Débito": "Cartão de Débito",
                "Cartão de Crédito": "Cartão de Crédito"
            },
            inputPlaceholder: "Selecione a forma de pagamento",
            showCancelButton: true,
            confirmButtonText: "Confirmar pagamento",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#198754",
            cancelButtonColor: "#8a2a61",
            inputValidator: (value) => {
                if (!value) {
                    return "Selecione uma forma de pagamento.";
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "marcar_pago.php?id=" + id + "&forma_pagamento=" + encodeURIComponent(result.value);
            }
        });
    }
</script>

<?php require_once "../includes/footer.php"; ?>