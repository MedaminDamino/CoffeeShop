import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Product } from '@/interfaces/Product'

export interface CartItem {
  product: Product
  quantity: number
}

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItem[]>([])

  const totalItems = computed(() => {
    return items.value.reduce((sum, item) => sum + item.quantity, 0)
  })

  const totalPrice = computed(() => {
    return items.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0)
  })

  async function addToCart(product: Product, userId?: number, branchId?: number) {
    // Update local cart immediately for UI responsiveness
    const existingItem = items.value.find(item => item.product.id === product.id)
    if (existingItem) {
      existingItem.quantity++
    } else {
      items.value.push({ product, quantity: 1 })
    }

    // If user is authenticated, sync with backend
    if (userId && branchId) {
      try {
        const { addItemToOrder } = await import('@/api/orders')
        await addItemToOrder({
          userId,
          branchId,
          productId: product.id,
          quantity: 1,
        })
      } catch (error) {
        console.error('Failed to sync cart with backend:', error)
        // Revert local change on failure
        if (existingItem) {
          existingItem.quantity--
        } else {
          items.value.pop()
        }
        throw error
      }
    }
  }

  function removeFromCart(productId: number) {
    const index = items.value.findIndex(item => item.product.id === productId)
    if (index > -1) {
      items.value.splice(index, 1)
    }
  }

  function updateQuantity(productId: number, quantity: number) {
    const item = items.value.find(item => item.product.id === productId)
    if (item) {
      if (quantity <= 0) {
        removeFromCart(productId)
      } else {
        item.quantity = quantity
      }
    }
  }

  function clearCart() {
    items.value = []
  }

  return {
    items,
    totalItems,
    totalPrice,
    addToCart,
    removeFromCart,
    updateQuantity,
    clearCart
  }
})