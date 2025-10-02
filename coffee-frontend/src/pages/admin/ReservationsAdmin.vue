<script setup lang="ts">
import CrudTable from '@/components/admin/CrudTable.vue'
import { getReservations, createReservation, type ReservationDTO } from '@/api/reservations'
import { getTables, type TableDTO } from '@/api/tables'
import { onMounted, ref, computed } from 'vue'

const tables = ref<TableDTO[]>([])
onMounted(async () => {
  tables.value = await getTables()
})

const reservationFields = computed(() => [
  { key: 'userId', label: 'User ID', type: 'number' as const, required: true, min: 1 },
  {
    key: 'tableId',
    label: 'Table',
    type: 'select' as const,
    required: true,
    options: tables.value.map((t) => ({ label: `Table ${t.number} (${t.capacity} seats)`, value: t.id })),
  },
  { key: 'startAt', label: 'Start At', type: 'datetime-local' as const, required: true },
  { key: 'endAt', label: 'End At', type: 'datetime-local' as const },

  {
    key: 'status',
    label: 'Status',
    type: 'select' as const,
    options: [
      { label: 'Pending', value: 'pending' },
      { label: 'Confirmed', value: 'confirmed' },
      { label: 'Canceled', value: 'canceled' },
      { label: 'Completed', value: 'completed' }
    ]
  },
  { key: 'notes', label: 'Notes', type: 'textarea' as const },
])
</script>

<template>
  <CrudTable
    title="Reservations"
    :columns="[
      { key: 'id', label: 'ID' },
      { key: 'userId', label: 'User ID' },
      { key: 'tableId', label: 'Table ID' },
      { key: 'startAt', label: 'Start At' },
      { key: 'status', label: 'Status' },
    ]"
    :fetchAll="getReservations"
    :createFields="reservationFields"
    :onCreate="(payload) => createReservation(payload as Pick<ReservationDTO, 'userId' | 'tableId' | 'startAt' | 'endAt' | 'status' | 'notes'>)"
  />
</template>


