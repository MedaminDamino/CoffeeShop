<script setup lang="ts">
import { onMounted, ref } from 'vue'

import { getTables, type TableDTO } from '@/api/tables'
import { createReservation } from '@/api/reservations'

const name = ref('')
const date = ref('')
const time = ref('')
const guests = ref(2)
const tables = ref<TableDTO[]>([])
const tableId = ref<number | null>(null)

onMounted(async () => {
  try {
    tables.value = await getTables()
  } catch (e) {
    tables.value = []
  }
})

async function submit() {
  if (!tableId.value) return
  await createReservation({
    name: name.value,
    table_id: tableId.value,
    date: date.value,
    time: time.value,
    guests: guests.value,
  })
  alert('Reservation created!')
}
</script>

<template>
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h1 class="h4 mb-4">Reserve a Table</h1>
          <form @submit.prevent="submit">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input v-model="name" type="text" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Table</label>
              <select v-model.number="tableId" class="form-select" required>
                <option :value="null" disabled>Select a table</option>
                <option v-for="t in tables" :key="t.id" :value="t.id">
                  Table {{ t.number }} ({{ t.seats }} seats)
                </option>
              </select>
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Date</label>
                <input v-model="date" type="date" class="form-control" required />
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
            <button class="btn btn-primary mt-4" type="submit">
              <i class="bi bi-calendar-check me-2"></i>Reserve
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
