</main>

<footer class="rodape">
    <p>Sistema simples de controle de estoque e vendas</p>
</footer>

<?php if (isset($_SESSION["alerta"])): ?>
    <script>
        Swal.fire({
            icon: "<?= $_SESSION["alerta"]["tipo"] ?>",
            title: "<?= $_SESSION["alerta"]["titulo"] ?>",
            text: "<?= $_SESSION["alerta"]["mensagem"] ?>",
            confirmButtonText: "OK"
        });
    </script>

    <?php unset($_SESSION["alerta"]); ?>
<?php endif; ?>

</body>

</html>