<?php

function formatarMoeda($valor)
{
    return "R$ " . number_format($valor, 2, ",", ".");
}

function limparTexto($texto)
{
    return htmlspecialchars(trim($texto), ENT_QUOTES, "UTF-8");
}
