<script setup lang="ts">
import { ref, computed } from 'vue'

type CartItem = { id: number; name: string; price: number; qty: number }
const cart = ref<CartItem[]>([])

const subtotal = computed(() => cart.value.reduce((s, i) => s + i.price * i.qty, 0))
const promoCode = ref('')
const discount = computed(() =>
  promoCode.value.toUpperCase() === 'WELCOME10' ? subtotal.value * 0.1 : 0,
)
const total = computed(() => Math.max(0, subtotal.value - discount.value))

function pay(type: 'counter' | 'online') {
  alert(`Paying ${total.value.toFixed(2)} at ${type}`)
}
</script>

<template>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h1 class="h4 mb-3">Your Cart</h1>
          <div v-if="cart.length === 0" class="text-muted">Cart is empty.</div>
          <ul v-else class="list-group list-group-flush">
            <li
              v-for="item in cart"
              :key="item.id"
              class="list-group-item d-flex justify-content-between"
            >
              <span>{{ item.name }} × {{ item.qty }}</span>
              <span>${{ (item.price * item.qty).toFixed(2) }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h5">Summary</h2>
          <div class="d-flex justify-content-between">
            <span>Subtotal</span><strong>${{ subtotal.toFixed(2) }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Discount</span><strong>-${{ discount.toFixed(2) }}</strong>
          </div>
          <hr />
          <div class="d-flex justify-content-between">
            <span>Total</span><strong>${{ total.toFixed(2) }}</strong>
          </div>
          <div class="mt-3 input-group">
            <input v-model="promoCode" class="form-control" placeholder="Promo code" />
            <button class="btn btn-outline-secondary" type="button">Apply</button>
          </div>
          <div class="d-grid gap-2 mt-3">
            <button class="btn btn-outline-primary" @click="pay('counter')">
              <i class="bi bi-shop me-2"></i>Pay at Counter
            </button>
            <button class="btn btn-primary" @click="pay('online')">
              <i class="bi bi-credit-card me-2"></i>Pay Online
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
