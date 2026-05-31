<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/funcoes.php";
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Controle de Bijuterias</title>

    <link rel="stylesheet" href="/TESTE/assets/style.css">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- DataTables CSS -->
    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <!-- Select2 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
        rel="stylesheet" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <!-- Select2 JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js">
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <header class="topo">
        <h1>Controle de Bijuterias</h1>

        <nav>
            <a href="/TESTE/index.php">Início</a>
            <a href="/TESTE/produtos/listar.php">Produtos</a>
            <a href="/TESTE/produtos/cadastrar.php">Cadastrar Produto</a>
            <a href="/TESTE/entradas/registrar.php">Entrada de Estoque</a>
            <a href="/TESTE/entradas/listar.php">Entradas</a>
            <a href="/TESTE/vendas/registrar.php">Registrar Venda</a>
            <a href="/TESTE/vendas/listar.php">Vendas</a>
        </nav>
    </header>

    <main class="container">