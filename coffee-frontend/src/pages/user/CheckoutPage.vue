<script setup lang="ts">
import { ref, computed } from 'vue'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { validatePromotion } from '@/api/promotions'
import type { PromotionDTO } from '@/api/promotions'

const cartStore = useCartStore()
const authStore = useAuthStore()

const subtotal = computed(() => cartStore.totalPrice)
const promoCode = ref('')
const appliedPromotion = ref<PromotionDTO | null>(null)
const errorMessage = ref<string | null>(null)
const successMessage = ref<string | null>(null)
const discount = computed(() => {
  if (!appliedPromotion.value) return 0
  const promo = appliedPromotion.value
  if (promo.discountType === 'percent') {
    return subtotal.value * (promo.discountValue / 100)
  } else {
    return Math.min(promo.discountValue, subtotal.value)
  }
})
const total = computed(() => Math.max(0, subtotal.value - discount.value))

function updateQty(productId: number, change: number) {
  const item = cartStore.items.find(i => i.product.id === productId)
  if (item) {
    const newQty = item.quantity + change
    cartStore.updateQuantity(productId, newQty)
  }
}

function removeItem(productId: number) {
  cartStore.removeFromCart(productId)
}

async function applyPromoCode() {
  if (!promoCode.value.trim()) {
    errorMessage.value = 'Please enter a promo code'
    setTimeout(() => {
      errorMessage.value = null
    }, 3000)
    return
  }
  console.log("Sending promo code to validate:", promoCode.value.trim())
  console.log("Current appliedPromotion.value:", appliedPromotion.value)
  try {
    const promotion = await validatePromotion(promoCode.value.trim())
    appliedPromotion.value = promotion
    console.log("Applied promotion:", appliedPromotion.value)
    successMessage.value = 'Promo code applied successfully!'
    setTimeout(() => {
      successMessage.value = null
    }, 3000)
  } catch (error) {
    appliedPromotion.value = null
    console.error('Promo code validation error:', error)
    if (error && typeof error === 'object' && 'response' in error) {
      const axiosError = error as { response?: { status?: number; data?: { message?: string; errors?: Record<string, string[]> } } }
      if (axiosError.response?.status === 422) {
        if (axiosError.response.data?.message) {
          errorMessage.value = axiosError.response.data.message
          setTimeout(() => {
            errorMessage.value = null
          }, 3000)
          return
        }
        if (axiosError.response.data?.errors) {
          const firstError = Object.values(axiosError.response.data.errors)[0]
          if (Array.isArray(firstError) && firstError.length > 0) {
            errorMessage.value = firstError[0] || 'Validation error'
            setTimeout(() => {
              errorMessage.value = null
            }, 3000)
            return
          }
        }
      }
    }
    errorMessage.value = 'Invalid promo code'
    setTimeout(() => {
      errorMessage.value = null
    }, 3000)
  }
}

async function pay(type: 'counter' | 'online') {
  if (cartStore.items.length === 0) {
    alert('Your cart is empty!')
    return
  }

  try {
    // Import the createOrder function
    const { createOrder } = await import('@/api/orders')

    // Prepare order data
    const orderData = {
      userId: authStore.user?.id || 1, // Get from auth store
      branchId: 1, // TODO: Get from user selection or default
      totalAmount: total.value,
      status: type === 'counter' ? 'paid' as const : 'pending' as const, // Mark as paid for counter payment
      paymentMethod: type === 'counter' ? 'cash' as const : 'online' as const,
      products: cartStore.items.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity,
        price: item.product.price
      })),
      meta: appliedPromotion.value ? {
        promotion_id: appliedPromotion.value.id,
        discount_amount: discount.value,
        original_total: subtotal.value
      } : undefined
    }

    // Create the order
    const order = await createOrder(orderData)

    // Clear cart after successful order
    cartStore.clearCart()

    alert(`Order placed successfully! Order ID: ${order.id}`)
  } catch (error) {
    console.error('Failed to place order:', error)
    alert('Failed to place order. Please try again.')
  }
}
</script>

