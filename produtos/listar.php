<?php
require_once "../config/conexao.php";
require_once "../includes/header.php";

$sql = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC";
$resultado = $conn->query($sql);
?>

<h2>Produtos</h2>

<a class="botao" href="cadastrar.php">
    <i class="fa-solid fa-plus"></i>
</a>

<div class="table-responsive">


    <table id="tabelaProdutos">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Valor de Compra</th>
                <th>Estoque</th>
                <th>Cadastrado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($produto = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= limparTexto($produto["nome"]) ?></td>
                        <td><?= formatarMoeda($produto["valor_compra"]) ?></td>
                        <td><?= $produto["quantidade"] ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($produto["criado_em"])) ?></td>
                        <td class="acoes">
                            <a
                                class="botao-icone editar"
                                href="editar.php?id=<?= $produto["id"] ?>"
                                title="Editar produto">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <button
                                type="button"
                                class="botao-icone excluir"
                                title="Excluir produto"
                                onclick="confirmarDesativacao(<?= $produto['id'] ?>)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nenhum produto cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#tabelaProdutos').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json'
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [
                [0, 'asc']
            ],
            columnDefs: [{
                orderable: false,
                targets: 4
            }]
        });
    });

    function confirmarDesativacao(id) {
        Swal.fire({
            title: "Desativar produto?",
            text: "O produto sairá da listagem, mas o histórico será mantido.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sim, desativar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#d33",
            cancelButtonColor: "#8a2a61"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "desativar.php?id=" + id;
            }
        });
    }
</script>

<?php require_once "../includes/footer.php"; ?>