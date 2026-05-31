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

$id = (int) ($_POST["id"] ?? 0);
$nome = trim($_POST["nome"] ?? "");
$valorCompra = converterMoedaParaDecimal($_POST["valor_compra"] ?? "0");

if ($id <= 0 || empty($nome) || $valorCompra < 0) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Dados inválidos para atualizar o produto."
    ];

    header("Location: listar.php");
    exit;
}

$sql = "UPDATE produtos 
        SET nome = ?, valor_compra = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sdi", $nome, $valorCompra, $id);

if ($stmt->execute()) {
    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Sucesso!",
        "mensagem" => "Produto atualizado com sucesso."
    ];

    header("Location: listar.php");
    exit;
}

$_SESSION["alerta"] = [
    "tipo" => "error",
    "titulo" => "Erro!",
    "mensagem" => "Não foi possível atualizar o produto."
];

header("Location: editar.php?id=" . $id);
exit;
