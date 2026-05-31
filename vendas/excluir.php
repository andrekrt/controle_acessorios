<?php
session_start();

require_once "../config/conexao.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Venda inválida."
    ];

    header("Location: listar.php");
    exit;
}

$sqlVenda = "SELECT produto_id, quantidade FROM vendas WHERE id = ?";
$stmtVenda = $conn->prepare($sqlVenda);
$stmtVenda->bind_param("i", $id);
$stmtVenda->execute();

$resultadoVenda = $stmtVenda->get_result();

if ($resultadoVenda->num_rows === 0) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Venda não encontrada."
    ];

    header("Location: listar.php");
    exit;
}

$venda = $resultadoVenda->fetch_assoc();

$produto_id = $venda["produto_id"];
$quantidade = $venda["quantidade"];

$conn->begin_transaction();

try {
    $sqlEstoque = "UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?";
    $stmtEstoque = $conn->prepare($sqlEstoque);
    $stmtEstoque->bind_param("ii", $quantidade, $produto_id);
    $stmtEstoque->execute();

    $sqlExcluir = "DELETE FROM vendas WHERE id = ?";
    $stmtExcluir = $conn->prepare($sqlExcluir);
    $stmtExcluir->bind_param("i", $id);
    $stmtExcluir->execute();

    $conn->commit();

    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Sucesso!",
        "mensagem" => "Venda excluída e estoque atualizado com sucesso."
    ];
} catch (Exception $e) {
    $conn->rollback();

    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Não foi possível excluir a venda."
    ];
}

header("Location: listar.php");
exit;
