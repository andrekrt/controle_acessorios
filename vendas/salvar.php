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
$quantidadeVendida = (int) ($_POST["quantidade"] ?? 0);
$valorVendaUnitario = converterMoedaParaDecimal($_POST["valor_venda_unitario"] ?? "0");

$clienteNome = trim($_POST["cliente_nome"] ?? "");
$formaPagamento = trim($_POST["forma_pagamento"] ?? "");
$statusPagamento = $_POST["status_pagamento"] ?? "Pago";

if ($produtoId <= 0 || $quantidadeVendida <= 0 || $valorVendaUnitario < 0 || empty($formaPagamento)) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Preencha todos os dados da venda corretamente."
    ];

    header("Location: registrar.php");
    exit;
}

if (!in_array($statusPagamento, ["Pago", "Pendente"])) {
    $statusPagamento = "Pago";
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

if ($quantidadeVendida > $produto["quantidade"]) {
    $_SESSION["alerta"] = [
        "tipo" => "warning",
        "titulo" => "Estoque insuficiente!",
        "mensagem" => "A quantidade vendida é maior que o estoque disponível."
    ];

    header("Location: registrar.php");
    exit;
}

$valorTotal = $quantidadeVendida * $valorVendaUnitario;
$lucroTotal = ($valorVendaUnitario - $produto["valor_compra"]) * $quantidadeVendida;

$conn->begin_transaction();

try {
    $sqlVenda = "INSERT INTO vendas (
                    produto_id,
                    cliente_nome,
                    forma_pagamento,
                    status_pagamento,
                    quantidade,
                    valor_venda_unitario,
                    valor_total,
                    lucro_total
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtVenda = $conn->prepare($sqlVenda);
    $stmtVenda->bind_param(
        "isssiddd",
        $produtoId,
        $clienteNome,
        $formaPagamento,
        $statusPagamento,
        $quantidadeVendida,
        $valorVendaUnitario,
        $valorTotal,
        $lucroTotal
    );

    if (!$stmtVenda->execute()) {
        throw new Exception("Erro ao registrar venda.");
    }

    $novoEstoque = $produto["quantidade"] - $quantidadeVendida;

    $sqlAtualizarEstoque = "UPDATE produtos SET quantidade = ? WHERE id = ?";
    $stmtEstoque = $conn->prepare($sqlAtualizarEstoque);
    $stmtEstoque->bind_param("ii", $novoEstoque, $produtoId);

    if (!$stmtEstoque->execute()) {
        throw new Exception("Erro ao atualizar estoque.");
    }

    $conn->commit();

    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Sucesso!",
        "mensagem" => "Venda registrada com sucesso."
    ];

    header("Location: listar.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();

    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Não foi possível registrar a venda."
    ];

    header("Location: registrar.php");
    exit;
}
