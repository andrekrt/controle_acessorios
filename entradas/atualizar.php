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

$id = (int) ($_POST["id"] ?? 0);
$novoProdutoId = (int) ($_POST["produto_id"] ?? 0);
$novaQuantidade = (int) ($_POST["quantidade"] ?? 0);
$novoValorCompra = converterMoedaParaDecimal($_POST["valor_compra_unitario"] ?? "0");
$observacao = trim($_POST["observacao"] ?? "");

if ($id <= 0 || $novoProdutoId <= 0 || $novaQuantidade <= 0 || $novoValorCompra < 0) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Dados inválidos para atualizar a entrada."
    ];

    header("Location: listar.php");
    exit;
}

$sqlEntradaAntiga = "SELECT * FROM entradas_estoque WHERE id = ?";
$stmtEntradaAntiga = $conn->prepare($sqlEntradaAntiga);
$stmtEntradaAntiga->bind_param("i", $id);
$stmtEntradaAntiga->execute();

$resultadoEntradaAntiga = $stmtEntradaAntiga->get_result();
$entradaAntiga = $resultadoEntradaAntiga->fetch_assoc();

if (!$entradaAntiga) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Entrada não encontrada."
    ];

    header("Location: listar.php");
    exit;
}

$produtoAntigoId = (int) $entradaAntiga["produto_id"];
$quantidadeAntiga = (int) $entradaAntiga["quantidade"];

$conn->begin_transaction();

try {
    if ($produtoAntigoId === $novoProdutoId) {
        $diferenca = $novaQuantidade - $quantidadeAntiga;

        $sqlProduto = "SELECT quantidade FROM produtos WHERE id = ?";
        $stmtProduto = $conn->prepare($sqlProduto);
        $stmtProduto->bind_param("i", $novoProdutoId);
        $stmtProduto->execute();

        $produto = $stmtProduto->get_result()->fetch_assoc();

        if (!$produto) {
            throw new Exception("Produto não encontrado.");
        }

        $novoEstoque = $produto["quantidade"] + $diferenca;

        if ($novoEstoque < 0) {
            throw new Exception("A alteração deixaria o estoque negativo.");
        }

        $sqlAtualizarEstoque = "UPDATE produtos SET quantidade = ? WHERE id = ?";
        $stmtAtualizarEstoque = $conn->prepare($sqlAtualizarEstoque);
        $stmtAtualizarEstoque->bind_param("ii", $novoEstoque, $novoProdutoId);
        $stmtAtualizarEstoque->execute();
    } else {
        $sqlProdutoAntigo = "SELECT quantidade FROM produtos WHERE id = ?";
        $stmtProdutoAntigo = $conn->prepare($sqlProdutoAntigo);
        $stmtProdutoAntigo->bind_param("i", $produtoAntigoId);
        $stmtProdutoAntigo->execute();

        $produtoAntigo = $stmtProdutoAntigo->get_result()->fetch_assoc();

        if (!$produtoAntigo) {
            throw new Exception("Produto antigo não encontrado.");
        }

        $novoEstoqueAntigo = $produtoAntigo["quantidade"] - $quantidadeAntiga;

        if ($novoEstoqueAntigo < 0) {
            throw new Exception("A alteração deixaria o estoque do produto antigo negativo.");
        }

        $sqlBaixarAntigo = "UPDATE produtos SET quantidade = ? WHERE id = ?";
        $stmtBaixarAntigo = $conn->prepare($sqlBaixarAntigo);
        $stmtBaixarAntigo->bind_param("ii", $novoEstoqueAntigo, $produtoAntigoId);
        $stmtBaixarAntigo->execute();

        $sqlSomarNovo = "UPDATE produtos SET quantidade = quantidade + ? WHERE id = ?";
        $stmtSomarNovo = $conn->prepare($sqlSomarNovo);
        $stmtSomarNovo->bind_param("ii", $novaQuantidade, $novoProdutoId);
        $stmtSomarNovo->execute();
    }

    $sqlAtualizarEntrada = "
        UPDATE entradas_estoque 
        SET produto_id = ?, quantidade = ?, valor_compra_unitario = ?, observacao = ?
        WHERE id = ?
    ";

    $stmtAtualizarEntrada = $conn->prepare($sqlAtualizarEntrada);
    $stmtAtualizarEntrada->bind_param(
        "iidsi",
        $novoProdutoId,
        $novaQuantidade,
        $novoValorCompra,
        $observacao,
        $id
    );

    if (!$stmtAtualizarEntrada->execute()) {
        throw new Exception("Erro ao atualizar entrada.");
    }

    atualizarCustoRecenteProduto($conn, $produtoAntigoId);

    if ($produtoAntigoId !== $novoProdutoId) {
        atualizarCustoRecenteProduto($conn, $novoProdutoId);
    }

    $conn->commit();

    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Sucesso!",
        "mensagem" => "Entrada atualizada e estoque ajustado com sucesso."
    ];

    header("Location: listar.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();

    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => $e->getMessage()
    ];

    header("Location: editar.php?id=" . $id);
    exit;
}
