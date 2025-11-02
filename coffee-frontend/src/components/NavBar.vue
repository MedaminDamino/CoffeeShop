
<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container">
      <router-link class="navbar-brand fw-bold" to="/">
        RACHFA
      </router-link>
      
      <!-- Mobile cart and toggle button wrapper -->
      <div class="d-flex align-items-center gap-3 d-lg-none">
        <router-link to="/checkout" class="cart-icon-link">
          <i class="bi bi-cart-fill fs-5"></i>
          <span v-if="cartItemCount > 0" class="cart-counter">{{ cartItemCount }}</span>
        </router-link>
        <button 
          class="navbar-toggler border-0" 
          type="button" 
          data-bs-toggle="collapse" 
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto gap-lg-4">
          <li class="nav-item">
            <router-link class="nav-link" to="/" @click="closeMenu">Home</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/reserve" @click="closeMenu">Reservation</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/menu" @click="closeMenu">Menu</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/about" @click="closeMenu">About</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/contact" @click="closeMenu">Contact</router-link>
          </li>
        </ul>
        
        <!-- Desktop actions -->
        <div class="d-none d-lg-flex gap-2 align-items-center">
          <button v-if="isLoggedIn && isAdmin" class="btn btn-outline-primary btn-sm" @click="goToAdmin">
            Admin Panel
          </button>
          <button v-if="!isLoggedIn" class="btn btn-outline-secondary btn-sm" @click="openLoginModal">
            Login
          </button>
          <button v-if="isLoggedIn" class="btn btn-outline-secondary btn-sm" @click="logout">
            Logout
          </button>
          <router-link to="/checkout" class="cart-icon-link">
            <i class="bi bi-cart-fill fs-5"></i>
            <span v-if="cartItemCount > 0" class="cart-counter">{{ cartItemCount }}</span>
          </router-link>
        </div>

        <!-- Mobile actions -->
        <div class="d-lg-none mt-3 pb-3 mobile-actions">
          <div class="d-flex flex-column gap-2">
            <button v-if="isLoggedIn && isAdmin" class="btn btn-outline-primary w-100" @click="goToAdmin">
              <i class="bi bi-speedometer2 me-2"></i>Admin Panel
            </button>
            <button v-if="!isLoggedIn" class="btn btn-outline-secondary w-100" @click="openLoginModal">
              <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
            <button v-if="isLoggedIn" class="btn btn-outline-secondary w-100" @click="logout">
              <i class="bi bi-box-arrow-right me-2"></i>Logout
            </button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()

const isLoggedIn = computed(() => authStore.isAuthenticated)
const isAdmin = computed(() => authStore.user?.role === 'admin' || authStore.user?.role === 'super_admin')
const cartItemCount = computed(() => cartStore.totalItems)

const closeMenu = () => {
  const navbarCollapse = document.getElementById('navbarNav')
  if (navbarCollapse?.classList.contains('show')) {
    navbarCollapse.classList.remove('show')
  }
}



const openLoginModal = () => {
  closeMenu()
  window.dispatchEvent(new CustomEvent('openAuthModal'))
}



import { nextTick } from 'vue'

const goToAdmin = async () => {
  closeMenu()
  await nextTick()
  router.push('/admin')
}

const logout = async () => {
  closeMenu()
  await nextTick()
  await authStore.logout()
  router.push('/')
}

</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.navbar {
  padding: 0.75rem 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  background-color: #ffffff !important;
}

.navbar-brand {
  font-size: 1.5rem;
  letter-spacing: 0.05em;
  color: #2c2c2c !important;
  text-decoration: none;
  font-weight: 700;
}

.nav-link {
  position: relative;
  color: #4a4a4a !important;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.3s ease;
  padding: 0.5rem 0;
}

.nav-link::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0%;
  height: 2px;
  background-color: #8C6353; 
  transition: width 0.3s ease-in-out;
}

.nav-link:hover::after,
.nav-link.router-link-active::after {
  width: 100%;
}

.btn {
  border-radius: 0.375rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-outline-primary {
  border-color: #8C6353;
  color: #8C6353;
}

.btn-outline-primary:hover {
  background-color: #8C6353;
  border-color: #8C6353;
  color: white;
}

.btn-outline-secondary {
  border-color: #6c757d;
  color: #6c757d;
}

.btn-outline-secondary:hover {
  background-color: #6c757d;
  border-color: #6c757d;
  color: white;
}

.cart-icon-link {
  color: #4a4a4a !important;
  text-decoration: none;
  transition: color 0.3s ease;
  position: relative;
  display: inline-block;
  padding: 0.25rem;
}

.cart-icon-link:hover {
  color: #8C6353 !important;
}

.cart-icon-link .bi-cart-fill {
  transition: transform 0.3s ease;
}

.cart-icon-link:hover .bi-cart-fill {
  transform: scale(1.1);
}

.cart-counter {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #8C6353;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: bold;
  border: 2px solid #FFFFFF;
}

.navbar-toggler {
  padding: 0.25rem 0.5rem;
  font-size: 1.25rem;
}

.navbar-toggler:focus {
  box-shadow: none;
}

/* Mobile specific styles */
@media (max-width: 991.98px) {
  .navbar-collapse {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
  }

  .navbar-nav {
    gap: 0 !important;
  }

  .nav-item {
    padding: 0.25rem 0;
  }

  .nav-link {
    padding: 0.75rem 0;
    font-size: 1.05rem;
  }

  .mobile-actions {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
  }

  .mobile-actions .btn {
    padding: 0.75rem;
    font-size: 1rem;
  }
}

/* Smooth collapse animation */
.navbar-collapse {
  transition: height 0.35s ease;
}

/* Active link styling */
.router-link-active {
  color: #8C6353 !important;
  font-weight: 600;
}
</style>