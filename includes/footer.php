        </div><!-- .page-body -->
    </main>
</div><!-- .app-layout -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<?php if (!empty($extraJs)): ?>
    <script src="<?= BASE_URL ?>/assets/js/<?= htmlspecialchars($extraJs) ?>"></script>
<?php endif; ?>
</body>
</html>
