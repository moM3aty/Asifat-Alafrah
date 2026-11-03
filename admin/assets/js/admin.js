document.addEventListener('DOMContentLoaded', () => {
  // === 1. Mobile Sidebar ===
  const mobMenu = document.getElementById('mobMenu');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');

  if (mobMenu && sidebar && overlay) {
    mobMenu.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('show');
    });
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  }

  // === 2. SweetAlert2 Defaults ===
  const swalDark = Swal.mixin({
    customClass: {
      popup: 'swal-dark',
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-ghost'
    },
    buttonsStyling: false
  });

  // === 3. Delete Confirmation ===
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      const url = el.getAttribute('href');
      const message = el.dataset.confirm || 'هل أنت متأكد من هذه الخطوة؟';
      
      swalDark.fire({
        title: 'هل أنت متأكد؟',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = url;
        }
      });
    });
  });

  // === 4. Flash Messages Toasts ===
  const flashScript = document.getElementById('flash-data');
  if (flashScript) {
    const flashes = JSON.parse(flashScript.textContent);
    flashes.forEach((f, index) => {
      setTimeout(() => {
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-start',
          showConfirmButton: false,
          timer: 4000,
          timerProgressBar: true,
          customClass: { popup: 'swal-dark' }
        });
        Toast.fire({
          icon: f.type === 'success' ? 'success' : 'error',
          title: f.msg
        });
      }, index * 500); // Delay between multiple toasts
    });
  }
});