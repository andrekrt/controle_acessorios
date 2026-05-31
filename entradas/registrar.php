<?php
require_once "../config/conexao.php";
require_once "../includes/header.php";

$sql = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC";
$resultado = $conn->query($sql);
?>

<h2>Registrar Entrada de Estoque</h2>

<form action="salvar.php" method="POST">
    <label for="produto_id">Produto</label>
    <select id="produto_id" name="produto_id" class="select2-produtos" required>
        <option value="">Selecione um produto</option>

        <?php while ($produto = $resultado->fetch_assoc()): ?>
            <option value="<?= $produto["id"] ?>">
                <?= limparTexto($produto["nome"]) ?> -
                Estoque atual: <?= $produto["quantidade"] ?> -
                Custo atual: <?= formatarMoeda($produto["valor_compra"]) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="quantidade">Quantidade de entrada</label>
    <input
        type="number"
        id="quantidade"
        name="quantidade"
        min="1"
        required>

    <label for="valor_compra_unitario">Valor de compra unitário</label>
    <input
        type="text"
        id="valor_compra_unitario"
        name="valor_compra_unitario"
        class="moeda"
        placeholder="R$ 0,00"
        required>

    <label for="observacao">Observação</label>
    <input
        type="text"
        id="observacao"
        name="observacao"
        placeholder="Ex: compra feira, fornecedor, reposição...">

    <button type="submit">Registrar Entrada</button>
</form>

<br>

<a class="botao" href="listar.php">Ver Entradas</a>
<a class="botao" href="../produtos/listar.php">Voltar para Produtos</a>

<script src="../assets/script.js"></script>

<script>
    $(document).ready(function() {
        $('.select2-produtos').select2({
            placeholder: 'Selecione ou pesquise um produto',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return 'Nenhum produto encontrado';
                },
                searching: function() {
                    return 'Pesquisando...';
                }
            }
        });
    });
</script>

<?php require_once "../includes/footer.php"; ?>