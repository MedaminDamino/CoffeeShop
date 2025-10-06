<template>
  <div class="card h-100 shadow-sm">
    <div class="card-body d-flex flex-column">
      <h3 class="h6 card-title mb-2">{{ product.name }}</h3>
      <p class="text-muted mb-3">${{ Number(product.price).toFixed(2) }}</p>
      <div class="mt-auto d-grid">
        <button class="btn btn-outline-primary">
          <i class="bi bi-cart-plus me-2"></i>Add to Cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Product } from '@/interfaces/Product'

defineProps<{
  product: Product
}>()
</script>
