<?php
require_once "../config/conexao.php";
require_once "../includes/header.php";
?>

<h2>Cadastrar Produto</h2>

<form action="salvar.php" method="POST">
    <label for="nome">Nome do Produto</label>
    <input
        type="text"
        id="nome"
        name="nome"
        required>

    <label for="valor_compra">Valor de compra inicial</label>
    <input
        type="text"
        id="valor_compra"
        name="valor_compra"
        class="moeda"
        placeholder="R$ 0,00"
        required>

    <label for="quantidade">Quantidade inicial em estoque</label>
    <input
        type="number"
        id="quantidade"
        name="quantidade"
        min="0"
        value="0"
        required>

    <button type="submit">Cadastrar Produto</button>
</form>

<br>

<a class="botao" href="listar.php">Voltar</a>

<script src="<?= APP_BASE_URL ?>assets/script.js"></script>

<?php require_once "../includes/footer.php"; ?>