<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const sidebarOpen = ref(false)
const hideMenuButton = ref(false)
let lastScrollY = 0

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

function closeSidebar() {
  sidebarOpen.value = false
}

function handleScroll() {
  const currentScrollY = window.scrollY
  // Hide button when scrolling down, show when scrolling up
  hideMenuButton.value = currentScrollY > lastScrollY && currentScrollY > 100
  lastScrollY = currentScrollY
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <NavBar />

  <div class="container-fluid admin-panel">
    <div class="row">
        <!-- Mobile Menu Button -->
        <button 
          class="mobile-menu-btn" 
          :class="{ open: sidebarOpen }"
          @click="toggleSidebar" 
          aria-label="Toggle menu"
        >
        <i class="bi" :class="sidebarOpen ? 'bi-arrow-left' : 'bi-arrow-right'"></i>
        </button>

        <!-- Overlay for mobile -->
        <div 
          class="sidebar-overlay" 
          :class="{ active: sidebarOpen }" 
          @click="closeSidebar"
        ></div>

        <!-- Sidebar -->
        <aside class="col-12 col-md-3 col-lg-2 sidebar d-flex flex-column p-0" :class="{ active: sidebarOpen }">
          <div class="sidebar-header text-center py-4">
            <h5 class="fw-bold mb-0">☕ Coffee Admin</h5>
          </div>

        <nav class="list-group list-group-flush flex-grow-1">
          <RouterLink class="list-group-item" to="/admin/users" @click="closeSidebar">
            <i class="bi bi-people me-2"></i> 
            <span>Users</span>
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/branches" @click="closeSidebar">
            <i class="bi bi-shop me-2"></i> 
            <span>Branches</span>
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/tables" @click="closeSidebar">
            <i class="bi bi-grid-3x3-gap me-2"></i> 
            <span>Tables</span>
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/categories" @click="closeSidebar">
            <i class="bi bi-tags me-2"></i> 
            <span>Categories</span>
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/products" @click="closeSidebar">
            <i class="bi bi-cup-hot me-2"></i> 
            <span>Products</span>
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/reservations" @click="closeSidebar">
            <i class="bi bi-calendar-check me-2"></i> 
            <span>Reservations</span>
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/orders" @click="closeSidebar">
            <i class="bi bi-receipt me-2"></i> 
            <span>Orders</span>
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/promotions" @click="closeSidebar">
            <i class="bi bi-megaphone me-2"></i> 
            <span>Promotions</span>
          </RouterLink>
        </nav>
      </aside>

      <!-- Main content -->
      <section class="col-12 col-md-9 col-lg-10 content-area">
        <RouterView />
      </section>
    </div>
  </div>

  <AppFooter />
</template>

<style scoped>
.admin-panel {
  min-height: 100vh;
  background-color: #EEEAE4;
  margin-top: 50px;
  position: relative;
}

/* Mobile Menu Button */
.mobile-menu-btn {
  display: none;
  position: fixed;
  top: 120px;
  left: 1rem;
  z-index: 1100;
  width: 48px;
  height: 48px;
  background: #1A2845;
  color: #EEEAE4;
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.3);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 1.5rem;
  align-items: center;
  justify-content: center;
}

.mobile-menu-btn.open {

 left: 18rem;
  background: #8C6353;

 
}

.mobile-menu-btn.hidden {
  opacity: 0;
  pointer-events: none;
  transform: translateX(-80px);
}

.mobile-menu-btn:hover {
  background: #8C6353;
  transform: scale(1.05);
}

.mobile-menu-btn.hidden:hover {
  transform: translateX(-80px);
}

.mobile-menu-btn:active {
  transform: scale(0.95);
}

/* Sidebar Overlay for Mobile */
.sidebar-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(26, 40, 69, 0.7);
  backdrop-filter: blur(4px);
  z-index: 1040;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
}

.sidebar-overlay.active {
  opacity: 1;
  pointer-events: all;
}

/* Sidebar */
.sidebar {
  background: linear-gradient(180deg, #1A2845 0%, #0f1829 100%);
  color: #E7D7C9;
  min-height: 100vh;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 10;
}

.sidebar-header {
  color: #EEEAE4;
  letter-spacing: 1px;
  text-transform: uppercase;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(231, 215, 201, 0.05);
}

.sidebar .list-group-item {
  background: transparent;
  color: #E7D7C9;
  border: none;
  padding: 14px 20px;
  font-weight: 500;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-left: 4px solid transparent;
  display: flex;
  align-items: center;
  text-decoration: none;
  position: relative;
  overflow: hidden;
}

.sidebar .list-group-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 0;
  background: linear-gradient(90deg, rgba(231, 215, 201, 0.2) 0%, transparent 100%);
  transition: width 0.3s ease;
}

