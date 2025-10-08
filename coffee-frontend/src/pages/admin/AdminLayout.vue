<script setup lang="ts">
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
</script>

<template>
  <NavBar />

  <div class="container-fluid admin-panel">
    <div class="row">
      <!-- Sidebar -->
      <aside class="col-12 col-md-3 col-lg-2 sidebar d-flex flex-column p-0">
        <div class="sidebar-header text-center py-4">
          <h5 class="fw-bold mb-0">☕ Coffee Admin</h5>
        </div>

        <div class="list-group list-group-flush flex-grow-1">
          <RouterLink class="list-group-item" to="/admin/users">
            <i class="bi bi-people me-2"></i> Users
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/branches">
            <i class="bi bi-shop me-2"></i> Branches
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/tables">
            <i class="bi bi-grid-3x3-gap me-2"></i> Tables
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/categories">
            <i class="bi bi-tags me-2"></i> Categories
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/products">
            <i class="bi bi-cup-hot me-2"></i> Products
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/reservations">
            <i class="bi bi-calendar-check me-2"></i> Reservations
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/orders">
            <i class="bi bi-receipt me-2"></i> Orders
          </RouterLink>
          <RouterLink class="list-group-item" to="/admin/promotions">
            <i class="bi bi-megaphone me-2"></i> Promotions
          </RouterLink>
        </div>
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
}

.sidebar {
  background-color: #1A2845;
  color: #E7D7C9;
  min-height: 100vh;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
  color: #EEEAE4;
  letter-spacing: 1px;
  text-transform: uppercase;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.sidebar .list-group-item {
  background: transparent;
  color: #E7D7C9;
  border: none;
  padding: 12px 20px;
  font-weight: 500;
  transition: all 0.3s ease;
  border-left: 4px solid transparent;
}

.sidebar .list-group-item:hover {
  background-color: rgba(231, 215, 201, 0.1);
  border-left: 4px solid #8C6353;
  color: #EEEAE4;
  transform: translateX(3px);
}

.sidebar .router-link-active {
  background-color: rgba(231, 215, 201, 0.15);
  border-left: 4px solid #E7D7C9;
  color: #FFF;
}

.content-area {
  padding: 3rem 2rem;
  background-color: #EEEAE4;
  min-height: 100vh;
}
</style>
