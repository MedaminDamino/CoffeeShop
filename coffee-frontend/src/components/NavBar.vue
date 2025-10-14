<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container">
      <router-link class="navbar-brand fw-bold" to="/">
        RACHFA
      </router-link>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto gap-4">
          <li class="nav-item">
            <router-link class="nav-link" to="/">Home</router-link>
          </li>
           <li class="nav-item">
            <router-link class="nav-link" to="/reserve">Reservation</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/menu">Menu</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/about">About</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/contact">Contact</router-link>
          </li>
        </ul>
        <div class="d-flex gap-2">
          <button v-if="isLoggedIn" class="btn btn-outline-primary btn-sm" @click="goToAdmin">
            Admin Panel
          </button>
          <button v-if="!isLoggedIn" class="btn btn-outline-secondary btn-sm" @click="openLoginModal">
            Login
          </button>
          <button v-if="isLoggedIn" class="btn btn-outline-secondary btn-sm" @click="logout">
            Logout
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const isLoggedIn = computed(() => authStore.isAuthenticated)

const goToAdmin = () => {
  router.push('/admin')
}

const openLoginModal = () => {
  // Emit event to open login modal
  window.dispatchEvent(new CustomEvent('openAuthModal'))
}

const logout = async () => {
  await authStore.logout()
  router.push('/');
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');



.navbar-brand {
  font-size: 1.5rem;
  letter-spacing: 0.05em;
  color: #2c2c2c !important;
  text-decoration: none;
}

.nav-link {
  position: relative;
  color: #4a4a4a !important;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.3s ease;
}

.nav-link::after {
  content: "";
  position: absolute;
  bottom: -4px;
  left: 0;
  width: 0%;
  height: 2px;
  background-color: #8C6353; 
  transition: width 0.3s ease-in-out;
}

.nav-link:hover::after {
  width: 100%;
}

/* Optional: remove color change completely */
.nav-link:hover {
  color: #4a4a4a !important; /* keep original color */
}

.btn {
  border-radius: 0.375rem;
}
</style>