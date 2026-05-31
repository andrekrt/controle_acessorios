<?php
require_once "../config/conexao.php";
require_once "../includes/header.php";

$sql = "SELECT * FROM produtos WHERE ativo = 1 AND quantidade > 0 ORDER BY nome ASC";
$resultado = $conn->query($sql);
?>

<h2>Registrar Venda</h2>

<form action="salvar.php" method="POST">
    <label for="cliente_nome">Nome do Cliente</label>
    <input
        type="text"
        id="cliente_nome"
        name="cliente_nome"
        placeholder="Ex: Maria Silva">

    <label for="produto_id">Produto</label>
    <select id="produto_id" name="produto_id" class="select2-produtos" required>
        <option value="">Selecione um produto</option>

        <?php while ($produto = $resultado->fetch_assoc()): ?>
            <option value="<?= $produto["id"] ?>">
                <?= limparTexto($produto["nome"]) ?> -
                Estoque: <?= $produto["quantidade"] ?> -
                Custo: <?= formatarMoeda($produto["valor_compra"]) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="quantidade">Quantidade vendida</label>
    <input type="number" id="quantidade" name="quantidade" min="1" required>

    <label for="valor_venda_unitario">Valor de venda unitário</label>
    <input
        type="text"
        id="valor_venda_unitario"
        name="valor_venda_unitario"
        class="moeda"
        placeholder="R$ 0,00"
        required>

    <label for="forma_pagamento">Forma de Pagamento</label>
    <select id="forma_pagamento" name="forma_pagamento" required>
        <option value="">Selecione</option>
        <option value="Dinheiro">Dinheiro</option>
        <option value="Pix">Pix</option>
        <option value="Cartão de Débito">Cartão de Débito</option>
        <option value="Cartão de Crédito">Cartão de Crédito</option>
        <option value="Fiado">Fiado</option>
    </select>

    <label for="status_pagamento">Status do Pagamento</label>
    <select id="status_pagamento" name="status_pagamento" required>
        <option value="Pago">Pago</option>
        <option value="Pendente">Pendente</option>
    </select>

    <button type="submit">Registrar Venda</button>
</form>

<br>

<a class="botao" href="../index.php">Voltar</a>

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