import { ref } from 'vue'
import { defineStore } from 'pinia'
import type { Product } from '@/interfaces/Product'
import { getProducts } from '@/api/products'

export const useProductStore = defineStore('products', () => {
  const products = ref<Product[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchProducts() {
    loading.value = true
    error.value = null
    try {
      const data = await getProducts()
      products.value = data
    } catch (err) {
      error.value = 'Failed to fetch products. Using mock data.'
      console.error(err)
      // Mock data for testing
      products.value = [
        { id: 1, name: 'Espresso', price: 3.5, description: undefined, imageUrl: undefined, isActive: true, meta: undefined, categoryId: 1 },
        { id: 2, name: 'Latte', price: 4.0, description: undefined, imageUrl: undefined, isActive: true, meta: undefined, categoryId: 1 },
        { id: 3, name: 'Cappuccino', price: 4.5, description: undefined, imageUrl: undefined, isActive: true, meta: undefined, categoryId: 1 },
      ]
    } finally {
      loading.value = false
    }
  }

  return { products, loading, error, fetchProducts }
})
