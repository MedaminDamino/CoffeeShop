<script setup lang="ts">
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
</script>

<template>
  <NavBar />
<div class="container-fluid" style="min-height: calc(100vh - 200px); padding-top: 100px;">
    <div class="row" style="min-height: calc(100vh - 280px);">
      <aside class="col-12 col-md-3 col-lg-2 bg-light border-end p-0 d-flex flex-column">
        <div class="list-group list-group-flush flex-grow-1" style="overflow-y: auto;">
          <RouterLink class="list-group-item list-group-item-action" to="/admin/users"
            >Users</RouterLink
          >
          <RouterLink class="list-group-item list-group-item-action" to="/admin/branches"
            >Branches</RouterLink
          >
          <RouterLink class="list-group-item list-group-item-action" to="/admin/tables"
            >Tables</RouterLink
          >
          <RouterLink class="list-group-item list-group-item-action" to="/admin/categories"
            >Categories</RouterLink
          >
          <RouterLink class="list-group-item list-group-item-action" to="/admin/products"
            >Products</RouterLink
          >
          <RouterLink class="list-group-item list-group-item-action" to="/admin/reservations"
            >Reservations</RouterLink
          >
          <RouterLink class="list-group-item list-group-item-action" to="/admin/orders"
            >Orders</RouterLink
          >
          <RouterLink class="list-group-item list-group-item-action" to="/admin/promotions"
            >Promotions</RouterLink
          >
        </div>
      </aside>
      <section class="col-12 col-md-9 col-lg-10 p-3 d-flex flex-column" style="padding-top: 5rem; padding-bottom: 2rem; min-height: calc(100vh - 280px);">
        <RouterView />
      </section>
    </div>
  </div>
  <AppFooter />
</template>
