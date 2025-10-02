<template>
  <div>
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h4 m-0">Menu</h1>
      <div class="input-group" style="max-width: 320px">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input v-model="query" class="form-control" placeholder="Search coffee, snacks, desserts" />
      </div>
    </div>

    <ul class="nav nav-pills mb-3 overflow-auto">
      <li class="nav-item me-2">
        <button class="nav-link" :class="{ active: activeCatId === 0 }" @click="activeCatId = 0">
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

    <div v-if="loading" class="alert alert-info">Loading...</div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-else class="row g-3">
      <div class="col-12 col-sm-6 col-md-4 col-lg-3" v-for="p in filtered" :key="p.id">
        <ProductCard :product="p" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useProductStore } from '@/stores/products'
import ProductCard from '@/components/ProductCard.vue'
import { getCategories, type CategoryDTO } from '@/api/categories'

const store = useProductStore()
const { products, fetchProducts, loading, error } = store

onMounted(async () => {
  fetchProducts()
  try {
    categories.value = await getCategories()
  } catch (e) {
    categories.value = [
      { id: 1, name: 'Coffee' },
      { id: 2, name: 'Snacks' },
      { id: 3, name: 'Desserts' },
    ]
  }
})

const query = ref('')
const categories = ref<CategoryDTO[]>([])
const activeCatId = ref(0) // 0 means All

const filtered = computed(() => {
  const q = query.value.trim().toLowerCase()
  return products.filter((p) => {
    const matchesQuery = !q || p.name.toLowerCase().includes(q)
    const matchesCat = activeCatId.value === 0 || p.category_id === activeCatId.value
    return matchesQuery && matchesCat
  })
})
</script>
