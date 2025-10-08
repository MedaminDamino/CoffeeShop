<template>
  <NavBar />

  <div class="menu-container">
    <!-- Header Section -->
    <div class="menu-header">
      <h1 class="menu-title">Our Menu</h1>
      <p class="menu-subtitle">Discover your perfect cup</p>
    </div>

    <!-- Search Bar -->
    <div class="search-wrapper">
      <div class="search-container">
        <i class="bi bi-search search-icon"></i>
        <input
          v-model.trim="query"
          class="search-input"
          placeholder="Search coffee, snacks, desserts..."
        />
        <button 
          v-if="query" 
          class="search-clear"
          @click="query = ''"
          aria-label="Clear search"
        >
          <i class="bi bi-x-circle-fill"></i>
        </button>
      </div>
    </div>

    <!-- Category Pills -->
    <div class="categories-section">
      <div class="categories-scroll">
        <button
          class="category-pill"
          :class="{ active: activeCatId === 0 }"
          @click="activeCatId = 0"
        >
          <span class="category-icon">🌟</span>
          <span>All</span>
        </button>
        <button
          v-for="c in categories"
          :key="c.id"
          class="category-pill"
          :class="{ active: activeCatId === c.id }"
          @click="activeCatId = c.id"
        >
          <span class="category-icon">{{ getCategoryIcon(c.name) }}</span>
          <span>{{ c.name }}</span>
        </button>
      </div>
    </div>

    <!-- Status Messages -->
    <div v-if="store.loading" class="status-message loading">
      <div class="spinner"></div>
      <span>Loading delicious items...</span>
    </div>
    <div v-else-if="store.error" class="status-message error">
      <i class="bi bi-exclamation-circle"></i>
      <span>{{ store.error }}</span>
    </div>

    <!-- Products Grid -->
    <div v-else-if="filteredProducts.length > 0" class="products-grid">
      <div
        v-for="p in filteredProducts"
        :key="p.id"
      >
        <MenuCard :product="p" />
      </div>
    </div>

    <!-- No Results -->
    <div v-else class="no-results">
      <i class="bi bi-search"></i>
      <h3>No items found</h3>
      <p>Try adjusting your search or filter</p>
    </div>
  </div>

  <AppFooter />
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useProductStore } from '@/stores/products'
import MenuCard from '@/components/MenuCard.vue'
import { getCategories, type CategoryDTO } from '@/api/categories'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const store = useProductStore()
const { fetchProducts } = store

const categories = ref<CategoryDTO[]>([])
const query = ref('')
const activeCatId = ref(0)

onMounted(async () => {
  try {
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
  return store.products.filter((p) => {
    const matchesQuery = !q || p.name.toLowerCase().includes(q)
    const matchesCategory =
      activeCatId.value === 0 || p.categoryId === activeCatId.value
    return matchesQuery && matchesCategory
  })
})

const getCategoryIcon = (name: string): string => {
  const icons: Record<string, string> = {
    'Drinks': '☕',
    'Tea': '🍵',
    'Snacks': '🥐',
    'Desserts': '🍰',
    'Beverages': '🥤',
    'Breakfast': '🍳',
    'Lunch': '🍽️',
  }
  return icons[name] || '🍴'
}
</script>

<style scoped>
.menu-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem 1rem;
  margin-top: 50px;
}

/* Header Section */
.menu-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.menu-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1A2845;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.5px;
}

.menu-subtitle {
  font-size: 1.1rem;
  color: #8C6353;
  margin: 0;
}

/* Search Bar */
.search-wrapper {
  margin-bottom: 2rem;
  display: flex;
  justify-content: center;
}

.search-container {
  position: relative;
  width: 100%;
  max-width: 600px;
}

.search-icon {
  position: absolute;
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  color: #8C6353;
  font-size: 1.2rem;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 1rem 3.5rem 1rem 3.5rem;
  border: 2px solid #EEEAE4;
  border-radius: 50px;
  font-size: 1rem;
  background: #FFFFFF;
  transition: all 0.3s ease;
  outline: none;
}

.search-input:focus {
  border-color: #8C6353;
  box-shadow: 0 4px 12px rgba(140, 99, 83, 0.1);
}

.search-input::placeholder {
  color: #8C6353;
  opacity: 0.6;
}

.search-clear {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #8C6353;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.search-clear:hover {
  opacity: 1;
}

/* Categories Section */
.categories-section {
  margin-bottom: 2.5rem;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.categories-section::-webkit-scrollbar {
  display: none;
}

.categories-scroll {
  display: flex;
  gap: 0.75rem;
  padding: 0.5rem 0;
  min-width: min-content;
}

.category-pill {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: 2px solid #E7D7C9;
  background: #FFFFFF;
  color: #1A2845;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.category-pill:hover {
  border-color: #8C6353;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(140, 99, 83, 0.15);
}

.category-pill.active {
  background: #1A2845;
  color: #FFFFFF;
  border-color: #1A2845;
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.2);
}

.category-icon {
  font-size: 1.2rem;
  line-height: 1;
}

/* Status Messages */
.status-message {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 2rem;
  border-radius: 16px;
  margin: 2rem 0;
  font-size: 1.1rem;
}

.status-message.loading {
  background: #E7D7C9;
  color: #1A2845;
}

.status-message.error {
  background: #ffe6e6;
  color: #d32f2f;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid #EEEAE4;
  border-top-color: #1A2845;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

/* No Results */
.no-results {
  text-align: center;
  padding: 4rem 2rem;
  color: #8C6353;
}

.no-results i {
  font-size: 4rem;
  opacity: 0.3;
  margin-bottom: 1rem;
}

.no-results h3 {
  font-size: 1.5rem;
  color: #1A2845;
  margin: 1rem 0 0.5rem 0;
}

.no-results p {
  font-size: 1rem;
  opacity: 0.7;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .menu-title {
    font-size: 2rem;
  }

  .menu-subtitle {
    font-size: 1rem;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
  }

  .category-pill {
    padding: 0.625rem 1.25rem;
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .menu-container {
    padding: 1.5rem 1rem;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  }

  .menu-title {
    font-size: 1.75rem;
  }
}
</style>