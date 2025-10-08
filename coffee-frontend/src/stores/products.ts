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
    } finally {
      loading.value = false
    }
  }

  return { products, loading, error, fetchProducts }
})