.sidebar .list-group-item:hover::before {
  width: 100%;
}

.sidebar .list-group-item:hover {
  background-color: rgba(231, 215, 201, 0.1);
  border-left: 4px solid #8C6353;
  color: #EEEAE4;
  transform: translateX(3px);
}

.sidebar .list-group-item i {
  font-size: 1.1rem;
  min-width: 24px;
  transition: transform 0.3s ease;
}

.sidebar .list-group-item:hover i {
  transform: scale(1.15);
}

.sidebar .router-link-active {
  background-color: rgba(231, 215, 201, 0.15);
  border-left: 4px solid #E7D7C9;
  color: #FFF;
  font-weight: 600;
}

.sidebar .router-link-active::after {
  content: '';
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 60%;
  background: #8C6353;
  border-radius: 4px 0 0 4px;
}

.content-area {
  padding: 3rem 2rem;
  background-color: #EEEAE4;
  min-height: 100vh;
}

/* Mobile Responsive Design */
@media (max-width: 767.98px) {
  .admin-panel {
    margin-top: 60px;
    padding-top: 70px;
  }

  /* Show mobile menu button */
  .mobile-menu-btn {
    display: flex;
    top: 80px; 
  }

  /* Show overlay */
  .sidebar-overlay {
    display: block;
  }

  /* Sidebar mobile behavior */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 280px;
    max-width: 85vw;
    z-index: 1050;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: none;
  }

  .sidebar.active {
    transform: translateX(0);
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
  }

  .sidebar-header {
    padding: 1.5rem 1rem !important;
  }

  .sidebar-header h5 {
    font-size: 1.1rem;
  }

  .sidebar .list-group-item {
    padding: 16px 20px;
    font-size: 0.95rem;
  }

  .sidebar .list-group-item i {
    font-size: 1.2rem;
    min-width: 28px;
  }

  /* Content area adjustments */
  .content-area {
    padding: 1.5rem 1rem;
    margin-left: 0 !important;
    padding-top: 0;
    width: 100%;
  }

  /* Ensure content doesn't go under mobile menu button */
  .content-area > * {
    margin-top: 0;
  }
}

/* Tablet adjustments */
@media (min-width: 768px) and (max-width: 991.98px) {
  .content-area {
    padding: 2rem 1.5rem;
  }

  .sidebar .list-group-item {
    padding: 12px 16px;
    font-size: 0.9rem;
  }

  .sidebar-header h5 {
    font-size: 1rem;
  }
}

/* Large screens */
@media (min-width: 1400px) {
  .content-area {
    padding: 3rem 3rem;
  }

  .sidebar .list-group-item {
    padding: 16px 24px;
  }
}

/* Smooth scrolling for sidebar on mobile */
@media (max-width: 767.98px) {
  .sidebar nav {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }

  .sidebar nav::-webkit-scrollbar {
    width: 6px;
  }

  .sidebar nav::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
  }

  .sidebar nav::-webkit-scrollbar-thumb {
    background: rgba(231, 215, 201, 0.3);
    border-radius: 3px;
  }

  .sidebar nav::-webkit-scrollbar-thumb:hover {
    background: rgba(231, 215, 201, 0.5);
  }
}

/* Animation for menu icon */
.mobile-menu-btn i {
  transition: transform 0.3s ease;
}

.mobile-menu-btn:active i {
  transform: rotate(90deg);
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  .sidebar,
  .sidebar-overlay,
  .mobile-menu-btn,
  .sidebar .list-group-item {
    transition: none;
  }
  
  .mobile-menu-btn.hidden {
    transition: none;
  }
}

/* Focus styles for accessibility */
.sidebar .list-group-item:focus-visible {
  outline: 2px solid #8C6353;
  outline-offset: -2px;
  background-color: rgba(231, 215, 201, 0.2);
}

.mobile-menu-btn:focus-visible {
  outline: 2px solid #E7D7C9;
  outline-offset: 2px;
}
</style>