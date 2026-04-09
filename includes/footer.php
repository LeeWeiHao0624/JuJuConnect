    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (optional but useful) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js_file): ?>
            <script src="<?php echo $js_file; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

