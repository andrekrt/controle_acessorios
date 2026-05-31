<?php
session_start();

require_once "../config/conexao.php";

$id = $_GET["id"] ?? null;

if (!$id) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Produto inválido."
    ];

    header("Location: listar.php");
    exit;
}

$sql = "DELETE FROM produtos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Sucesso!",
        "mensagem" => "Produto excluído com sucesso."
    ];
} else {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Não foi possível excluir o produto."
    ];
}

header("Location: listar.php");
exit;
