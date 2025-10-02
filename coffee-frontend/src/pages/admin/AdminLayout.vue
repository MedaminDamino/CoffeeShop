<template>
  <div class="container-fluid">
    <div class="row">
      <aside class="col-12 col-md-3 col-lg-2 bg-light border-end p-0">
        <div class="list-group list-group-flush sticky-top" style="top: 0">
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
      <section class="col-12 col-md-9 col-lg-10 p-3">
        <RouterView />
      </section>
    </div>
  </div>
</template>