<template>
  <NavBar />
  <div class="cart-page">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Your Order</h1>
        <p class="page-subtitle">Review your items before checkout</p>
      </div>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="cart-items-container">
            <div v-if="cartStore.items.length === 0" class="empty-cart">
              <i class="bi bi-cart-x"></i>
              <h3>Your cart is empty</h3>
              <p>Add some delicious items to get started!</p>
              <router-link to="/menu" class="btn-browse">Browse Menu</router-link>
            </div>

            <div v-else class="cart-items">
              <div
                v-for="item in cartStore.items"
                :key="item.product.id"
                class="cart-item"
              >
                <div class="item-info">
                  <div class="item-image">
                    <i class="bi bi-cup-hot"></i>
                  </div>
                  <div class="item-details">
                    <h3 class="item-name">{{ item.product.name }}</h3>
                    <p class="item-price">${{ Number(item.product.price).toFixed(2) }}</p>
                  </div>
                </div>

                <div class="item-actions">
                  <div class="qty-control">
                    <button
                      class="qty-btn"
                      @click="updateQty(item.product.id, -1)"
                      :disabled="item.quantity <= 1"
                    >
                      <i class="bi bi-dash"></i>
                    </button>
                    <span class="qty-display">{{ item.quantity }}</span>
                    <button class="qty-btn" @click="updateQty(item.product.id, 1)">
                      <i class="bi bi-plus"></i>
                    </button>
                  </div>
                  <div class="item-total">${{ (item.product.price * item.quantity).toFixed(2) }}</div>
                  <button class="btn-remove" @click="removeItem(item.product.id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="summary-card">
            <h2 class="summary-title">Order Summary</h2>
            
            <div class="summary-row">
              <span class="summary-label">Subtotal</span>
              <span class="summary-value">${{ subtotal.toFixed(2) }}</span>
            </div>
            
            <div class="summary-row">
              <span class="summary-label">Discount</span>
              <span class="summary-value discount">-${{ discount.toFixed(2) }}</span>
            </div>
            
            <div class="summary-divider"></div>
            
            <div class="summary-row total-row">
              <span class="summary-label">Total</span>
              <span class="summary-value total">${{ total.toFixed(2) }}</span>
            </div>

            <div class="promo-section">
              <label class="promo-label">Promo Code</label>
              <div class="promo-input-group">
                <input 
                  v-model="promoCode" 
                  class="promo-input" 
                  placeholder="Enter code"
                />
                <button class="btn-apply" type="button" @click="applyPromoCode">Apply</button>
              </div>
              <small class="promo-hint">Try WELCOME10 for 10% off</small>
            </div>

            <div class="payment-buttons">
              <button class="btn-payment btn-counter" @click="pay('counter')">
                <i class="bi bi-shop"></i>
                <span>Pay at Counter</span>
              </button>
              <button class="btn-payment btn-online" @click="$router.push('/checkout/online')">
                <i class="bi bi-credit-card"></i>
                <span>Pay Online</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Message -->
  <transition name="slide-down">
    <div v-if="successMessage" class="success-toast">
      <div class="toast-content">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ successMessage }}</span>
      </div>
      <button class="toast-close" @click="successMessage = null">
        <i class="bi bi-x"></i>
      </button>
    </div>
  </transition>

  <!-- Error Toast -->
  <transition name="slide-down">
    <div v-if="errorMessage" class="error-toast">
      <div class="toast-content">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>{{ errorMessage }}</span>
      </div>
      <button class="toast-close" @click="errorMessage = null">
        <i class="bi bi-x"></i>
      </button>
    </div>
  </transition>

  <AppFooter />
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

* {
  font-family: 'Inter', sans-serif;
}

.cart-page {
  min-height: 100vh;
  background-color: #EEEAE4;
  padding: 120px 0 60px;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1A2845;
  margin-bottom: 0.5rem;
}

.page-subtitle {
  font-size: 1.1rem;
  color: #8C6353;
  font-weight: 400;
}

/* Cart Items Container */
.cart-items-container {
  background: #ffffff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 12px rgba(26, 40, 69, 0.08);
}

.empty-cart {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-cart i {
  font-size: 4rem;
  color: #8C6353;
  margin-bottom: 1.5rem;
}

.empty-cart h3 {
  font-size: 1.5rem;
  color: #1A2845;
  margin-bottom: 0.5rem;
}

.empty-cart p {
  color: #8C6353;
  margin-bottom: 2rem;
}

.btn-browse {
  display: inline-block;
  padding: 0.75rem 2rem;
  background: #8C6353;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-browse:hover {
  background: #1A2845;
  transform: translateY(-2px);
}

/* Cart Items */
.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  background: #EEEAE4;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.cart-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.1);
}

