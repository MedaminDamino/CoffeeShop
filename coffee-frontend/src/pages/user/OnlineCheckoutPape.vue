<script setup lang="ts">
import { ref, computed, reactive, onMounted } from 'vue'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'

const cartStore = useCartStore()
const authStore = useAuthStore()

// Tunisian cities for dropdown
const tunisianCities = [
  'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan',
  'Bizerte', 'Béja', 'Jendouba', 'Kef', 'Siliana', 'Sousse', 'Monastir',
  'Mahdia', 'Sfax', 'Kairouan', 'Kasserine', 'Sidi Bouzid', 'Gabès',
  'Medenine', 'Tataouine', 'Gafsa', 'Tozeur', 'Kebili'
]

// Form data reactive object
const formData = reactive({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  address: '',
  postalCode: '',
  city: '',
  deliveryNotes: ''
})

// Form validation errors
const errors = reactive({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  address: '',
  postalCode: '',
  city: '',
  deliveryNotes: ''
})

// Computed properties
const subtotal = computed(() => cartStore.totalPrice)
const promoCode = ref('')
const discount = computed(() =>
  promoCode.value.toUpperCase() === 'WELCOME10' ? subtotal.value * 0.1 : 0,
)
const total = computed(() => Math.max(0, subtotal.value - discount.value))

// Pre-populate form with user data
onMounted(() => {
  // Keep form fields empty by default as requested
  // Note: phone and address are not in the current User interface
  // These would need to be added to the backend and User interface
})

// Form validation
function validateForm() {
  let isValid = true

  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key as keyof typeof errors] = ''
  })

  // First name validation
  if (!formData.firstName.trim()) {
    errors.firstName = 'First name is required'
    isValid = false
  }

  // Last name validation
  if (!formData.lastName.trim()) {
    errors.lastName = 'Last name is required'
    isValid = false
  }

  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!formData.email.trim()) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!emailRegex.test(formData.email)) {
    errors.email = 'Please enter a valid email address'
    isValid = false
  }

  // Phone validation
  if (!formData.phone.trim()) {
    errors.phone = 'Phone number is required'
    isValid = false
  } else if (!/^\+?[\d\s-()]{8,}$/.test(formData.phone)) {
    errors.phone = 'Please enter a valid phone number'
    isValid = false
  }

  // Address validation
  if (!formData.address.trim()) {
    errors.address = 'Address is required'
    isValid = false
  }

  // City validation
  if (!formData.city.trim()) {
    errors.city = 'City is required'
    isValid = false
  }

  // Postal code validation
  if (!formData.postalCode.trim()) {
    errors.postalCode = 'Postal code is required'
    isValid = false
  }

  return isValid
}

