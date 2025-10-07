<script setup lang="ts">
import { onMounted, ref } from 'vue'

import { getTables, type TableDTO } from '@/api/tables'
import { createReservation } from '@/api/reservations'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
import { useAuthStore } from '@/stores/auth'
import { Modal } from 'bootstrap'

const authStore = useAuthStore()
const date = ref('')
const time = ref('')
const guests = ref(2)
const tables = ref<TableDTO[]>([])
const tableId = ref<number | null>(null)
const successMessage = ref('')
const errorMessage = ref('')

onMounted(async () => {
  try {
    tables.value = await getTables()
  } catch (error) {
    console.error(error)
    tables.value = []
  }
})

async function submit() {
  errorMessage.value = ''
  successMessage.value = ''
  if (!authStore.isAuthenticated) {
    const modal = new Modal(document.getElementById('authModal')!)
    modal.show()
    return
  }
  if (!tableId.value || !date.value || !time.value) return
  const startAt = `${date.value} ${time.value}:00`
  try {
    await createReservation({
      userId: authStore.user!.id,
      tableId: tableId.value,
      startAt,
      status: 'confirmed',
      notes: `Guests: ${guests.value}`,
    })
    successMessage.value = 'Reservation created successfully! We look forward to serving you.'
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (error: unknown) {
    const err = error as { response?: { data?: { error?: string } } }
    errorMessage.value = err.response?.data?.error || 'Failed to create reservation'
  }
}
</script>

<template>
  <NavBar />
  <div class="row justify-content-center form-container">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h1 class="h4 mb-4">Reserve a Table</h1>
          <div v-if="errorMessage" class="alert alert-danger mb-3" role="alert">
            {{ errorMessage }}
          </div>
          <div v-if="successMessage" class="alert alert-success mb-3" role="alert">
            {{ successMessage }}
          </div>
          <form @submit.prevent="submit">
            <div class="mb-3">
              <label class="form-label">Table</label>
              <select v-model.number="tableId" class="form-select" required>
                <option :value="null" disabled>Select a table</option>
                <option v-for="t in tables" :key="t.id" :value="t.id">
                  Table {{ t.number }} ({{ t.capacity }} seats)
                </option>
              </select>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Date</label>
                <input v-model="date" type="date" class="form-control" :min="new Date().toISOString().split('T')[0]"
                  required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Time</label>
                <input v-model="time" type="time" class="form-control" required />
              </div>
            </div>
            <div class="mt-3">
              <label class="form-label">Guests</label>
              <input v-model.number="guests" type="number" min="1" class="form-control" />
            </div>
            <div class="text-center mt-4">
              <div class="text-center mt-4">
                <button type="submit" class="btn" style="background-color:#1A2845; color:white; border:none; 
                 padding:12px 28px; font-size:1.1rem; border-radius:8px;">
                  <i class="bi bi-calendar-check me-2"></i>Reserve
                </button>
              </div>

            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
  <AppFooter />
</template>
<style>
.form-container {
  margin-top: 8rem;
  margin-bottom: 5rem;
}
</style>
