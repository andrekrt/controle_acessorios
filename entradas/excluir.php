<?php
session_start();

require_once "../config/conexao.php";

function atualizarCustoRecenteProduto($conn, $produtoId)
{
    $sql = "
        SELECT valor_compra_unitario 
        FROM entradas_estoque 
        WHERE produto_id = ? 
        ORDER BY criado_em DESC, id DESC 
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $produtoId);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $entrada = $resultado->fetch_assoc();

    $novoCusto = $entrada ? $entrada["valor_compra_unitario"] : 0;

    $sqlAtualizar = "UPDATE produtos SET valor_compra = ? WHERE id = ?";
    $stmtAtualizar = $conn->prepare($sqlAtualizar);
    $stmtAtualizar->bind_param("di", $novoCusto, $produtoId);
    $stmtAtualizar->execute();
}

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Entrada inválida."
    ];

    header("Location: listar.php");
    exit;
}

$sqlEntrada = "SELECT * FROM entradas_estoque WHERE id = ?";
$stmtEntrada = $conn->prepare($sqlEntrada);
$stmtEntrada->bind_param("i", $id);
$stmtEntrada->execute();

$resultadoEntrada = $stmtEntrada->get_result();
$entrada = $resultadoEntrada->fetch_assoc();

if (!$entrada) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Entrada não encontrada."
    ];

    header("Location: listar.php");
    exit;
}

$produtoId = (int) $entrada["produto_id"];
$quantidadeEntrada = (int) $entrada["quantidade"];

$conn->begin_transaction();

try {
    $sqlProduto = "SELECT quantidade FROM produtos WHERE id = ?";
    $stmtProduto = $conn->prepare($sqlProduto);
    $stmtProduto->bind_param("i", $produtoId);
    $stmtProduto->execute();

    $produto = $stmtProduto->get_result()->fetch_assoc();

    if (!$produto) {
        throw new Exception("Produto não encontrado.");
    }

    $novoEstoque = $produto["quantidade"] - $quantidadeEntrada;

    if ($novoEstoque < 0) {
        throw new Exception("Não é possível excluir esta entrada, pois o estoque ficaria negativo.");
    }

    $sqlAtualizarEstoque = "UPDATE produtos SET quantidade = ? WHERE id = ?";
    $stmtAtualizarEstoque = $conn->prepare($sqlAtualizarEstoque);
    $stmtAtualizarEstoque->bind_param("ii", $novoEstoque, $produtoId);
    $stmtAtualizarEstoque->execute();

    $sqlExcluir = "DELETE FROM entradas_estoque WHERE id = ?";
    $stmtExcluir = $conn->prepare($sqlExcluir);
    $stmtExcluir->bind_param("i", $id);

    if (!$stmtExcluir->execute()) {
        throw new Exception("Erro ao excluir entrada.");
    }

    atualizarCustoRecenteProduto($conn, $produtoId);

    $conn->commit();

    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Sucesso!",
        "mensagem" => "Entrada excluída e estoque ajustado com sucesso."
    ];
} catch (Exception $e) {
    $conn->rollback();

    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => $e->getMessage()
    ];
}

header("Location: listar.php");
exit;
