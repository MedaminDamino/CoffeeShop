<script setup lang="ts">
import { RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AuthModal from '@/components/AuthModal.vue'

const authStore = useAuthStore()
const router = useRouter()

const logout = async () => {
  await authStore.logout()
  router.push('/')
}
</script>

<template>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #2C2C2C;">
      <div class="container-fluid px-4">
        <RouterLink class="navbar-brand fw-bold fs-4 d-flex align-items-center" to="/">
          <span class="me-2">☕</span>
          <span>Coffee Shop</span>
        </RouterLink>
        <button
          class="navbar-toggler border-0"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <!-- Centered navigation links -->
          <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
            <li class="nav-item mx-2">
              <RouterLink class="nav-link px-3 py-2 rounded-pill" active-class="active" to="/">Home</RouterLink>
            </li>
            <li class="nav-item mx-2">
              <RouterLink class="nav-link px-3 py-2 rounded-pill" active-class="active" to="/about">About</RouterLink>
            </li>
            <li class="nav-item mx-2">
              <RouterLink class="nav-link px-3 py-2 rounded-pill" active-class="active" to="/services">Services</RouterLink>
            </li>
          </ul>
          <!-- Login button on the right -->
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li v-if="authStore.isAuthenticated && (authStore.user?.role === 'admin' || authStore.user?.role === 'super_admin')" class="nav-item me-3">
              <RouterLink class="nav-link px-3 py-2 rounded-pill" active-class="active" to="/admin">Admin Panel</RouterLink>
            </li>
            <li class="nav-item">
              <button
                v-if="!authStore.isAuthenticated"
                class="btn btn-light fw-semibold px-4 py-2 rounded-pill"
                data-bs-toggle="modal"
                data-bs-target="#authModal"
              >
                Login
              </button>
              <div v-else class="dropdown">
                <button
                  class="btn btn-light dropdown-toggle fw-semibold px-4 py-2 rounded-pill"
                  type="button"
                  id="userDropdown"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
                  {{ authStore.user?.username }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                  <li><button class="dropdown-item py-2" @click="logout">Logout</button></li>
                </ul>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="container py-4">
      <RouterView />
    </main>

    <footer class="bg-light border-top py-3 mt-auto">
      <div class="container small text-muted">
        © {{ new Date().getFullYear() }} Coffee Shop. All rights reserved.
      </div>
    </footer>

    <!-- Auth Modal -->
    <AuthModal />
  </div>
</template>

<style>
/* Global font family */
* {
  font-family: 'Poppins', sans-serif;
}

/* Navbar custom styles */
.navbar-brand {
  transition: transform 0.2s ease;
}

.navbar-brand:hover {
  transform: scale(1.05);
}

.nav-link {
  transition: all 0.3s ease;
  position: relative;
}

.nav-link:hover {
  background-color: #404040;
  transform: translateY(-1px);
}

.nav-link.active {
  background-color: #4A4A4A;
  font-weight: 500;
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
