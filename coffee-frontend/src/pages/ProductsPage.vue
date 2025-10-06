<template>
  <NavBar />

  <div>
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h4 m-0">Menu</h1>

      <div class="input-group" style="max-width: 320px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input
          v-model.trim="query"
          class="form-control"
          placeholder="Search coffee, snacks, desserts"
        />
      </div>
    </div>

    <!-- Category Pills -->
    <ul class="nav nav-pills mb-3 overflow-auto">
      <li class="nav-item me-2">
        <button
          class="nav-link"
          :class="{ active: activeCatId === 0 }"
          @click="activeCatId = 0"
        >
          All
        </button>
      </li>
      <li v-for="c in categories" :key="c.id" class="nav-item me-2">
        <button
          class="nav-link"
          :class="{ active: activeCatId === c.id }"
          @click="activeCatId = c.id"
        >
          {{ c.name }}
        </button>
      </li>
    </ul>

    <!-- Status -->
    <div v-if="loading" class="alert alert-info">Loading...</div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- Products -->
    <div v-else class="row g-3">
      <div
        v-for="p in filteredProducts"
        :key="p.id"
        class="col-12 col-sm-6 col-md-4 col-lg-3"
      >
        <Menu :product="p" />
      </div>
    </div>
  </div>

  <AppFooter />
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useProductStore } from '@/stores/products'
import Menu from '@/components/Menu.vue'
import { getCategories, type CategoryDTO } from '@/api/categories'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const store = useProductStore()
const { products, fetchProducts, loading, error } = store

const categories = ref<CategoryDTO[]>([])
const query = ref('')
const activeCatId = ref(0) // 0 means All

onMounted(async () => {
  try {
    // Load products and categories concurrently
    await Promise.all([
      fetchProducts(),
      getCategories().then((data) => (categories.value = data)),
    ])
  } catch (err) {
    console.error('Error loading data:', err)
  }
})

const filteredProducts = computed(() => {
  const q = query.value.toLowerCase()
  return products.filter((p) => {
    const matchesQuery = !q || p.name.toLowerCase().includes(q)
    const matchesCategory =
      activeCatId.value === 0 || p.categoryId === activeCatId.value
    return matchesQuery && matchesCategory
  })
})
</script>
