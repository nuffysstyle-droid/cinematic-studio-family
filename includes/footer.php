        </div><!-- .page-body -->
    </main>
</div><!-- .app-layout -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<?php if (!empty($extraJs)): ?>
    <?php foreach ((array) $extraJs as $jsFile): ?>
        <script src="<?= BASE_URL ?>/assets/js/<?= htmlspecialchars($jsFile) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
