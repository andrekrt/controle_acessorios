<?php
session_start();

require_once "../config/conexao.php";

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Produto inválido."
    ];

    header("Location: listar.php");
    exit;
}

$sql = "UPDATE produtos SET ativo = 0 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Sucesso!",
        "mensagem" => "Produto desativado com sucesso."
    ];
} else {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Não foi possível desativar o produto."
    ];
}

header("Location: listar.php");
exit;
