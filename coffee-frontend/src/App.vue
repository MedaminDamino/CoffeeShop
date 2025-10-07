<script setup lang="ts">
import { RouterView } from 'vue-router'
import { onMounted } from 'vue'
import AuthModal from '@/components/AuthModal.vue'
import { Modal } from 'bootstrap'

onMounted(() => {
  window.addEventListener('openAuthModal', () => {
    const modal = new Modal(document.getElementById('authModal')!)
    modal.show()
  })
})
</script>

<template>
  <div id="app">
    <RouterView />

    <!-- Auth Modal -->
    <AuthModal />

    <!-- Floating Chat Widget for Mobile -->
    <div class="d-md-none">
      <button class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-3 p-3 shadow-lg" style="z-index: 1050;">
        <i class="bi bi-chat-dots fs-4"></i>
      </button>
    </div>
  </div>
</template>

<style>
/* Global font family */
* {
  font-family: 'Poppins', sans-serif;
}

/* Button hover effects */
.btn {
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Responsive adjustments */
@media (max-width: 991.98px) {
  .navbar-nav {
    text-align: center;
    margin-top: 1rem;
  }

  .navbar-nav .nav-item {
    margin-bottom: 0.5rem;
  }
}
</style>
