<?php
require_once "../config/conexao.php";
require_once "../includes/header.php";

$sql = "
    SELECT 
        entradas_estoque.id,
        entradas_estoque.quantidade,
        entradas_estoque.valor_compra_unitario,
        entradas_estoque.observacao,
        entradas_estoque.criado_em,
        produtos.nome AS produto_nome
    FROM entradas_estoque
    INNER JOIN produtos ON produtos.id = entradas_estoque.produto_id
    ORDER BY entradas_estoque.criado_em DESC
";

$resultado = $conn->query($sql);

$sqlResumo = "
    SELECT 
        SUM(quantidade) AS total_itens,
        SUM(quantidade * valor_compra_unitario) AS total_investido
    FROM entradas_estoque
";

$resumo = $conn->query($sqlResumo)->fetch_assoc();

$totalItens = $resumo["total_itens"] ?? 0;
$totalInvestido = $resumo["total_investido"] ?? 0;
?>

<h2>Entradas de Estoque</h2>

<div class="cards">
    <div class="card">
        <h3>Total de itens adicionados</h3>
        <p><?= $totalItens ?: 0 ?></p>
    </div>

    <div class="card">
        <h3>Total investido</h3>
        <p><?= formatarMoeda($totalInvestido) ?></p>
    </div>
</div>

<a class="botao" href="registrar.php" title="Registrar entrada">
    <i class="fa-solid fa-plus"></i>
</a>

<div class="table-responsive">
    <table id="tabelaEntradas">
        <thead>
            <tr>
                <th>Data</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Valor Compra Unitário</th>
                <th>Total Entrada</th>
                <th>Observação</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($entrada = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= date("d/m/Y H:i", strtotime($entrada["criado_em"])) ?></td>
                        <td><?= limparTexto($entrada["produto_nome"]) ?></td>
                        <td><?= $entrada["quantidade"] ?></td>
                        <td><?= formatarMoeda($entrada["valor_compra_unitario"]) ?></td>
                        <td><?= formatarMoeda($entrada["quantidade"] * $entrada["valor_compra_unitario"]) ?></td>
                        <td><?= limparTexto($entrada["observacao"] ?: "-") ?></td>
                        <td class="acoes">
                            <a
                                class="botao-icone editar"
                                href="editar.php?id=<?= $entrada["id"] ?>"
                                title="Editar entrada">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <button
                                type="button"
                                class="botao-icone excluir"
                                title="Excluir entrada"
                                onclick="confirmarExclusaoEntrada(<?= $entrada['id'] ?>)">
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
        $('#tabelaEntradas').DataTable({
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
                targets: 6
            }]
        });
    });

    function confirmarExclusaoEntrada(id) {
        Swal.fire({
            title: "Excluir entrada?",
            text: "A entrada será removida e a quantidade será retirada do estoque.",
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
</script>

<?php require_once "../includes/footer.php"; ?>