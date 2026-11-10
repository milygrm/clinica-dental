  </main>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="toastMsg" class="toast align-items-center border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastTexto"></div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
<?php if (!empty($scripts)) foreach ($scripts as $s): ?>
<script src="assets/js/<?= e($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>
