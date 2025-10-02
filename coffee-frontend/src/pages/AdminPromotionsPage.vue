<script setup lang="ts">
import { ref } from 'vue'

type Promo = { code: string; percent: number }
const promos = ref<Promo[]>([{ code: 'WELCOME10', percent: 10 }])
const code = ref('')
const percent = ref<number | null>(null)

function addPromo() {
  if (!code.value || !percent.value) return
  promos.value.push({ code: code.value.toUpperCase(), percent: percent.value })
  code.value = ''
  percent.value = null
}
</script>

<template>
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h1 class="h5 mb-3">Create Promotion</h1>
          <div class="mb-3">
            <label class="form-label">Code</label>
            <input v-model="code" class="form-control" placeholder="e.g. SUMMER15" />
          </div>
          <div class="mb-3">
            <label class="form-label">Discount %</label>
            <input v-model.number="percent" type="number" min="1" max="90" class="form-control" />
          </div>
          <button class="btn btn-primary" @click="addPromo">
            <i class="bi bi-plus-circle me-2"></i>Add
          </button>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h6">Existing Promotions</h2>
          <ul class="list-group list-group-flush">
            <li
              v-for="p in promos"
              :key="p.code"
              class="list-group-item d-flex justify-content-between"
            >
              <span>{{ p.code }}</span>
              <span>{{ p.percent }}%</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
