import { ref, computed, watch } from 'vue'
import { defineStore } from 'pinia'
import type { Product } from '@/interfaces/Product'

export interface CartItem {
  product: Product
  quantity: number
}

const CART_STORAGE_KEY = 'coffee-cart'

export const useCartStore = defineStore('cart', () => {
  // Load cart from localStorage on initialization
  const loadCart = (): CartItem[] => {
    try {
      const stored = localStorage.getItem(CART_STORAGE_KEY)
      return stored ? JSON.parse(stored) : []
    } catch (error) {
      console.error('Failed to load cart from localStorage:', error)
      return []
    }
  }

  // Save cart to localStorage
  const saveCart = (cartItems: CartItem[]) => {
    try {
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartItems))
    } catch (error) {
      console.error('Failed to save cart to localStorage:', error)
    }
  }

  const items = ref<CartItem[]>(loadCart())

  const totalItems = computed(() => {
    return items.value.reduce((sum, item) => sum + item.quantity, 0)
  })

  const totalPrice = computed(() => {
    return items.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0)
  })

  function addToCart(product: Product, userId?: number, branchId?: number) {
    // Update local cart immediately for UI responsiveness
    const existingItem = items.value.find(item => item.product.id === product.id)
    if (existingItem) {
      existingItem.quantity++
    } else {
      items.value.push({ product, quantity: 1 })
    }

    // If user is authenticated, sync with backend (don't revert on failure)
    if (userId && branchId) {
      // Fire and forget - don't await to avoid blocking UI
      import('@/api/orders').then(({ addItemToOrder }) => {
        addItemToOrder({
          userId,
          branchId,
          productId: product.id,
          quantity: 1,
        }).catch(error => {
          console.error('Failed to sync cart with backend:', error)
          // Don't revert local cart - localStorage persistence takes precedence
        })
      })
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

  // Watch for changes and persist to localStorage
  watch(items, (newItems) => {
    saveCart(newItems)
  }, { deep: true })

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