// Handle payment
async function handlePayment() {
  if (cartStore.items.length === 0) {
    alert('Your cart is empty!')
    return
  }

  if (!validateForm()) {
    alert('Please correct the form errors before proceeding.')
    return
  }

  try {
    // Import the createOrder function
    const { createOrder } = await import('@/api/orders')

    // Prepare order data
    const orderData = {
      userId: authStore.user?.id || 1,
      branchId: 1, // TODO: Get from user selection or default
      totalAmount: total.value,
      status: 'paid' as const,
      paymentMethod: 'online' as const,
      products: cartStore.items.map(item => ({
        product_id: item.product.id,
        quantity: item.quantity,
        price: item.product.price
      })),
      meta: {
        customerInfo: {
          firstName: formData.firstName,
          lastName: formData.lastName,
          name: `${formData.firstName} ${formData.lastName}`,
          email: formData.email,
          phone: formData.phone,
          address: formData.address,
          city: formData.city,
          postalCode: formData.postalCode
        },
        deliveryNotes: formData.deliveryNotes
      }
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
  <div class="checkout-page">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Online Checkout</h1>
        <p class="page-subtitle">Complete your order with secure online payment</p>
      </div>

      <div class="row g-4">
        <!-- Customer Information Form -->
        <div class="col-lg-8 d-flex">
          <div class="checkout-form-container flex-fill">
            <h2 class="section-title">Customer Information</h2>

            <form @submit.prevent="handlePayment" class="checkout-form">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="firstName" class="form-label">First Name *</label>
                  <input
                    id="firstName"
                    v-model="formData.firstName"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.firstName }"
                    placeholder="Enter your first name"
                  />
                  <div v-if="errors.firstName" class="invalid-feedback">{{ errors.firstName }}</div>
                </div>

                <div class="col-md-6">
                  <label for="lastName" class="form-label">Last Name *</label>
                  <input
                    id="lastName"
                    v-model="formData.lastName"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.lastName }"
                    placeholder="Enter your last name"
                  />
                  <div v-if="errors.lastName" class="invalid-feedback">{{ errors.lastName }}</div>
                </div>

                <div class="col-md-6">
                  <label for="email" class="form-label">Email Address *</label>
                  <input
                    id="email"
                    v-model="formData.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': errors.email }"
                    placeholder="Enter your email"
                  />
                  <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                </div>

                <div class="col-md-6">
                  <label for="phone" class="form-label">Phone Number *</label>
                  <input
                    id="phone"
                    v-model="formData.phone"
                    type="tel"
                    class="form-control"
                    :class="{ 'is-invalid': errors.phone }"
                    placeholder="Enter your phone number"
                  />
                  <div v-if="errors.phone" class="invalid-feedback">{{ errors.phone }}</div>
                </div>

                <div class="col-12">
                  <label for="address" class="form-label">Address *</label>
                  <textarea
                    id="address"
                    v-model="formData.address"
                    class="form-control"
                    :class="{ 'is-invalid': errors.address }"
                    rows="3"
                    placeholder="Enter your full address"
                  ></textarea>
                  <div v-if="errors.address" class="invalid-feedback">{{ errors.address }}</div>
                </div>

                <div class="col-md-6">
                  <label for="postalCode" class="form-label">Postal Code *</label>
                  <input
                    id="postalCode"
                    v-model="formData.postalCode"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': errors.postalCode }"
                    placeholder="Enter postal code"
                  />
                  <div v-if="errors.postalCode" class="invalid-feedback">{{ errors.postalCode }}</div>
                </div>

                <div class="col-md-6">
                  <label for="city" class="form-label">City *</label>
                  <select
                    id="city"
                    v-model="formData.city"
                    class="form-control"
                    :class="{ 'is-invalid': errors.city }"
                  >
                    <option value="">Select your city</option>
                    <option v-for="city in tunisianCities" :key="city" :value="city">{{ city }}</option>
                  </select>
                  <div v-if="errors.city" class="invalid-feedback">{{ errors.city }}</div>
                </div>

                <div class="col-12">
                  <label for="deliveryNotes" class="form-label">Delivery Notes</label>
                  <textarea
                    id="deliveryNotes"
                    v-model="formData.deliveryNotes"
                    class="form-control"
                    rows="2"
                    placeholder="Any special delivery instructions (optional)"
                  ></textarea>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4 d-flex">
          <div class="summary-card flex-fill">
            <h2 class="summary-title">Order Summary</h2>

            <div class="cart-items">
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
                <div class="item-quantity">
                  <span class="qty-display">Qty: {{ item.quantity }}</span>
                  <div class="item-total">${{ (item.product.price * item.quantity).toFixed(2) }}</div>
                </div>
              </div>
            </div>

            <div class="summary-divider"></div>

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
                <button class="btn-apply" type="button">Apply</button>
              </div>
              <small class="promo-hint">Try WELCOME10 for 10% off</small>
            </div>

            <button
              class="btn-payment btn-online"
              @click="handlePayment"
              :disabled="cartStore.items.length === 0"
            >
              <i class="bi bi-credit-card"></i>
              <span>Pay Online</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <AppFooter />
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

* {
  font-family: 'Inter', sans-serif;
}

.checkout-page {
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

/* Checkout Form */
.checkout-form-container {
  background: #ffffff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 12px rgba(26, 40, 69, 0.08);
  margin-bottom: 2rem;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1A2845;
  margin-bottom: 1.5rem;
}

.checkout-form .form-label {
  font-weight: 600;
  color: #1A2845;
  margin-bottom: 0.5rem;
}

.checkout-form .form-control {
  padding: 0.75rem 1rem;
  border: 2px solid #E7D7C9;
  border-radius: 8px;
  font-size: 0.95rem;
  color: #1A2845;
  transition: all 0.3s ease;
}

.checkout-form select.form-control {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%238C6353' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.75rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 3rem;
  appearance: none;
  cursor: pointer;
}

.checkout-form .form-control:focus {
  outline: none;
  border-color: #8C6353;
  box-shadow: 0 0 0 3px rgba(140, 99, 83, 0.1);
}

.checkout-form select.form-control:focus {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%231A2845' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
}

.checkout-form .form-control.is-invalid {
  border-color: #dc3545;
}

.checkout-form .invalid-feedback {
  display: block;
  color: #dc3545;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}


/* Cart Items */
.cart-items {
  margin-bottom: 1.5rem;
}

.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid #E7D7C9;
}

.cart-item:last-child {
  border-bottom: none;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.item-image {
  width: 50px;
  height: 50px;
  background: #E7D7C9;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #8C6353;
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.item-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1A2845;
  margin: 0;
}

.item-price {
  font-size: 0.9rem;
  color: #8C6353;
  margin: 0;
}

.item-quantity {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
}

.qty-display {
  font-size: 0.9rem;
  color: #8C6353;
  font-weight: 500;
}

.item-total {
  font-size: 1rem;
  font-weight: 700;
  color: #1A2845;
}

/* Summary Card */
.summary-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 12px rgba(26, 40, 69, 0.08);
  position: sticky;
  top: 100px;
  display: flex;
  flex-direction: column;
  height: 100%;
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

/* Payment Button */
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

.btn-online {
  background: #8C6353;
  color: white;
}

.btn-online:hover:not(:disabled) {
  background: #1A2845;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 40, 69, 0.2);
}

.btn-online:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-payment i {
  font-size: 1.2rem;
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
  .checkout-page {
    padding: 100px 0 40px;
  }

  .checkout-form-container {
    padding: 1.5rem;
  }

  .summary-card {
    padding: 1.5rem;
  }

  .cart-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .item-quantity {
    align-items: flex-start;
  }
}
</style>