.item-info {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex: 1;
}

.item-image {
  width: 60px;
  height: 60px;
  background: #E7D7C9;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: #8C6353;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.item-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1A2845;
  margin: 0;
}

.item-price {
  font-size: 0.95rem;
  color: #8C6353;
  margin: 0;
}

/* Item Actions */
.item-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.qty-control {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: white;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
}

.qty-btn {
  width: 28px;
  height: 28px;
  border: none;
  background: #E7D7C9;
  color: #1A2845;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.9rem;
}

.qty-btn:hover:not(:disabled) {
  background: #8C6353;
  color: white;
}

.qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.qty-display {
  font-weight: 600;
  color: #1A2845;
  min-width: 25px;
  text-align: center;
}

.item-total {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1A2845;
  min-width: 70px;
  text-align: right;
}

.btn-remove {
  width: 36px;
  height: 36px;
  border: none;
  background: transparent;
  color: #8C6353;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1.1rem;
}

.btn-remove:hover {
  background: #fff;
  color: #d63031;
}

/* Summary Card */
.summary-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 12px rgba(26, 40, 69, 0.08);
  position: sticky;
  top: 100px;
}

.summary-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1A2845;
  margin-bottom: 1.5rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.summary-label {
  font-size: 1rem;
  color: #8C6353;
  font-weight: 500;
}

.summary-value {
  font-size: 1rem;
  color: #1A2845;
  font-weight: 600;
}

.summary-value.discount {
  color: #27ae60;
}

.summary-divider {
  height: 1px;
  background: #E7D7C9;
  margin: 1.5rem 0;
}

.total-row {
  margin-bottom: 1.5rem;
}

.total-row .summary-label,
.total-row .summary-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1A2845;
}

/* Promo Section */
.promo-section {
  margin-bottom: 1.5rem;
}

.promo-label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1A2845;
  margin-bottom: 0.5rem;
}

.promo-input-group {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.promo-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid #E7D7C9;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #1A2845;
  transition: all 0.3s ease;
}

.promo-input:focus {
  outline: none;
  border-color: #8C6353;
}

.btn-apply {
  padding: 0.75rem 1.5rem;
  background: #E7D7C9;
  color: #1A2845;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-apply:hover {
  background: #8C6353;
  color: white;
}

.promo-hint {
  font-size: 0.8rem;
  color: #8C6353;
  font-style: italic;
}

/* Payment Buttons */
.payment-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn-payment {
  width: 100%;
  padding: 1rem;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  transition: all 0.3s ease;
}

.btn-counter {
  background: white;
  color: #1A2845;
  border: 2px solid #E7D7C9;
}

.btn-counter:hover {
  background: #E7D7C9;
  border-color: #8C6353;
}

.btn-online {
  background: #8C6353;
  color: white;
}

.btn-online:hover {
  background: #1A2845;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.2);
}

.btn-payment i {
  font-size: 1.2rem;
}

/* Success Toast */
.success-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
  z-index: 1050;
  min-width: 350px;
  max-width: 500px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

.toast-content i {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.toast-content span {
  font-weight: 600;
  font-size: 0.95rem;
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 50%;
  transition: all 0.2s ease;
  flex-shrink: 0;
  opacity: 0.8;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.2);
  opacity: 1;
  transform: scale(1.1);
}

.toast-close i {
  font-size: 1rem;
}

/* Error Toast */
.error-toast {
  position: fixed;
  top: 80px;
  right: 20px;
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(220, 53, 69, 0.3);
  z-index: 1050;
  min-width: 350px;
  max-width: 500px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

/* Transitions */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-100%) scale(0.9);
}

/* Responsive */
@media (max-width: 991px) {
  .summary-card {
    position: static;
  }

  .page-title {
    font-size: 2rem;
  }
}

@media (max-width: 768px) {
  .cart-page {
    padding: 100px 0 40px;
  }

  .cart-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .item-actions {
    width: 100%;
    justify-content: space-between;
  }

  .cart-items-container {
    padding: 1.5rem;
  }

  .summary-card {
    padding: 1.5rem;
  }
}
</style>
