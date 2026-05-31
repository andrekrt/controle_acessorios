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

$nome = trim($_POST["nome"] ?? "");
$valorCompra = converterMoedaParaDecimal($_POST["valor_compra"] ?? "0");
$quantidade = (int) ($_POST["quantidade"] ?? 0);

if (empty($nome) || $valorCompra < 0 || $quantidade < 0) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Preencha os dados do produto corretamente."
    ];

    header("Location: cadastrar.php");
    exit;
}

$conn->begin_transaction();

try {
    $sqlProduto = "INSERT INTO produtos (
                    nome,
                    valor_compra,
                    quantidade
                )
                VALUES (?, ?, ?)";

    $stmtProduto = $conn->prepare($sqlProduto);
    $stmtProduto->bind_param(
        "sdi",
        $nome,
        $valorCompra,
        $quantidade
    );

    if (!$stmtProduto->execute()) {
        throw new Exception("Erro ao cadastrar produto.");
    }

    $produtoId = $conn->insert_id;

    if ($quantidade > 0) {
        $observacao = "Entrada inicial criada no cadastro do produto";

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
            $valorCompra,
            $observacao
        );

        if (!$stmtEntrada->execute()) {
            throw new Exception("Erro ao registrar entrada inicial.");
        }
    }

    $conn->commit();

    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Sucesso!",
        "mensagem" => "Produto cadastrado com sucesso."
    ];

    header("Location: listar.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();

    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Não foi possível cadastrar o produto."
    ];

    header("Location: cadastrar.php");
    exit;
}
