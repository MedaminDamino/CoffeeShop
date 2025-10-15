<template>
  <div class="product-card">
    <div class="product-image">
      <div class="image-placeholder">
        <i class="bi bi-cup-hot"></i>
      </div>
      
    </div>
    <div class="product-info">
      <div class="product-header">
        <h3 class="product-name">{{ product.name }}</h3>
        <div class="rating">
          <i class="bi bi-star-fill"></i>
          <span>4.8</span>
        </div>
      </div>
      <p class="product-description">{{ product.description}}</p>
      <div class="product-footer">
        <div class="price-container">
          <span class="price-label">Price</span>
          <span class="product-price">${{ Number(product.price).toFixed(2) }}</span>
        </div>
        <button class="add-to-cart-btn" aria-label="Add to cart" @click="addToCart">
           <i class="bi bi-plus-lg"></i>
         </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Product } from '@/interfaces/Product'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'

const props = defineProps<{
  product: Product
}>()

const cartStore = useCartStore()
const authStore = useAuthStore()

const addToCart = async () => {
  try {
    // Get user and branch info for backend sync
    const userId = authStore.user?.id || 1 // Default to user ID 1 if not logged in
    const branchId = 1 // TODO: Get from user selection or default

    await cartStore.addToCart(props.product, userId, branchId)

    // Show success feedback
    showToast('Item added to cart!', 'success')
  } catch (error) {
    console.error('Failed to add item to cart:', error)
    showToast('Failed to add item to cart. Please try again.', 'error')
  }
}

const showToast = (message: string, type: 'success' | 'error') => {
  // Simple toast implementation - could be replaced with a proper toast library
  const toast = document.createElement('div')
  toast.className = `toast toast-${type}`
  toast.textContent = message
  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 24px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
    background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
  `

  document.body.appendChild(toast)

  setTimeout(() => {
    toast.style.animation = 'slideOut 0.3s ease-in'
    setTimeout(() => document.body.removeChild(toast), 300)
  }, 3000)
}
</script>

<style scoped>
@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideOut {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}
.product-card {
  background: #FFFFFF;
  border-radius: 20px;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 2px solid #EEEAE4;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
}

.product-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(231, 215, 201, 0.1) 0%, rgba(140, 99, 83, 0.05) 100%);
  opacity: 0;
  transition: opacity 0.4s ease;
  pointer-events: none;
  z-index: 1;
}

.product-card:hover::before {
  opacity: 1;
}

.product-card:hover {
  transform: translateY(-12px) scale(1.02);
  box-shadow: 0 20px 40px rgba(26, 40, 69, 0.2);
  border-color: #E7D7C9;
}

.product-image {
  position: relative;
  padding-top: 100%;
  background: linear-gradient(135deg, #E7D7C9 0%, #EEEAE4 50%, #E7D7C9 100%);
  overflow: hidden;
}

.product-image::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
  animation: shimmer 3s infinite;
  pointer-events: none;
}

@keyframes shimmer {
  0%, 100% { transform: translate(-25%, -25%); }
  50% { transform: translate(25%, 25%); }
}

.image-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 4rem;
  color: #8C6353;
  opacity: 0.4;
  transition: all 0.4s ease;
}

.product-card:hover .image-placeholder {
  transform: scale(1.1) rotate(-5deg);
  opacity: 0.6;
}

.badge-container {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 2;
}



@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.product-info {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  flex: 1;
  position: relative;
  z-index: 2;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
  gap: 0.75rem;
}

.product-name {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1A2845;
  margin: 0;
  line-height: 1.3;
  flex: 1;
}

.rating {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  background: #EEEAE4;
  padding: 0.35rem 0.65rem;
  border-radius: 12px;
  flex-shrink: 0;
}

.rating i {
  color: #8C6353;
  font-size: 0.75rem;
}

.rating span {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1A2845;
}

.product-description {
  font-size: 0.9rem;
  color: #8C6353;
  margin: 0 0 1.25rem 0;
  opacity: 0.8;
  font-style: italic;
}

.product-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: auto;
  gap: 1rem;
}

.price-container {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.price-label {
  font-size: 0.75rem;
  color: #8C6353;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  opacity: 0.7;
}

.product-price {
  font-size: 1.75rem;
  font-weight: 800;
  color: #1A2845;
  line-height: 1;
}

.add-to-cart-btn {
  width: 56px;
  height: 56px;
  padding: 0;
  background: linear-gradient(135deg, #1A2845 0%, #2a3a5a 100%);
  color: #FFFFFF;
  border: none;
  border-radius: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.2);
  position: relative;
  overflow: hidden;
}

.add-to-cart-btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.add-to-cart-btn:hover::before {
  width: 200%;
  height: 200%;
}

.add-to-cart-btn:hover {
  background: linear-gradient(135deg, #8C6353 0%, #a07565 100%);
  transform: scale(1.1) rotate(5deg);
  box-shadow: 0 8px 20px rgba(140, 99, 83, 0.4);
}

.add-to-cart-btn:active {
  transform: scale(1) rotate(0deg);
}

.add-to-cart-btn i {
  font-size: 1.5rem;
  position: relative;
  z-index: 1;
  transition: transform 0.3s ease;
}

.add-to-cart-btn:hover i {
  transform: rotate(90deg);
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .product-info {
    padding: 1.25rem;
  }

  .product-name {
    font-size: 1rem;
  }

  .product-price {
    font-size: 1.5rem;
  }

  .add-to-cart-btn {
    width: 50px;
    height: 50px;
  }

  .image-placeholder {
    font-size: 3rem;
  }
}
</style>