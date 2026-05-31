<?php
session_start();

require_once "../config/conexao.php";

function converterMoedaParaDecimal($valor)
{
    $valor = str_replace("R$", "", $valor);
    $valor = trim($valor);
    $valor = str_replace(".", "", $valor);
    $valor = str_replace(",", ".", $valor);

    return (float) $valor;
}

$produtoId = (int) ($_POST["produto_id"] ?? 0);
$quantidade = (int) ($_POST["quantidade"] ?? 0);
$valorCompraUnitario = converterMoedaParaDecimal($_POST["valor_compra_unitario"] ?? "0");
$observacao = trim($_POST["observacao"] ?? "");

if ($produtoId <= 0 || $quantidade <= 0 || $valorCompraUnitario < 0) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Preencha os dados da entrada corretamente."
    ];

    header("Location: registrar.php");
    exit;
}

$sqlProduto = "SELECT * FROM produtos WHERE id = ?";
$stmtProduto = $conn->prepare($sqlProduto);
$stmtProduto->bind_param("i", $produtoId);
$stmtProduto->execute();

$resultadoProduto = $stmtProduto->get_result();
$produto = $resultadoProduto->fetch_assoc();

if (!$produto) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Produto não encontrado."
    ];

    header("Location: registrar.php");
    exit;
}

$conn->begin_transaction();

try {
    $sqlEntrada = "INSERT INTO entradas_estoque (
                    produto_id,
                    quantidade,
                    valor_compra_unitario,
                    observacao
                )
                VALUES (?, ?, ?, ?)";

    $stmtEntrada = $conn->prepare($sqlEntrada);
    $stmtEntrada->bind_param(
        "iids",
        $produtoId,
        $quantidade,
        $valorCompraUnitario,
        $observacao
    );

    if (!$stmtEntrada->execute()) {
        throw new Exception("Erro ao registrar entrada.");
    }

    $novoEstoque = $produto["quantidade"] + $quantidade;

    $sqlAtualizarProduto = "
        UPDATE produtos 
        SET quantidade = ?, valor_compra = ?
        WHERE id = ?
    ";

    $stmtProdutoAtualizar = $conn->prepare($sqlAtualizarProduto);
    $stmtProdutoAtualizar->bind_param(
        "idi",
        $novoEstoque,
        $valorCompraUnitario,
        $produtoId
    );

    if (!$stmtProdutoAtualizar->execute()) {
        throw new Exception("Erro ao atualizar produto.");
    }

    $conn->commit();

    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Sucesso!",
        "mensagem" => "Entrada registrada e estoque atualizado com sucesso."
    ];

    header("Location: listar.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();

    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Não foi possível registrar a entrada."
    ];

    header("Location: registrar.php");
    exit;
}
