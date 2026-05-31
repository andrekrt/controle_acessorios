<?php
session_start();

require_once "../config/conexao.php";

$id = (int) ($_GET["id"] ?? 0);
$formaPagamento = trim($_GET["forma_pagamento"] ?? "");

$formasPermitidas = [
    "Dinheiro",
    "Pix",
    "Cartão de Débito",
    "Cartão de Crédito"
];

if ($id <= 0 || empty($formaPagamento) || !in_array($formaPagamento, $formasPermitidas)) {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Dados inválidos para confirmar pagamento."
    ];

    header("Location: listar.php");
    exit;
}

$sql = "
    UPDATE vendas 
    SET status_pagamento = 'Pago',
        forma_pagamento = ?
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $formaPagamento, $id);

if ($stmt->execute()) {
    $_SESSION["alerta"] = [
        "tipo" => "success",
        "titulo" => "Pagamento confirmado!",
        "mensagem" => "A venda foi marcada como paga."
    ];
} else {
    $_SESSION["alerta"] = [
        "tipo" => "error",
        "titulo" => "Erro!",
        "mensagem" => "Não foi possível confirmar o pagamento."
    ];
}

header("Location: listar.php");
exit;
