                </div>
                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                        <div class="mb-2 mb-md-0">
                            <strong>Hajj Registration CRM</strong> © <?php echo date('Y'); ?> Awaisi Tours
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>
    
    <script src="../../template/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../template/assets/vendor/js/bootstrap.js"></script>
    <script src="../../template/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../../template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../template/assets/vendor/js/menu.js"></script>
    <script src="../../template/assets/js/main.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

