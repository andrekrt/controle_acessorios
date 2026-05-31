<?php
require_once "../config/conexao.php";
require_once "../includes/header.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    echo "<p>Produto não encontrado.</p>";
    require_once "../includes/footer.php";
    exit;
}

$sql = "SELECT * FROM produtos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "<p>Produto não encontrado.</p>";
    require_once "../includes/footer.php";
    exit;
}

$produto = $resultado->fetch_assoc();
?>

<h2>Editar Produto</h2>

<form action="atualizar.php" method="POST">
    <input type="hidden" name="id" value="<?= $produto["id"] ?>">

    <label for="nome">Nome do Produto</label>
    <input
        type="text"
        id="nome"
        name="nome"
        value="<?= limparTexto($produto["nome"]) ?>"
        required>

    <label for="valor_compra">Valor de compra atual</label>
    <input
        type="text"
        id="valor_compra"
        name="valor_compra"
        class="moeda"
        value="<?= number_format($produto["valor_compra"], 2, ',', '.') ?>"
        required>

    <label>Estoque atual</label>
    <input
        type="number"
        value="<?= $produto["quantidade"] ?>"
        disabled>

    <small>
        Para alterar o estoque, use a tela de Entrada de Estoque.
    </small>

    <button type="submit">Salvar Alterações</button>
    <a class="botao" href="listar.php">Voltar</a>
</form>

<script src="<?= APP_BASE_URL ?>assets/script.js"></script>

<?php require_once "../includes/footer.php"; ?>