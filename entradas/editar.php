<?php
require_once "../config/conexao.php";
require_once "../includes/header.php";

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    echo "<p>Entrada não encontrada.</p>";
    require_once "../includes/footer.php";
    exit;
}

$sqlEntrada = "
    SELECT 
        entradas_estoque.*,
        produtos.nome AS produto_nome
    FROM entradas_estoque
    INNER JOIN produtos ON produtos.id = entradas_estoque.produto_id
    WHERE entradas_estoque.id = ?
";

$stmtEntrada = $conn->prepare($sqlEntrada);
$stmtEntrada->bind_param("i", $id);
$stmtEntrada->execute();

$resultadoEntrada = $stmtEntrada->get_result();

if ($resultadoEntrada->num_rows === 0) {
    echo "<p>Entrada não encontrada.</p>";
    require_once "../includes/footer.php";
    exit;
}

$entrada = $resultadoEntrada->fetch_assoc();

$sqlProdutos = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC";
$resultadoProdutos = $conn->query($sqlProdutos);
?>

<h2>Editar Entrada de Estoque</h2>

<form action="atualizar.php" method="POST">
    <input type="hidden" name="id" value="<?= $entrada["id"] ?>">

    <label for="produto_id">Produto</label>
    <select id="produto_id" name="produto_id" class="select2-produtos" required>
        <option value="">Selecione um produto</option>

        <?php while ($produto = $resultadoProdutos->fetch_assoc()): ?>
            <option
                value="<?= $produto["id"] ?>"
                <?= $produto["id"] == $entrada["produto_id"] ? "selected" : "" ?>>
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
        value="<?= $entrada["quantidade"] ?>"
        required>

    <label for="valor_compra_unitario">Valor de compra unitário</label>
    <input
        type="text"
        id="valor_compra_unitario"
        name="valor_compra_unitario"
        class="moeda"
        value="<?= number_format($entrada["valor_compra_unitario"], 2, ',', '.') ?>"
        required>

    <label for="observacao">Observação</label>
    <input
        type="text"
        id="observacao"
        name="observacao"
        value="<?= limparTexto($entrada["observacao"] ?? "") ?>">

    <button type="submit">Salvar Alterações</button>
    <a class="botao" href="listar.php">Voltar</a>
</form>

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