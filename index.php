<?php
require_once "config/conexao.php";
require_once "includes/header.php";

$sqlProdutos = "SELECT COUNT(*) AS total_produtos FROM produtos WHERE ativo = 1";
$totalProdutos = $conn->query($sqlProdutos)->fetch_assoc()["total_produtos"] ?? 0;

$sqlEstoque = "SELECT SUM(quantidade) AS total_estoque FROM produtos WHERE ativo = 1";
$totalEstoque = $conn->query($sqlEstoque)->fetch_assoc()["total_estoque"] ?? 0;

$sqlVendas = "
    SELECT 
        SUM(valor_total) AS total_vendido,

        SUM(CASE 
            WHEN status_pagamento = 'Pago' 
            THEN valor_total 
            ELSE 0 
        END) AS total_recebido,

        SUM(CASE 
            WHEN status_pagamento = 'Pendente' 
            THEN valor_total 
            ELSE 0 
        END) AS total_a_receber,

        SUM(CASE 
            WHEN status_pagamento = 'Pago' 
            THEN lucro_total 
            ELSE 0 
        END) AS lucro_recebido,

        SUM(CASE 
            WHEN status_pagamento = 'Pendente' 
            THEN lucro_total 
            ELSE 0 
        END) AS lucro_a_receber

    FROM vendas
";

$resumoVendas = $conn->query($sqlVendas)->fetch_assoc();

$totalVendido = $resumoVendas["total_vendido"] ?? 0;
$totalRecebido = $resumoVendas["total_recebido"] ?? 0;
$totalAReceber = $resumoVendas["total_a_receber"] ?? 0;
$lucroRecebido = $resumoVendas["lucro_recebido"] ?? 0;
$lucroAReceber = $resumoVendas["lucro_a_receber"] ?? 0;
?>

<h2>Painel Inicial</h2>

<div class="cards">
    <div class="card">
        <h3>Produtos ativos</h3>
        <p><?= $totalProdutos ?></p>
    </div>

    <div class="card">
        <h3>Itens em estoque</h3>
        <p><?= $totalEstoque ?: 0 ?></p>
    </div>

    <div class="card">
        <h3>Total vendido</h3>
        <p><?= formatarMoeda($totalVendido) ?></p>
    </div>

    <div class="card">
        <h3>Total recebido</h3>
        <p><?= formatarMoeda($totalRecebido) ?></p>
    </div>

    <div class="card">
        <h3>Valores a receber</h3>
        <p><?= formatarMoeda($totalAReceber) ?></p>
    </div>

    <div class="card">
        <h3>Lucro recebido</h3>
        <p><?= formatarMoeda($lucroRecebido) ?></p>
    </div>

    <div class="card">
        <h3>Lucro a receber</h3>
        <p><?= formatarMoeda($lucroAReceber) ?></p>
    </div>
</div>

<a class="botao" href="produtos/listar.php">
    <i class="fa-solid fa-box"></i> Ver Produtos
</a>

<a class="botao" href="produtos/cadastrar.php">
    <i class="fa-solid fa-plus"></i> Cadastrar Produto
</a>

<a class="botao" href="entradas/registrar.php">
    <i class="fa-solid fa-arrow-down"></i> Entrada de Estoque
</a>

<a class="botao" href="vendas/registrar.php">
    <i class="fa-solid fa-cart-shopping"></i> Registrar Venda
</a>

<?php require_once "includes/footer.php"; ?>